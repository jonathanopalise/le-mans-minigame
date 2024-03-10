<?php


class Span
{
    private SixteenPixelBlockCollection $blockCollection;
    private int $startOffset;
    private int $endOffset;

    public function __construct(SixteenPixelBlockCollection $blockCollection, int $startOffset, int $endOffset)
    {
        if ($startOffset > $endOffset) {
            throw new \Exception('Start offset must be less than or equal to end offset');
        }

        $this->blockCollection = $blockCollection;

        $blockCollectionMaxIndex = $this->blockCollection->getBlockCount() - 1;

        if ($startOffset < 0 || $startOffset > $blockCollectionMaxIndex) {
            throw new \Exception('Start offset is out of bounds');
        }

        if ($endOffset < 0 || $endOffset > $blockCollectionMaxIndex) {
            throw new \Exception('End offset is out of bounds');
        }

        $this->startOffset = $startOffset;
        $this->endOffset = $endOffset;
    }

    public function getLength(): int
    {
        return ($this->endOffset - $this->startOffset) + 1;
    }

    public function isBlitterGood(): bool
    {
        if ($this->getLength() <= 3) {
            return true;
        }

        $middleMaskWords = [];
        for ($offset = $this->startOffset + 1; $offset < $this->endOffset; $offset++) {
            $middleMaskWords[] = $this->blockCollection->getBlockByOffset($offset)->getMaskWord();
        }           

        $uniqueMiddleMaskWords = array_unique($middleMaskWords);
        return count($uniqueMiddleMaskWords) == 1;
    }

    public function splitIntoSpanCollection(SpanCollection $spanCollection): void
    {
        $startOffset = $this->startOffset;
        $endOffset = $this->endOffset;

        $spanCollection->addSpanUsingOffsets(
            $this->blockCollection,
            $startOffset,
            $startOffset + 2
        );

        $spanCollection->addSpanUsingOffsets(
            $this->blockCollection,
            $startOffset + 3,
            $endOffset
        );
    }

    public function getBlockCollection(): SixteenPixelBlockCollection
    {
        return $this->blockCollection;
    }

    public function getStartOffset(): int
    {
        return $this->startOffset;
    }

    public function getEndOffset(): int
    {
        return $this->endOffset;
    }
}

class SpanCollection
{
    private array $spans = [];

    public function addSpan(Span $span): void
    {
        $this->checkThatBlockCollectionMatches($span);
        $this->spans[] = $span;
    }

    public function addSpanUsingOffsets(SixteenPixelBlockCollection $blockCollection, int $startOffset, int $endOffset): void
    {
        $span = new Span($blockCollection, $startOffset, $endOffset);
        $this->checkThatBlockCollectionMatches($span);
        $this->spans[] = $span;
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    public function isBlitterGood()
    {
        foreach ($this->spans as $span) {
            if (!$span->isBlitterGood()) {
                return false;
            }
        }

        return true;
    }

    public function getUniqueSpanLengths(): array
    {
        $uniqueLengths = [];
        foreach ($this->spans as $span) {
            $uniqueLengths[$span->getLength()] = true;
        }

        $uniqueLengths = array_keys($uniqueLengths);
        sort($uniqueLengths, SORT_NUMERIC);

        return $uniqueLengths;
    }

    public function getSpanCollectionByLength(int $length): SpanCollection
    {
        $spanCollection = new self();
        foreach ($this->spans as $span) {
            if ($span->getLength() == $length) {
                $spanCollection->addSpan($span);
            }
        }

        return $spanCollection;
    }

    private function checkThatBlockCollectionMatches(Span $span): void
    {
        if (!empty($this->spans)) {
            $expectedBlockCollection = $this->spans[0]->getBlockCollection();
            if ($span->getBlockCollection() !== $expectedBlockCollection) {
                throw new \Exception('Attempting to add span that belongs to a different block collection');
            }
        }
    }
}

class SixteenPixelBlock {
    private int $sourceOffset;
    private int $destinationOffset;
    private int $maskWord;
    private array $bitplaneWords;

    public function __construct(int $sourceOffset, int $destinationOffset, int $maskWord, array $bitplaneWords)
    {
        $this->sourceOffset = $sourceOffset;
        $this->destinationOffset = $destinationOffset;
        $this->maskWord = $maskWord;
        $this->bitplaneWords = $bitplaneWords;
    }

    public function getSourceOffset(): int
    {
        return $this->sourceOffset;
    }

    public function getDestinationOffset(): int
    {
        return $this->destinationOffset;
    }

    public function getMaskWord(): int
    {
        return $this->maskWord;
    }

    public function getBitplaneWords(): array
    {
        return $this->bitplaneWords;
    }
}

class SixteenPixelBlockCollection
{
    private array $blocks = [];

    public function addBlock(SixteenPixelBlock $block)
    {
        $this->blocks[] = $block;
    }

    public function getBlockByOffset($offset): SixteenPixelBlock
    {
        return $this->blocks[$offset];
    }

    public function getBlockCount(): int
    {
        return count($this->blocks);
    }
}

class CompiledSpriteBuilder {

    const FRAMEBUFFER_BYTES_PER_LINE = 160;
    const BYTES_PER_16_PIXELS = 8;

	private string $data;
	private SixteenPixelBlockCollection $sixteenPixelBlockCollection;
    private array $uniqueLongValues = [];
	private int $widthInSixteenPixelBlocks;
	private int $heightInLines;

	public function __construct(string $data, int $widthInSixteenPixelBlocks, int $heightInLines)
	{
		$this->data = $data; // this will need to be an array of bytes!
		$this->widthInSixteenPixelBlocks = $widthInSixteenPixelBlocks;
		$this->heightInLines = $heightInLines;
        $this->sixteenPixelBlockCollection = new SixteenPixelBlockCollection();

        $sourceOffset = 0;
        $destinationOffset = 0;
        $bytesToSkipAfterEachLine = self::FRAMEBUFFER_BYTES_PER_LINE - $widthInSixteenPixelBlocks * self::BYTES_PER_16_PIXELS;

		for ($y = 0; $y < $this->heightInLines; $y++) {
            for ($x = 0; $x < $this->widthInSixteenPixelBlocks; $x++) {
                $maskWord = $this->getWordAtSourceOffset($sourceOffset);
                $bitplaneWords = [
                    $this->getWordAtSourceOffset($sourceOffset + 2),
                    $this->getWordAtSourceOffset($sourceOffset + 4),
                    $this->getWordAtSourceOffset($sourceOffset + 6),
                    $this->getWordAtSourceOffset($sourceOffset + 8),
                ];

                $this->sixteenPixelBlockCollection->addBlock(
                    new SixteenPixelBlock($sourceOffset, $destinationOffset, $maskWord, $bitplaneWords)
                );

                $sourceOffset += 10;
                $destinationOffset += 8;
            }
            $destinationOffset += $bytesToSkipAfterEachLine;
        }
	}

    private function getWordAtSourceOffset(int $offset): int
    {
        return ord($this->data[$offset])
            | (ord($this->data[$offset+1]) << 8);
    }

	private function getLongAtSourceOffset(int $offset): int
	{
        return ord($this->data[$offset])
            | (ord($this->data[$offset+1]) << 8)
            | (ord($this->data[$offset+2]) << 16)
            | (ord($this->data[$offset+3]) << 24);
	}

    public function runFirstPass()
    {
        $sixteenPixelBlockOffset = 0;
        $destinationOffset = 0;
        $previouslyWrittenDestinationOffset = 0;

                $spans = [];
		for ($y = 0; $y < $this->heightInLines; $y++) {
            echo("** next line\n");

            $maskValues = [];

            $currentBlock = $this->sixteenPixelBlockCollection->getBlockByOffset($sixteenPixelBlockOffset);
            $sourceOffset = $currentBlock->getSourceOffset();

            for ($x = 0; $x < $this->widthInSixteenPixelBlocks; $x++) {
                $maskValues[] = $this->sixteenPixelBlockCollection->getBlockByOffset($sixteenPixelBlockOffset + $x)->getMaskWord();
            }

            // start by stripping off any empty blocks from the beginning and end of the line

            $lineActiveOffsetStart = $sixteenPixelBlockOffset;
            $lineActiveOffsetEnd = ($sixteenPixelBlockOffset + $this->widthInSixteenPixelBlocks) - 1;

            $lineIsEmpty = true;
            for ($offset = $lineActiveOffsetStart; $offset < $lineActiveOffsetEnd; $offset++) {
                if ($this->sixteenPixelBlockCollection->getBlockByOffset($offset)->getMaskWord() != 0xffff) {
                    $lineIsEmpty = false;
                }
            }

            if (!$lineIsEmpty) {
                while ($this->sixteenPixelBlockCollection->getBlockByOffset($lineActiveOffsetStart)->getMaskWord() == 0xffff) {
                    $lineActiveOffsetStart++;
                }
                while ($this->sixteenPixelBlockCollection->getBlockByOffset($lineActiveOffsetEnd)->getMaskWord() == 0xffff) {
                    $lineActiveOffsetEnd--;
                }

                $currentSpan = [
                    'startOffset' => $lineActiveOffsetStart,
                    'endOffset' => null,
                ];
                $spanCurrentlyActive = true;
                echo("scanning from ".$lineActiveOffsetStart." to ".$lineActiveOffsetEnd."\n");
                for ($offset = $lineActiveOffsetStart; $offset <= $lineActiveOffsetEnd; $offset++) {
                    $maskWord = $this->sixteenPixelBlockCollection->getBlockByOffset($offset)->getMaskWord();
                    if ($spanCurrentlyActive) {
                        if ($maskWord == 0xffff) { 
                            $spanCurrentlyActive = false;
                            $currentSpan['endOffset'] = $offset - 1;
                            $spans[] = $currentSpan;
                        }
                    } else {
                        if ($maskWord != 0xffff) {
                            $spanCurrentlyActive = true;
                            $currentSpan = [
                                'startOffset' => $offset,
                                'endOffset' => null,
                            ];
                        }
                    }
                }
                if ($spanCurrentlyActive) {
                    $currentSpan['endOffset'] = $lineActiveOffsetEnd;
                    $spans[] = $currentSpan;
                }

            }

            $sixteenPixelBlockOffset += $this->widthInSixteenPixelBlocks;
		}

        $spanCollection = new SpanCollection($this->sixteenPixelBlockCollection);
        foreach ($spans as $span) {
            $spanCollection->addSpanUsingOffsets($this->sixteenPixelBlockCollection, $span['startOffset'], $span['endOffset']);
        }

        while (!$spanCollection->isBlitterGood()) {
            $replacementSpanCollection = new SpanCollection($this->sixteenPixelBlockCollection);
            foreach ($spanCollection->getSpans() as $span) {
                if (!$span->isBlitterGood()) {
                    $span->splitIntoSpanCollection($replacementSpanCollection);
                } else {
                    $replacementSpanCollection->addSpan($span);
                }
            }
            $spanCollection = $replacementSpanCollection;
        }

        //var_dump($spanCollection);

        /*foreach ($spanCollection->getSpans() as $span) {
            echo("span:\n");
            echo("  startOffset: ".$span->getStartOffset()."\n");
            echo("  endOffset: ".$span->getEndOffset()."\n");
        }*/

        $uniqueSpanLengths = $spanCollection->getUniqueSpanLengths();
        foreach ($uniqueSpanLengths as $length) {
            echo("Spans of length ".$length."\n");
            $lengthBasedSpanCollection = $spanCollection->getSpanCollectionByLength($length);
            foreach ($lengthBasedSpanCollection->getSpans() as $span) {
                $blockCollection = $span->getBlockCollection();
                echo("  span:\n");
                echo("    startOffset: ".$span->getStartOffset()."\n");
                echo("    endOffset: ".$span->getEndOffset()."\n");
                echo("    length: ".$span->getLength()."\n");
                echo("    masks:\n");
                switch ($length) {
                    case 1:
                        printf(
                            "      endmask1: %x\n",
                            $blockCollection->getBlockByOffset($span->getStartOffset())->getMaskWord()
                        );
                        break;
                    case 2:
                        printf(
                            "      endmask1: %x\n      endmask3: %x\n",
                            $blockCollection->getBlockByOffset($span->getStartOffset())->getMaskWord(),
                            $blockCollection->getBlockByOffset($span->getStartOffset()+1)->getMaskWord()
                        );
                        break;
                    default:
                        printf(
                            "      endmask1: %x\n      endmask2: %x\n      endmask3: %x\n",
                            $blockCollection->getBlockByOffset($span->getStartOffset())->getMaskWord(),
                            $blockCollection->getBlockByOffset($span->getStartOffset()+1)->getMaskWord(),
                            $blockCollection->getBlockByOffset($span->getEndOffset()+1)->getMaskWord()
                        );
                        break;
                }
            }
        }



        // so now we have a load of spans
        // some spans may be more than 4 blocks wide, but we may still be able to use them
        // if the middle blocks all have the same mask




        // one pass - check if blitter good
        /*foreach ($spans as $key => $span) {
            $spanLength = ($span['endOffset'] - $span['startOffset']) + 1;
            if ($spanLength < 4) {
                $spans[$key]['blitterGood'] = true;
            } else {
                $middleMaskWords = [];
                for ($offset = $span['startOffset'] + 1; $offset < $span['endOffset']; $offset++) {
                    $middleMaskWords[] = $this->sixteenPixelBlockCollection->getBlockByOffset($offset)->getMaskWord();
                }

                $uniqueMiddleMaskWords = array_unique($middleMaskWords);
                $spans[$key]['blitterGood'] = (count($uniqueMiddleMaskWords) == 1);
                $spans[$key]['uniqueMiddleMaskWords'] = $uniqueMiddleMaskWords;
            }
        }*/



        //var_dump($spans);
    }
}

$treeWords = [65535, 0, 0, 0, 0, 65389, 144, 0, 146, 0, 45567, 3072, 0, 19968, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 34645, 26794, 0, 30890, 0, 231, 18704, 0, 65304, 0, 53247, 4096, 0, 12288, 0, 65535, 0, 0, 0, 0, 32897, 32320, 0, 32638, 0, 4, 45225, 0, 65531, 0, 4095, 49152, 0, 61440, 0, 65533, 2, 0, 2, 0, 49412, 16089, 0, 16123, 0, 2, 47761, 0, 65533, 0, 4095, 4096, 0, 61440, 0, 65529, 6, 0, 6, 0, 49216, 674, 0, 16319, 0, 12, 898, 0, 65523, 0, 511, 28672, 0, 65024, 0, 65496, 39, 0, 39, 0, 0, 42958, 0, 65535, 0, 0, 16388, 0, 65535, 0, 5119, 0, 0, 60416, 0, 65280, 119, 0, 255, 0, 0, 17511, 0, 65535, 0, 0, 49284, 0, 65535, 0, 207, 16384, 0, 65328, 0, 62208, 2238, 0, 3327, 0, 0, 33029, 0, 65535, 0, 0, 41145, 0, 65535, 0, 1039, 6240, 0, 64496, 0, 59904, 282, 0, 5631, 0, 0, 2152, 0, 65535, 0, 0, 276, 0, 65535, 0, 31, 57472, 0, 65504, 0, 63488, 356, 0, 2047, 0, 0, 5099, 0, 65535, 0, 0, 224, 0, 65535, 0, 63, 2688, 0, 65472, 0, 55296, 961, 0, 10239, 0, 0, 48576, 0, 65535, 0, 0, 3203, 0, 65535, 0, 273, 16518, 0, 65262, 0, 7680, 8348, 0, 57855, 0, 0, 59195, 0, 65535, 0, 0, 5121, 0, 65535, 0, 3, 2500, 0, 65532, 0, 34304, 31187, 0, 31231, 0, 0, 6224, 0, 65535, 0, 0, 33320, 0, 65535, 0, 3, 2076, 0, 65532, 0, 49152, 15081, 0, 16383, 0, 0, 34960, 0, 65535, 0, 0, 20896, 0, 65535, 0, 19, 1088, 0, 65516, 0, 57344, 3580, 0, 8191, 0, 0, 25552, 0, 65535, 0, 0, 5600, 0, 65535, 0, 3, 1248, 0, 65532, 0, 20480, 36064, 0, 45055, 0, 0, 12665, 0, 65535, 0, 0, 40008, 0, 65535, 0, 11, 8452, 0, 65524, 0, 34816, 16656, 0, 30719, 0, 0, 14400, 0, 65535, 0, 0, 23296, 0, 65535, 0, 1, 150, 0, 65534, 0, 50176, 14826, 0, 15359, 0, 0, 53480, 0, 65535, 0, 0, 257, 0, 65535, 0, 1, 8416, 0, 65534, 0, 49408, 12888, 0, 16127, 0, 0, 17541, 0, 65535, 0, 0, 10312, 0, 65535, 0, 1, 33810, 0, 65534, 0, 61696, 2561, 0, 3839, 0, 0, 1050, 0, 65535, 0, 0, 49824, 0, 65535, 0, 3, 56400, 0, 65532, 0, 59392, 5089, 0, 6143, 0, 0, 49152, 0, 65535, 0, 0, 40960, 0, 65535, 0, 1, 34828, 0, 65534, 0, 63520, 657, 0, 2015, 0, 49152, 77, 0, 16383, 0, 0, 40548, 0, 65535, 0, 7, 22784, 0, 65528, 0, 64592, 416, 0, 943, 0, 0, 33608, 0, 65535, 0, 0, 16443, 0, 65535, 0, 15, 34864, 0, 65520, 0, 65472, 33, 0, 63, 0, 0, 54113, 8192, 57343, 8192, 8192, 1658, 49152, 8191, 49152, 519, 44272, 0, 65016, 0, 65415, 96, 0, 120, 0, 16384, 11297, 4096, 45055, 4096, 8320, 39008, 16384, 40831, 16384, 207, 11552, 0, 65328, 0, 65409, 120, 0, 126, 0, 28672, 34656, 2048, 34815, 2048, 2624, 1042, 0, 62911, 0, 7, 96, 0, 65528, 0, 65324, 146, 0, 211, 0, 12556, 51282, 1536, 51443, 1536, 15488, 121, 33024, 17023, 33024, 31, 47456, 0, 65504, 0, 65045, 298, 0, 490, 0, 14848, 50190, 0, 50687, 0, 3584, 328, 24576, 37375, 24576, 32799, 2432, 0, 32736, 0, 65515, 16, 0, 20, 0, 61440, 3648, 0, 4095, 0, 0, 23818, 0, 65535, 0, 255, 59136, 0, 65280, 0, 65504, 0, 0, 31, 0, 49152, 7872, 23, 16360, 23, 0, 10690, 32768, 32767, 32768, 61439, 4096, 0, 4096, 0, 65533, 0, 0, 2, 0, 49676, 11376, 0, 15859, 0, 1029, 16962, 2048, 62458, 2048, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 61730, 3777, 0, 3805, 0, 1664, 39204, 24576, 39295, 24576, 16383, 0, 0, 49152, 0, 65528, 7, 0, 7, 0, 24582, 34080, 0, 40953, 0, 3018, 42036, 20480, 42037, 20480, 12287, 20480, 0, 53248, 0, 65531, 4, 0, 4, 0, 1158, 22592, 0, 64377, 0, 510, 3585, 24576, 40449, 24576, 4095, 61440, 0, 61440, 0, 65535, 0, 0, 0, 0, 60930, 68, 0, 4605, 0, 8191, 0, 49152, 8192, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65408, 56, 0, 127, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 0, 0, 7, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 1, 2, 1, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 1, 2, 1, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 49152, 0, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 2, 0, 7, 0, 16383, 0, 49152, 0, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 6, 1, 6, 1, 8191, 0, 49152, 8192, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65472, 8, 49, 14, 49, 1023, 32768, 19456, 45056, 19456, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65024, 20, 457, 54, 457, 255, 16384, 13056, 52224, 13056, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 63488, 0, 1075, 972, 1075, 63, 0, 56384, 9088, 56384, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 61440, 16, 4079, 16, 4079, 31, 0, 59360, 6144, 59360, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 64512, 0, 1023, 0, 1023, 127, 0, 65408, 0, 65408, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65472, 0, 63, 0, 63, 2047, 0, 63488, 0, 63488, 65535, 0, 0, 0, 0];
$treeWidth = 4;
$treeHeight = 54;

$carWords = [65535, 0, 0, 0, 0, 61441, 56, 4036, 4036, 58, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 32768, 4351, 26880, 27904, 4863, 16383, 0, 32768, 32768, 16384, 65535, 0, 0, 0, 0, 65534, 0, 1, 1, 0, 0, 16387, 33741, 33741, 31794, 4095, 49152, 8192, 8192, 53248, 65535, 0, 0, 0, 0, 65532, 0, 3, 3, 0, 0, 15, 3603, 28179, 37356, 2047, 61440, 18432, 18432, 45056, 65535, 0, 0, 0, 0, 49152, 8176, 8206, 8206, 8177, 0, 3, 31, 61471, 4064, 15, 64960, 45600, 45600, 19920, 65535, 0, 0, 0, 0, 32768, 30712, 30726, 30726, 2041, 0, 3, 11, 65035, 500, 3, 64240, 56584, 56584, 8948, 65535, 0, 0, 0, 0, 0, 64504, 64518, 64519, 1016, 0, 2055, 2055, 34823, 29176, 1, 65404, 61058, 61058, 4476, 65528, 4, 4, 4, 0, 0, 40192, 57087, 40703, 16640, 0, 2055, 2055, 2055, 61944, 0, 65535, 63487, 63487, 2048, 0, 65532, 65532, 65532, 0, 0, 3776, 36671, 3903, 32960, 0, 6655, 39423, 47615, 16384, 0, 65535, 65535, 65535, 0, 0, 65532, 65532, 65532, 0, 0, 3967, 36851, 4083, 32780, 0, 63999, 63999, 63999, 0, 0, 65535, 65535, 65535, 0, 0, 65532, 65532, 65532, 0, 0, 35838, 40956, 40956, 3, 0, 47615, 47615, 47615, 16384, 0, 65535, 65415, 65415, 120, 0, 65532, 65532, 65532, 0, 0, 35583, 36351, 36351, 4608, 0, 63999, 63999, 63999, 0, 0, 65408, 63615, 63615, 1920, 0, 12, 65532, 65532, 0, 0, 35535, 36335, 36303, 544, 0, 30720, 63999, 63999, 0, 0, 127, 65408, 65408, 127, 0, 65520, 12, 12, 65520, 0, 36231, 36551, 36487, 320, 0, 39423, 63488, 63488, 511, 0, 65408, 0, 0, 65535, 1, 0, 2, 2, 65532, 32768, 3719, 4039, 3975, 64, 0, 49152, 63488, 63488, 2047, 0, 0, 0, 0, 65288, 1, 1532, 1534, 1534, 512, 32768, 3975, 4039, 3975, 64, 0, 57280, 65472, 65472, 32, 0, 0, 0, 0, 4351, 1, 1532, 1534, 1534, 64000, 32768, 3975, 4039, 3975, 80, 0, 57280, 65472, 65472, 63, 0, 128, 136, 136, 65296, 1, 1532, 1534, 1534, 512, 49152, 1923, 1987, 1923, 72, 0, 57280, 65472, 65472, 32, 0, 128, 4240, 4240, 2336, 1, 3072, 3118, 3118, 976, 64512, 899, 963, 899, 88, 0, 49152, 49152, 49152, 16352, 0, 128, 2208, 2208, 1344, 7, 5112, 5112, 5112, 0, 65024, 387, 451, 387, 72, 0, 49120, 49120, 49120, 16384, 0, 2112, 3072, 3072, 704, 15, 26608, 26608, 26608, 0, 65280, 130, 194, 130, 81, 0, 65472, 65472, 65472, 0, 0, 2688, 3200, 3200, 832, 31, 0, 0, 0, 0, 65408, 64, 64, 64, 0, 0, 0, 0, 0, 0, 0, 2304, 2895, 2895, 1200, 31, 0, 2304, 2304, 5248, 65504, 0, 0, 0, 0, 0, 0, 17535, 17535, 34816, 48, 0, 61440, 61440, 4032, 63, 0, 4608, 4608, 11520, 65504, 0, 0, 0, 1, 127, 0, 34816, 34816, 29696, 65528, 0, 0, 0, 0, 63, 0, 0, 0, 0, 65520, 0, 0, 0, 0, 255, 0, 0, 0, 0, 65534, 0, 0, 0, 0, 255, 0, 0, 0, 0];
$carWidth = 4;
$carHeight = 25;

$str= '';
foreach ($carWords as $word) {
    $str .= chr($word >> 8);
    $str .= chr($word & 255);
}

$builder = new CompiledSpriteBuilder($str, $carWidth, $carHeight);
$builder->runFirstPass();

//var_dump($instructionStream);

