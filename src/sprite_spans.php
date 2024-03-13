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

    public function canUseFxsr(int $skewed): bool
    {
        $skewCalculatorLong = 0b11111111111111110000000000000000;

        $endmask1 = $this->blockCollection->getBlockByOffset($this->startOffset)->getInvertedMaskWord();

        $useFxsr = false;
        if ($skewed) {
            if ((($skewCalculatorLong >> $skewed) & 0xffff) & $endmask1) {
                $useFxsr = true;
            }
        }

        return $useFxsr;
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

    public function getSpanCollectionByFxsrEligibility(bool $fxsr, int $skewed): SpanCollection
    {
        $spanCollection = new self();
        foreach ($this->spans as $span) {
            if ($span->canUseFxsr($skewed) == $fxsr) {
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
    private int $originalSourceOffset;
    private int $sourceOffset;
    private int $destinationOffset;
    private int $maskWord;
    private array $bitplaneWords;

    public function __construct(int $originalSourceOffset, int $sourceOffset, int $destinationOffset, int $maskWord, array $bitplaneWords)
    {
        $this->originalSourceOffset = $originalSourceOffset;
        $this->sourceOffset = $sourceOffset;
        $this->destinationOffset = $destinationOffset;
        $this->maskWord = $maskWord;
        $this->bitplaneWords = $bitplaneWords;
    }

    public function getOriginalSourceOffset(): int
    {
        return $this->originalSourceOffset;
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

    public function getInvertedMaskWord(): int
    {
        return ~($this->maskWord) & 0xffff;
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
    private int $skewed;

	public function __construct(string $data, int $widthInSixteenPixelBlocks, int $heightInLines, int $skewed)
	{
		$this->data = $data; // this will need to be an array of bytes!
		$this->widthInSixteenPixelBlocks = $widthInSixteenPixelBlocks;
		$this->heightInLines = $heightInLines;
        $this->skewed = $skewed;
        $this->sixteenPixelBlockCollection = new SixteenPixelBlockCollection();

        $originalSourceOffset = 0;
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
                    new SixteenPixelBlock($originalSourceOffset, $sourceOffset, $destinationOffset, $maskWord, $bitplaneWords)
                );

                if ($skewed) {
                    if ($x < $this->widthInSixteenPixelBlocks - 1) {
                        $originalSourceOffset += 10;
                    }
                } else {
                    $originalSourceOffset += 10;
                }

                $sourceOffset += 10;
                $destinationOffset += 8;
            }
            $destinationOffset += $bytesToSkipAfterEachLine;
        }
	}

    private function getWordAtSourceOffset(int $offset): int
    {
        return ord($this->data[$offset+1])
            | (ord($this->data[$offset]) << 8);
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
            //echo("** next line\n");

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
                //echo("scanning from ".$lineActiveOffsetStart." to ".$lineActiveOffsetEnd."\n");
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

       $instructions = [];

        // mSkewFXSR      equ  $80
        // mSkewNFSR      equ  $40

        $endmask1 = null;
        $endmask2 = null;
        $endmask3 = null;

        $oldEndmask1 = null;
        $oldEndmask2 = null;
        $oldEndmask3 = null;

        $oldSourceOffset = 0;
        $oldDestinationOffset = 0;

        $uniqueSpanLengths = $spanCollection->getUniqueSpanLengths();
        foreach ($uniqueSpanLengths as $length) {
            $instructions[] = '';

            $destinationYIncrement = -((8 * ($length - 1)) - 2); // dest y increment = (Dest x increment * (x count - 1)) -2

            $instructions[] = sprintf(
                'move.w #%d,$ffff8a30.w ; dest y increment (per length group)',
                $destinationYIncrement
            );

            $instructions[] = sprintf(
                'move.w #%d,$ffff8a36.w ; x count (per length group)',
                $length
            );

            $lengthBasedSpanCollection = $spanCollection->getSpanCollectionByLength($length);
            $fxsrOptions = [true, false];
            foreach ($fxsrOptions as $useFxsr) {
                $fxsrBasedSpanCollection = $lengthBasedSpanCollection->getSpanCollectionByFxsrEligibility($useFxsr, $this->skewed);

                $spans = $fxsrBasedSpanCollection->getSpans();

                if (count($spans)) {
                    $sourceYIncrement = -((10 * ($length - 1)) - 2); // source y increment = (source x increment * (x count - 1)) -2
                    if ($useFxsr) {
                        $sourceYIncrement -= 10;
                    }

                    $instructions[] = sprintf(
                        'move.w #%d,$ffff8a22.w ; source y increment (per fxsr eligibility)',
                        $sourceYIncrement
                    );
                }

                foreach ($spans as $span) {
                    $blockCollection = $span->getBlockCollection();
                    $endmask1 = $blockCollection->getBlockByOffset($span->getStartOffset())->getInvertedMaskWord();

                    //$useFxsr = $span->canUseFxsr($this->skewed);

                    $sourceOffset = $blockCollection->getBlockByOffset($span->getStartOffset())->getOriginalSourceOffset() + 2;
                    if ($useFxsr) {
                        $sourceOffset -= 10;
                    }

                    $instructions[] = '';
                    $instructions[] = sprintf(
                        'lea.l %d(a0),a0 ; calc source address into a2',
                        $sourceOffset - $oldSourceOffset
                    );
                    $instructions[] = 'move.l a0,(a3) ; set source address';

                    $oldSourceOffset = $sourceOffset;


                    $destinationOffset = $blockCollection->getBlockByOffset($span->getStartOffset())->getDestinationOffset();
                    $instructions[] = sprintf(
                        'lea.l %d(a1),a1 ; calc destination address into a2',
                        $destinationOffset - $oldDestinationOffset
                    );
                    $instructions[] = 'move.l a1,(a4) ; set destination address';
                    $instructions[] = 'move.w d0,(a5) ; set ycount (4 bitplanes)';

                    $oldDestinationOffset = $destinationOffset;

                    switch ($length) {
                        case 1:
                            if ($endmask1 != $oldEndmask1) {
                                if ($endmask1 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a28.w ; set endmask1';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a28.w ; set endmask1',
                                        $endmask1
                                    );
                                }
                            }
                            break;
                        case 2:
                            $endmask3 = $blockCollection->getBlockByOffset($span->getStartOffset()+1)->getInvertedMaskWord();

                            if ($endmask1 != $oldEndmask1) {
                                if ($endmask1 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a28.w ; set endmask1';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a28.w ; set endmask1',
                                        $endmask1
                                    );
                                }
                            }
                            if ($endmask3 != $oldEndmask3) {
                                if ($endmask3 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a2c.w ; set endmask1';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a2c.w ; set endmask3',
                                        $endmask3
                                    );
                                }
                            }
                            break;
                        default:
                            $endmask2 = $blockCollection->getBlockByOffset($span->getStartOffset()+1)->getInvertedMaskWord();
                            $endmask3 = $blockCollection->getBlockByOffset($span->getEndOffset())->getInvertedMaskWord();

                            if ($endmask1 != $oldEndmask1) {
                                if ($endmask1 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a28.w ; set endmask1';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a28.w ; set endmask1',
                                        $endmask1
                                    );
                                }
                            }
                            if ($endmask2 != $oldEndmask2 && $endmask3 != $oldEndmask3) {
                                if ($endmask2 == 0xffff && $endmask3 == 0xffff) {
                                    $instructions[] = 'move.l d7,$ffff8a2a.w ; set endmask2 and endmask3';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.l #$%x,$ffff8a2a.w ; set endmask2 and endmask3',
                                        (($endmask2 << 16) | $endmask3) & 0xffffffff
                                    );
                                }
                            } elseif ($endmask2 != $oldEndmask2) {
                                if ($endmask2 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a2a.w ; set endmask2';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a2a.w ; set endmask2',
                                        $endmask2
                                    );
                                }
                            } elseif ($endmask3 != $oldEndmask3) {
                                if ($endmask3 == 0xffff) {
                                    $instructions[] = 'move.w d7,$ffff8a2c.w ; set endmask3';
                                } else {
                                    $instructions[] = sprintf(
                                        'move.w #$%x,$ffff8a2c.w ; set endmask3',
                                        $endmask3
                                    );
                                }
                            }
                            break;
                    }

                    $oldEndmask1 = $endmask1;
                    $oldEndmask2 = $endmask2;
                    $oldEndmask3 = $endmask3;

                    if ($useFxsr) {
                        $instructions[] = 'move.w d2,(a6) ; set blitter control with fxsr';
                    } else {
                        $instructions[] = 'move.w d1,(a6) ; set blitter control no fxsr';
                    }
                } // end

            }


        }

        $instructions[] = 'rts';

        return $instructions;
    }
}


