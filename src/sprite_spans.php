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
        // TODO
        // try different combinations of splits to find two "blitter good" spans
        // if this fails, try different combinations of splits to find three "blitter good" spans
        // etc etc


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

        /*foreach ($spanCollection->getSpans() as $span) {
            if (!$span->isBlitterGood()) {
                echo("split didn't give blitter good spans!\n");
                exit(1);
            }
        }*/
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
        //return true;

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

    public function canUseNfsr(int $skewed): bool
    {
        if (!$skewed) {
            return false;
        }

        if ($this->getLength() == 1) {
            return false;
        }

        $skewCalculatorLong = 0b00000000000000001111111111111111;

        $endmask = $this->blockCollection->getBlockByOffset($this->endOffset)->getInvertedMaskWord();

        $useNfsr = false;
        if ((($skewCalculatorLong >> $skewed) & 0xffff) & $endmask) {
            $useNfsr = true;
        }

        return !$useNfsr;
    }

    public function getApplicableEndmasks()
    {
        $endmasks = [];
        $length = $this->getLength();

        $endmasks[1] = $this->blockCollection->getBlockByOffset($this->startOffset)->getInvertedMaskWord();
        if ($length == 2) {
            $endmasks[3] = $this->blockCollection->getBlockByOffset($this->startOffset+1)->getInvertedMaskWord();
        } elseif ($length > 2) {
            $endmasks[2] = $this->blockCollection->getBlockByOffset($this->startOffset+1)->getInvertedMaskWord();
            $endmasks[3] = $this->blockCollection->getBlockByOffset($this->endOffset)->getInvertedMaskWord();
        }

        return $endmasks;
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

        $uniqueApplicableEndmasks = [];
        foreach ($spanCollection->getSpans() as $span) {
            $applicableEndmasks = $span->getApplicableEndmasks();
            $applicableEndmasksString = http_build_query($applicableEndmasks, '', ',');
            $uniqueApplicableEndmasks[$applicableEndmasksString] = true;
        }

        $reorderedSpanCollection = new self();
        foreach (array_keys($uniqueApplicableEndmasks) as $applicableEndmasksSearchString) {
            foreach ($spanCollection->getSpans() as $span) {
                $applicableEndmasks = $span->getApplicableEndmasks();
                $applicableEndmasksString = http_build_query($applicableEndmasks, '', ',');

                if ($applicableEndmasksString == $applicableEndmasksSearchString) {
                    $reorderedSpanCollection->addSpan($span);
                }
            }
        }

        return $reorderedSpanCollection;
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

    public function getSpanCollectionByNfsrEligibility(bool $nfsr, int $skewed): SpanCollection
    {
        $spanCollection = new self();
        foreach ($this->spans as $span) {
            if ($span->canUseNfsr($skewed) == $nfsr) {
                $spanCollection->addSpan($span);
            }
        }

        return $spanCollection;
    }

    public function getSpansOrderedByStartOffset(): array
    {
        $spans = $this->getSpans();
        usort($spans, ['SpanCollection', 'compare']);

        return $spans;
    }

    static function compare($a, $b)
    {
        $aStartOffset = $a->getStartOffset();
        $bStartOffset = $b->getStartOffset();

        if ($aStartOffset == $bStartOffset) {
            return 0;
        }
        return ($aStartOffset < $bStartOffset) ? -1 : 1;
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

class InstructionStream
{
    private array $instructions = [];

    public function add(string $instruction)
    {
        $this->instructions[] = $instruction;
    }

    public function getArray(): array
    {
        return $this->instructions;
    }

    public function appendStream(InstructionStream $stream): void
    {
        foreach ($stream->getArray() as $instruction) {
            $this->add($instruction);
        }
    }
}

class CompiledSpriteBuilder {

    const FRAMEBUFFER_BYTES_PER_LINE = 160;
    const BYTES_PER_16_PIXELS = 8;
    const BLITTER_COPY_THRESHOLD = 6;

	private string $data;
	private SixteenPixelBlockCollection $sixteenPixelBlockCollection;
    private array $uniqueLongValues = [];
	private int $widthInSixteenPixelBlocks;
	private int $heightInLines;
    private int $skewed;
    private int $drawOffsetSourceAdjust;
    private int $drawOffsetDestinationAdjust;

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
        /*if ($this->skewed & 1) {
            return [];
        }*/

        $sixteenPixelBlockOffset = 0;
        $destinationOffset = 0;
        $previouslyWrittenDestinationOffset = 0;

        $spans = [];
		for ($y = 0; $y < $this->heightInLines; $y++) {

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
            for ($offset = $lineActiveOffsetStart; $offset <= $lineActiveOffsetEnd; $offset++) {
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

                if ((($lineActiveOffsetEnd - $lineActiveOffsetStart) + 1) <= 3) {
                    $spans[] = [
                        'startOffset' => $lineActiveOffsetStart,
                        'endOffset' => $lineActiveOffsetEnd,
                    ];
                } else {
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

        $instructionStream = new InstructionStream();

        $instructionStream->add('lea $ffff8a28.w,a2        ; cache endmask1');

        // mSkewFXSR      equ  $80
        // mSkewNFSR      equ  $40

        $endmasks = [
            1 => null,
            2 => null,
            3 => null,
        ];

        $oldEndmasks = $endmasks;

        $oldSourceOffset = 0;
        $oldDestinationOffset = 0;

        $sourceAdvance = null;
        $destinationAdvance = null;

        $oldSourceAdvance = null;
        $oldDestinationAdvance = null;

        $oldDestinationYIncrement = null;

        $this->drawOffsetSourceAdjust = 0;
        $this->drawOffsetDestinationAdjust = 0;

        $loopState = [
            'loopStartSourceOffset' => 0,
            'loopStartDestinationOffset' => 0,
        ];

        $uniqueSpanLengths = $spanCollection->getUniqueSpanLengths();
        $loopIndex = 1;
        foreach ($uniqueSpanLengths as $length) {
            $instructionStream->add('');

            //$destinationYIncrement = -((8 * ($length - 1)) - 2); // dest y increment = (Dest x increment * (x count - 1)) -2

            // TODO: this may need to change on a per span basis!
            /*$instructionStream->add(
                sprintf(
                    'move.w #%d,$ffff8a30.w ; dest y increment (per length group)',
                    $destinationYIncrement
                )
            );*/

            // this is fine
            $instructionStream->add(
                sprintf(
                    'move.w #%d,$ffff8a36.w ; x count (per length group)',
                    $length
                )
            );

            $lengthBasedSpanCollection = $spanCollection->getSpanCollectionByLength($length);
            $fxsrOptions = [true, false];
            foreach ($fxsrOptions as $useFxsr) {
                $fxsrBasedSpanCollection = $lengthBasedSpanCollection->getSpanCollectionByFxsrEligibility($useFxsr, $this->skewed);

                $nfsrOptions = [true, false];
                foreach ($nfsrOptions as $useNfsr) {
                    $nfsrBasedSpanCollection = $fxsrBasedSpanCollection->getSpanCollectionByNfsrEligibility($useNfsr, $this->skewed);

                    $spans = $nfsrBasedSpanCollection->getSpansOrderedByStartOffset();

                    $oldSourceYIncrement = null;
                    foreach ($spans as $key => $span) {
                        $blockCollection = $span->getBlockCollection();

                        $applicableEndmasks = $span->getApplicableEndmasks();

                        $changedEndmasks = [];
                        for ($index = 1; $index <= 3; $index++) {
                            if (isset($applicableEndmasks[$index])) {
                                $endmasks[$index] = $applicableEndmasks[$index];
                                if ($endmasks[$index] != $oldEndmasks[$index]) {
                                    $changedEndmasks[$index] = $endmasks[$index];
                                }
                            }
                        }

                        $oldEndmasks = $endmasks;

                        $sourceOffset = $blockCollection->getBlockByOffset($span->getStartOffset())->getOriginalSourceOffset() + 2;
                        if ($useFxsr) {
                            $sourceOffset -= 10;
                        }
                        $destinationOffset = $blockCollection->getBlockByOffset($span->getStartOffset())->getDestinationOffset();

                        $oldSourceAdvance = $sourceAdvance;
                        $oldDestinationAdvance = $destinationAdvance;

                        $sourceAdvance = $sourceOffset - $oldSourceOffset;
                        $destinationAdvance = $destinationOffset - $oldDestinationOffset; 

                        $oldSourceOffset = $sourceOffset;
                        $oldDestinationOffset = $destinationOffset;

                        $state = [
                            'loopStartSourceAdvance' => $sourceAdvance,
                            'loopStartDestinationAdvance' => $destinationAdvance,
                            'loopStartSourceOffset' => $sourceOffset,
                            'loopStartDestinationOffset' => $destinationOffset,
                            'subsequentSourceAdvance' => null,
                            'subsequentDestinationAdvance' => null,
                            'copyInstructionIterations' => 1,
                            'useFxsr' => $useFxsr,
                            'useNfsr' => $useNfsr,
                            'changedEndmasks' => $changedEndmasks,
                        ];

                        //$instructionStream->add('');
                        //$instructionStream->add('; ** span source offset = '.$sourceOffset);
                        //$instructionStream->add('; ** span destination offset = '.$destinationOffset);

                        //$instructionStream->add('; span index '.$key);
                        if ($key == array_key_first($spans)) {
                            //$instructionStream->add('; first iteration');
                            //$instructionStream->add('; new loop started at beginning of fxsr group list');
                            //$instructionStream->add('; - sourceOffset = '.$sourceOffset);
                            //$instructionStream->add('; - destinationOffset = '.$destinationOffset);
                            //$instructionStream->add('; - loopStartSourceOffset = '.$loopState['loopStartSourceOffset']);
                            //$instructionStream->add('; - loopStartDestinationOffset = '.$loopState['loopStartDestinationOffset']);
                            //$instructionStream->add('; drawOffsetSourceAdjust = '.$this->drawOffsetSourceAdjust);
                            //$instructionStream->add('; drawOffsetDestinationAdjust = '.$this->drawOffsetDestinationAdjust);

                            //$instructionStream->add('lea '.(($sourceOffset - $loopState['loopStartSourceOffset']) - $this->drawOffsetSourceAdjust).'(a0),a0 ; advance source at beginning of loop ('.$loopState['loopStartSourceAdvance'].' - '.$this->drawOffsetSourceAdjust.')');
                            //$instructionStream->add('lea '.(($destinationOffset - $loopState['loopStartDestinationOffset']) - $this->drawOffsetDestinationAdjust).'(a1),a1 ; advance destination at beginning of loop ('.$loopState['loopStartDestinationAdvance'].' - '.$this->drawOffsetDestinationAdjust.')');

                            $instructionStream->add('');
                            $instructionStream->add('lea '.(($sourceOffset - $loopState['loopStartSourceOffset']) - $this->drawOffsetSourceAdjust).'(a0),a0 ; calc source address');
                            $instructionStream->add('lea '.(($destinationOffset - $loopState['loopStartDestinationOffset']) - $this->drawOffsetDestinationAdjust).'(a1),a1 ; calc destination address');

                            $loopState = $state;
                        } else {
                            if ($loopState['copyInstructionIterations'] == 1) {
                                $loopState['subsequentSourceAdvance'] = $sourceAdvance;
                                $loopState['subsequentDestinationAdvance'] = $destinationAdvance;
                                /*$instructionStream->add('');
                                $instructionStream->add('; first iteration');
                                $instructionStream->add('; - set loopstate subsequent source advance to '.$sourceAdvance);
                                $instructionStream->add('; - set loopstate subsequent destination advance to '.$destinationAdvance);*/
                            }
                            if ($sourceAdvance != $loopState['subsequentSourceAdvance'] || $destinationAdvance != $loopState['subsequentDestinationAdvance'] || count($changedEndmasks)) {
                                /*$instructionStream->add('');
                                $instructionStream->add('; loop terminated at '.$loopState['copyInstructionIterations'].' iterations, writing instructions because:');
                                if ($loopState['subsequentSourceAdvance'] != $sourceAdvance) {
                                    $instructionStream->add('; - sourceAdvance changed from '.$loopState['subsequentSourceAdvance'].' to '.$sourceAdvance);
                                }
                                if ($loopState['subsequentDestinationAdvance'] != $destinationAdvance) {
                                    $instructionStream->add('; - destinationAdvance changed from '.$loopState['subsequentDestinationAdvance'].' to '.$destinationAdvance);
                                }
                                if (count($changedEndmasks)) {
                                    $instructionStream->add('; - endmasks changed');
                                }*/

                                $sourceYIncrement = $this->calculateSourceYIncrement($loopState, $length);
                                $destinationYIncrement = $this->calculateDestinationYIncrement($loopState, $length);
                                $loopIndex = $this->addConfirmCopyInstructions(
                                    $loopState,
                                    $instructionStream,
                                    $loopIndex,
                                    $sourceYIncrement !== $oldSourceYIncrement ? $sourceYIncrement : null,
                                    $destinationYIncrement !== $oldDestinationYIncrement ? $destinationYIncrement: null
                                );
                                $oldSourceYIncrement = $sourceYIncrement;
                                $oldDestinationYIncrement = $destinationYIncrement;

                                //$instructionStream->add('');
                                //$instructionStream->add('; new loop started elsewhere in spans');
                                //$instructionStream->add('; - sourceOffset = '.$sourceOffset);
                                //$instructionStream->add('; - destinationOffset = '.$destinationOffset);
                                //$instructionStream->add('; - loopStartSourceOffset = '.$loopState['loopStartSourceOffset']);
                                //$instructionStream->add('; - loopStartDestinationOffset = '.$loopState['loopStartSourceOffset']);
                                //$instructionStream->add('; drawOffsetSourceAdjust = '.$this->drawOffsetSourceAdjust);
                                //$instructionStream->add('; drawOffsetDestinationAdjust = '.$this->drawOffsetDestinationAdjust);

                                $instructionStream->add('');
                                $instructionStream->add('lea '.(($sourceOffset - $loopState['loopStartSourceOffset']) - $this->drawOffsetSourceAdjust).'(a0),a0 ; calc source address');
                                $instructionStream->add('lea '.(($destinationOffset - $loopState['loopStartDestinationOffset']) - $this->drawOffsetDestinationAdjust).'(a1),a1 ; calc destination address');

                                $loopState = $state;
                            } else {
                                $loopState['copyInstructionIterations']++;
                            }
                        }

                        if ($key == array_key_last($spans)) {
                            $sourceYIncrement = $this->calculateSourceYIncrement($loopState, $length);
                            $destinationYIncrement = $this->calculateDestinationYIncrement($loopState, $length);
                            /*$instructionStream->add('');
                            $instructionStream->add('; loop terminated because end of fxsr group spans, writing instructions');*/
                            $loopIndex = $this->addConfirmCopyInstructions(
                                $loopState,
                                $instructionStream,
                                $loopIndex,
                                $sourceYIncrement !== $oldSourceYIncrement ? $sourceYIncrement: null,
                                $destinationYIncrement !== $oldDestinationYIncrement ? $destinationYIncrement: null
                            );
                            $oldDestinationYIncrement = $destinationYIncrement;
                            $oldSourceYIncrement = $sourceYIncrement;
                        }
                    }
                }
            }
        }


        $instructionArray = $instructionStream->getArray();

        // another hack to remove redundant leas :)

        /*$lastSourceResetKey = null;
        $lastSourceResetValue = null;
        $lastDestinationResetKey = null;
        $lastDestinationResetValue = null;
        $lastSourceBlitterWriteKey = null;
        $lastDestinationBlitterWriteKey = null;
        foreach ($instructionArray as $key => $instruction) {
            if (str_starts_with($instruction, 'move.w a1,(a4)')) {
                $lastDestinationBlitterWriteKey = $key;
            } elseif (str_starts_with($instruction, 'move.w a0,(a3)')) {
                $lastSourceBlitterWriteKey = $key;
            } elseif (str_contains($instruction, 'reset destination address')) {
                $lastDestinationResetKey = $key;
                $instructionStartingAtNumber = substr($instruction, 6);
                $lastDestinationResetValue = intval(substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '(')));
            } elseif (str_contains($instruction, 'reset source address')) {
                $lastSourceResetKey = $key;
                $instructionStartingAtNumber = substr($instruction, 6);
                $lastSourceResetValue = intval(substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '(')));
            } elseif (str_contains($instruction, 'calc destination address')) {
                if ($lastDestinationResetKey) {
                    $instructionStartingAtNumber = substr($instruction, 6);
                    $calcValue = intval(substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '(')));
                    $adjustedValue = $calcValue + $lastDestinationResetValue;

                    $instructionArray[$lastDestinationResetKey] = '; redundant instruction removed!';
                    $instructionArray[$key] = 'lea.l '.$adjustedValue.'(a1),a1 ; calc destination address into a1 ADJUSTED';
                    $lastDestinationResetKey = null;
                }
            } elseif (str_contains($instruction, 'calc source address')) {
                if ($lastSourceResetKey) {
                    $instructionStartingAtNumber = substr($instruction, 6);
                    $calcValue = intval(substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '(')));
                    $adjustedValue = $calcValue + $lastSourceResetValue;

                    $instructionArray[$lastSourceResetKey] = '; redundant instruction removed!';
                    $instructionArray[$key] = 'lea.l '.$adjustedValue.'(a0),a0 ; calc source address into a0 ADJUSTED';
                    $lastSourceResetKey = null;
                }
            } elseif (str_starts_with($instruction, '.loop')) {
                $lastSourceResetKey = null;
                $lastDestinationResetKey = null;
            }
        }*/

        // remove redundant lea instructions
        // should probably do this elsewhere
        // might relate to breakage of the lampposts!

        foreach ($instructionArray as $key => $instruction) {
            if (str_starts_with($instruction, 'lea 0(a0),a0')) {
                $instructionArray[$key] = '; redundant a0 lea instruction removed';
            } elseif (str_starts_with($instruction, 'lea 0(a1),a1')) {
                $instructionArray[$key] = '; redundant a1 lea instruction removed';
            }
        }

        // are any of the precomputed blitter control registers unused?
        // if so, use them for something else!

        $blitterControlRegisters = ['d1', 'd2', 'd3', 'd4'];
        $freeBlitterControlRegisters = ['d1' => true, 'd2' => true, 'd3' => true, 'd4' => true, 'd5' => true, 'd7' => true/*, 'd6' => true*/];
        foreach ($instructionArray as $instruction) {
            foreach ($blitterControlRegisters as $registerName) {
                if (str_starts_with($instruction, 'move.w '.$registerName)) {
                    unset($freeBlitterControlRegisters[$registerName]);
                }
            }
            if (str_starts_with($instruction, 'dbra d5')) {
                unset($freeBlitterControlRegisters['d5']);
            }
            /*if (str_starts_with($instruction, 'dbra d6')) {
                unset($freeBlitterControlRegisters['d6']);
            }
            if (str_starts_with($instruction, 'moveq') && str_contains($instruction, 'd6')) {
                unset($freeBlitterControlRegisters['d6']);
            }*/
        }

        $commonOffsets = [];
        if (count($freeBlitterControlRegisters)) {
            foreach ($instructionArray as $instruction) {
                if (str_contains($instruction, 'calc source address') || str_contains($instruction, 'calc destination address')) {
                    $instructionStartingAtNumber = substr($instruction, 4);
                    $offsetValue = intval(substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '(')));
                    if (!isset($commonOffsets[$offsetValue])) {
                        //echo("offset value ".$offsetValue." found in instruction ".$instruction."\n");
                        $commonOffsets[$offsetValue] = 0;
                    }
                    $commonOffsets[$offsetValue]++;
                } elseif (str_starts_with($instruction, 'move.w #')) {
                    $instructionStartingAtNumber = substr($instruction, 8);
                    $commaPosition = strpos($instructionStartingAtNumber, ',');
                    $offsetValue = intval(substr($instructionStartingAtNumber, 0, $commaPosition));
                    if (!isset($commonOffsets[$offsetValue])) {
                        //echo("offset value ".$offsetValue." found in instruction ".$instruction."\n");
                        $commonOffsets[$offsetValue] = 0;
                    }
                    $commonOffsets[$offsetValue]++;
                }
            }
        }

        $filteredCommonOffsets = [];
        foreach ($commonOffsets as $offset => $occurrences) {
            if ($occurrences > 1) {
                $filteredCommonOffsets[$offset] = $occurrences;
            }
        }

        arsort($filteredCommonOffsets);
        $reindexedCommonOffsets = [];
        foreach ($filteredCommonOffsets as $offset => $occurrences) {
            $reindexedCommonOffsets[] = $offset;
        }

        // if I have more free registers than values I need to store, remove registers from the free list
        while (count($freeBlitterControlRegisters) > count($reindexedCommonOffsets)) {
            array_pop($freeBlitterControlRegisters);
        }

        $offsetRegisterMappings = [];
        $commonOffsetIndex = 0;
        foreach ($freeBlitterControlRegisters as $registerName => $value) {
            $offsetRegisterMappings[$reindexedCommonOffsets[$commonOffsetIndex]] = $registerName;
            unset($reindexedCommonOffsets[$commonOffsetIndex]);
            $commonOffsetIndex++;
        }

        foreach ($instructionArray as $key => $instruction) {
            if (str_contains($instruction, 'calc source address') || str_contains($instruction, 'calc destination address')) {
                $instructionStartingAtNumber = substr($instruction, 4);
                $offsetValueStr = substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '('));
                $offsetValue = intval($offsetValueStr);

                if (!is_numeric($offsetValueStr)) {
                    echo("FAIL: ".$instruction."\n");
                    exit(1);
                }

                if (isset($offsetRegisterMappings[$offsetValue])) {
                    if (str_contains($instruction, 'calc destination address')) {
                        $instructionArray[$key] = 'add.w '.$offsetRegisterMappings[$offsetValue].',a1 ; destination address into a1 REGISTER';
                    } else {
                        $instructionArray[$key] = 'add.w '.$offsetRegisterMappings[$offsetValue].',a0 ; source address into a0 REGISTER';
                    }
                }
            } elseif (str_starts_with($instruction, 'move.w #')) {
                $instructionStartingAtNumber = substr($instruction, 8);
                $commaPosition = strpos($instructionStartingAtNumber, ',');
                $offsetValue = intval(substr($instructionStartingAtNumber, 0, $commaPosition));
                if (isset($offsetRegisterMappings[$offsetValue])) {
                    $commaPosition = strpos($instruction, ',');
                    $newInstruction = 'move.w ' . $offsetRegisterMappings[$offsetValue] . substr($instruction, $commaPosition) . ' REGISTER (3)';
                    $instructionArray[$key] = $newInstruction;
                }
            }
        }

        // are there any remaining common offsets that I wasn't able to assign to registers in the initial pass?
        // can i assign any of the remaining values to registers?

        foreach ($reindexedCommonOffsets as $commonOffset) {
            foreach ($freeBlitterControlRegisters as $registerName => $value) {
                $firstRegisterUsageKey = null;
                $lastRegisterUsageKey = null;
                foreach ($instructionArray as $key => $instruction) {
                    if (str_starts_with($instruction, 'move.w ' . $registerName) || str_starts_with($instruction, 'add.w ' . $registerName)) {
                        if (is_null($firstRegisterUsageKey)) {
                            $firstRegisterUsageKey = $key;
                        } else {
                            $lastRegisterUsageKey = $key;
                        }
                    }
                }

                if (is_null($firstRegisterUsageKey)) {
                    echo("failed to find existing usage of ".$registerName." - abort!\n");
                    exit();
                }

                //$firstCommonOffsetUsageKey = null;
                //$lastCommonOffsetUsageKey = null;
                $overlap = false;
                foreach ($instructionArray as $key => $instruction) {
                    $offsetValue = null;
                    if (str_contains($instruction, 'calc source address') || str_contains($instruction, 'calc destination address')) {
                        $instructionStartingAtNumber = substr($instruction, 4);
                        $offsetValueStr = substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '('));
                        $offsetValue = intval($offsetValueStr);
                    } elseif (str_starts_with($instruction, 'move.w #')) { 
                        $instructionStartingAtNumber = substr($instruction, 8);
                        $commaPosition = strpos($instructionStartingAtNumber, ',');
                        $offsetValue = intval(substr($instructionStartingAtNumber, 0, $commaPosition));
                    }

                    if (!is_null($offsetValue) && $key >= $firstRegisterUsageKey && $key <= $lastRegisterUsageKey) {
                        $overlap = true;
                        /*if (is_null($firstCommonOffsetUsageKey)) {
                            $firstCommonOffsetUsageKey = $key;
                        } else {
                            $lastCommonOffsetUsageKey = $key;
                        }*/
                    }
                }

                if (!$overlap) {
                    // we can assign this value to this register starting after $lastRegisterUsageKey
                    // i.e.
                    // - we insert a 'move.w #commonOffset,$registerName' after $lastRegisterUsageKey
                    // - and then all lea/move.w instructions after that get updated
                    //echo("WIN1\n");
                    //var_dump($firstCommonOffsetUsageKey);
                    //var_dump($lastRegisterUsageKey);
                    //echo('last usage of register '.$registerName.' is at '.$lastRegisterUsageKey.', first usage of common offset '.$commonOffset.' is at '.$firstCommonOffsetUsageKey."\n");
                    //echo("-----------------------\n");
                    /*foreach ($instructionArray as $key => $instruction) {
                        if ($key > $lastRegisterUsageKey) {
                            if (str_contains($instruction, 'calc source address') || str_contains($instruction, 'calc destination address')) {
                                $instructionStartingAtNumber = substr($instruction, 4);
                                $offsetValueStr = substr($instructionStartingAtNumber, 0, strpos($instructionStartingAtNumber, '('));
                                $offsetValue = intval($offsetValueStr);

                                if ($offsetValue == $commonOffset) {
                                    if (str_contains($instruction, 'calc destination address')) {
                                        $instructionArray[$key] = 'add.w '.$registerName.',a1 ; destination address into a1 REGISTER';
                                    } else {
                                        $instructionArray[$key] = 'add.w '.$registerName.',a0 ; source address into a0 REGISTER';
                                    }
                                }
                            } elseif (str_starts_with($instruction, 'move.w #')) {
                                $instructionStartingAtNumber = substr($instruction, 8);
                                $commaPosition = strpos($instructionStartingAtNumber, ',');
                                $offsetValue = intval(substr($instructionStartingAtNumber, 0, $commaPosition));
                                if ($offsetValue == $commonOffset) {
                                    $commaPosition = strpos($instruction, ',');
                                    $newInstruction = 'move.w ' . $registerName . substr($instruction, $commaPosition) . ' REGISTER (4)';
                                    $instructionArray[$key] = $newInstruction;
                                }
                            } elseif (str_starts_with($instruction, 'add.w #')) {
                                $instructionStartingAtNumber = substr($instruction, 7);
                                $commaPosition = strpos($instructionStartingAtNumber, ',');
                                $offsetValue = intval(substr($instructionStartingAtNumber, 0, $commaPosition));
                                if ($offsetValue == $commonOffset) {
                                    $commaPosition = strpos($instruction, ',');
                                    $newInstruction = 'add.w ' . $registerName . substr($instruction, $commaPosition) . ' REGISTER (4)';
                                    $instructionArray[$key] = $newInstruction;
                                }
                            }
                        }
                    }

                    foreach ($instructionArray as $instruction) {
                        echo($instruction."\n");
                    }*/

                    //echo("WIN1\n");
                    //exit();
                }
            }
        }

        // end

        foreach ($offsetRegisterMappings as $offset => $registerName) {
            if ($offset >= -128 && $offset <= 127) { 
                $instruction = 'moveq.l #'.$offset.','.$registerName;
            } else {
                $instruction = 'move.w #'.$offset.','.$registerName;
            }
            array_unshift($instructionArray, $instruction);
        }


        // something to do with endmasks

        $endmaskCounts = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];

        foreach ($instructionArray as $key => $instruction) {
            for ($index = 1; $index <= 3; $index++) {
                if (str_contains($instruction, 'set endmask' . $index)) {
                    $endmaskCounts[$index]++;
                }
            }
        }

        $highestUsageValue = 0;
        $highestUsageEndmask = 0;
        for ($index = 1; $index <= 3; $index++) {
            if ($endmaskCounts[$index] > $highestUsageValue) {
                $highestUsageValue = $endmaskCounts[$index];
                $highestUsageEndmask = $index;
            }
        }

        if ($highestUsageValue > 1) {
            foreach ($instructionArray as $key => $instruction) {
                if (str_contains($instruction, 'set endmask' . $highestUsageEndmask)) {
                    $commaPosition = strpos($instruction, ',');
                    $instructionArray[$key] = substr($instruction, 0, $commaPosition) . ',(a2) ; set endmask'.$highestUsageEndmask.' REGISTER';
                } elseif (str_contains($instruction, 'cache endmask')) {
                    $endmaskToRegMappings = [
                        1 => 'ffff8a28',
                        2 => 'ffff8a2a',
                        3 => 'ffff8a2c',
                    ];

                    $instructionArray[$key] = 'lea $'.$endmaskToRegMappings[$highestUsageEndmask].'.w,a2        ; post process cache endmask' . $highestUsageEndmask;
                }
            }
        } else {
            foreach ($instructionArray as $key => $instruction) {
                if (str_contains($instruction, 'cache endmask')) {
                    $instructionArray[$key] = '; cache endmask instruction removed';
                }
            }
        }

        // eliminate any redundant lea instructions at end of stream... very hacky!

        /*$instructionArrayReversed = array_reverse($instructionArray);

        while (str_starts_with($instructionArrayReversed[0], 'lea')) {
            array_shift($instructionArrayReversed);
        }

        $instructionArray = array_reverse($instructionArrayReversed);*/

        $instructionArray[] = 'rts';

        return $instructionArray;

        //return $instructionStream->getArray();
    }

    private function addConfirmCopyInstructions(
        array $loopState,
        InstructionStream $instructionStream,
        int $loopIndex,
        ?int $sourceYIncrement,
        ?int $destinationYIncrement
    ): int {
        $copyInstructionIterations = $loopState['copyInstructionIterations'];
        $useFxsr = $loopState['useFxsr'];
        $useNfsr = $loopState['useNfsr'];
        $sourceAdvance = $loopState['subsequentSourceAdvance'];
        $destinationAdvance = $loopState['subsequentDestinationAdvance'];
        $changedEndmasks = $loopState['changedEndmasks'];

        $instructionStream->add('');

        $endmaskInstructionStream = $this->generateEndmaskInstructionStream($changedEndmasks);
        $instructionStream->appendStream($endmaskInstructionStream);

        if (!is_null($sourceYIncrement)) { 
            $instructionStream->add(
                $this->generateSourceYIncrementInstruction($sourceYIncrement)
            );
        }

        if (!is_null($destinationYIncrement)) { 
            $instructionStream->add(
                $this->generateDestinationYIncrementInstruction($destinationYIncrement)
            );
        }

        if ($copyInstructionIterations > self::BLITTER_COPY_THRESHOLD) {

            $instructionStream->add('');
            $instructionStream->add('; 4 bitplane multiple line copy START');

            $instructionStream->add('moveq.l #'. $copyInstructionIterations . ',d6');

            /*$copyInstructionStream = $this->generateCopyInstructionStream(
                null,
                null,
                'd6',
                $useFxsr,
                $useNfsr
            );
            $instructionStream->appendStream($copyInstructionStream);*/

            $copyInstructionStream = $this->generateCopyInstructionStream(
                2,
                2,
                'd6',
                $useFxsr,
                $useNfsr
            );

            $copyInstructionStreamAfterLoop = $this->generateCopyInstructionStream(
                null,
                null,
                'd6',
                $useFxsr,
                $useNfsr
            );


            $this->drawOffsetSourceAdjust = 6;
            $this->drawOffsetDestinationAdjust = 6;

            $this->addLoopInstructions($instructionStream, $copyInstructionStream, 3, $loopIndex, 'd5');
            $instructionStream->appendStream($copyInstructionStreamAfterLoop);
            $instructionStream->add('; 4 bitplane multiple line copy END');
            $loopIndex++;

        } else {
            if ($copyInstructionIterations > 1) {
                $copyInstructionStream = $this->generateCopyInstructionStream(
                    $sourceAdvance,
                    $destinationAdvance,
                    'd0',
                    $useFxsr,
                    $useNfsr 
                );      

                $this->drawOffsetSourceAdjust = $copyInstructionIterations * $sourceAdvance;
                $this->drawOffsetDestinationAdjust = $copyInstructionIterations * $destinationAdvance;

                $instructionStream->add('');
                $instructionStream->add('; looped span copy START');
                $this->addLoopInstructions($instructionStream, $copyInstructionStream, $copyInstructionIterations, $loopIndex, 'd6');
                $instructionStream->add('; looped span copy END');
                $loopIndex++;

                // alternative implementation that doesn't have excess instructions but needs more memory
                /*$copyInstructionStream = $this->generateCopyInstructionStream(
                    $sourceAdvance,
                    $destinationAdvance,
                    'd0',
                    $useFxsr,
                    $useNfsr
                );
                $copyInstructionStreamAfterLoop = $this->generateCopyInstructionStream(
                    null,
                    null,
                    'd0',
                    $useFxsr,
                    $useNfsr
                );

                $this->drawOffsetSourceAdjust = ($copyInstructionIterations - 1) * $sourceAdvance;
                $this->drawOffsetDestinationAdjust = ($copyInstructionIterations - 1) * $destinationAdvance;

                if ($copyInstructionIterations == 2) {
                    $instructionStream->add('');
                    $instructionStream->add('; 2 iteration span copy START');
                    $instructionStream->appendStream($copyInstructionStream);
                    $instructionStream->appendStream($copyInstructionStreamAfterLoop);
                    $instructionStream->add('; 2 iteration span copy END');
                } else {
                    $instructionStream->add('');
                    $instructionStream->add('; looped span copy START');
                    $this->addLoopInstructions($instructionStream, $copyInstructionStream, $copyInstructionIterations - 1, $loopIndex, 'd6');
                    $instructionStream->appendStream($copyInstructionStreamAfterLoop);
                    $instructionStream->add('; looped span copy END');
                    $loopIndex++;
                }*/

            } else {
                $copyInstructionStream = $this->generateCopyInstructionStream(
                    null,
                    null,
                    'd0',
                    $useFxsr,
                    $useNfsr 
                );      

                $this->drawOffsetSourceAdjust = 0;
                $this->drawOffsetDestinationAdjust = 0;

                $instructionStream->add('');
                $instructionStream->add('; single span copy START');
                $instructionStream->appendStream($copyInstructionStream);
                $instructionStream->add('; single span copy END');
            }
        }

        return $loopIndex;
    }

    private function addLoopInstructions(
        InstructionStream $instructionStream,
        InstructionStream $copyInstructionStream,
        $copyInstructionIterations,
        $loopIndex,
        $loopRegister
    ) {
        $instructionStream->add('');
        $instructionStream->add('moveq.l #'.($copyInstructionIterations - 1).','.$loopRegister);
        $instructionStream->add('.loop'.$loopIndex.':');

        $instructionStream->appendStream($copyInstructionStream);

        $instructionStream->add('dbra '.$loopRegister.',.loop'.$loopIndex);
    }

    private function generateCopyInstructionStream(?int $sourceAdvance, ?int $destinationAdvance, string $ycountSource, bool $useFxsr, bool $useNfsr): InstructionStream
    {
        $copyInstructionStream = new InstructionStream();

        $copyInstructionStream->add('move.w a0,(a3) ; set source address');
        $copyInstructionStream->add('move.w a1,(a4) ; set destination address');
        $copyInstructionStream->add(
            $this->generateYCountInstruction($ycountSource)
        );

        $copyInstructionStream->add(
            $this->generateBlitterControlInstruction($useFxsr, $useNfsr)
        );

        if ($sourceAdvance !== 0) {
            $copyInstructionStream->add(
                sprintf(
                    'lea %d(a0),a0 ; calc source address into a0',
                    $sourceAdvance
                )
            );
        }

        if ($destinationAdvance) {
            $copyInstructionStream->add(
                sprintf(
                    'lea %d(a1),a1 ; calc destination address into a1',
                    $destinationAdvance
                )
            );
        }

        return $copyInstructionStream;
    }

    private function generateYCountInstruction(string $ycountSource): string
    {
        return 'move.w '. $ycountSource .',(a5) ; set ycount';
    }

    private function generateEndmaskInstructionStream(array $changedEndmasks): InstructionStream
    {
        $endmaskInstructionStream = new InstructionStream();

        if (isset($changedEndmasks[1]) && isset($changedEndmasks[2])) {
            $combinedEndmask = $changedEndmasks[1] << 16 | $changedEndmasks[2];
            $endmaskInstructionStream->add(
                $this->generateSetEndmaskInstruction($combinedEndmask, 1, 'l')
            );

            if (isset($changedEndmasks[3])) {
                $endmaskInstructionStream->add(
                    $this->generateSetEndmaskInstruction($changedEndmasks[3], 3, 'w')
                );
            }
        } else if (isset($changedEndmasks[2]) && isset($changedEndmasks[3])) {
            if (isset($changedEndmasks[1])) {
                $endmaskInstructionStream->add(
                    $this->generateSetEndmaskInstruction($changedEndmasks[1], 1, 'w')
                );
            }

            $combinedEndmask = $changedEndmasks[2] << 16 | $changedEndmasks[3];
            $endmaskInstructionStream->add(
                $this->generateSetEndmaskInstruction($combinedEndmask, 2, 'l')
            );
        } else {

            if (isset($changedEndmasks[1])) {
                $endmaskInstructionStream->add(
                    $this->generateSetEndmaskInstruction($changedEndmasks[1], 1, 'w')
                );
            }

            if (isset($changedEndmasks[2])) {
                $endmaskInstructionStream->add(
                    $this->generateSetEndmaskInstruction($changedEndmasks[2], 2, 'w')
                );
            }

            if (isset($changedEndmasks[3])) {
                $endmaskInstructionStream->add(
                    $this->generateSetEndmaskInstruction($changedEndmasks[3], 3, 'w')
                );
            }
        }

        // TODO: reinstate long write for multiple changed endmasks

        return $endmaskInstructionStream;
    }

    private function generateBlitterControlInstruction(bool $useFxsr, bool $useNfsr): string
    {
        return sprintf(
            'move.w %s,(a6) ; set blitter control, fxsr = %s, nfsr = %s',
            $this->getBlitterControlSourceRegister($useFxsr, $useNfsr),
            $useFxsr ? 'true' : 'false',
            $useNfsr ? 'true' : 'false',
        );

        // alternative implementation - unfortunately results in higher file size
        if ($useNfsr) {
            if ($useFxsr) {
                $controlValue = 0xc0c0 | $this->skewed;
                return 'move.w #'.$controlValue.',(a6) ; set blitter control, fxsr = true, nfsr = true';
            } else {
                $controlValue = 0xc040 | $this->skewed; 
                return 'move.w #'.$controlValue.',(a6) ; set blitter control, fxsr = false, nfsr = true';
            }
        } else {
            if ($useFxsr) {
                $controlValue = 0xc080 | $this->skewed;
                return 'move.w #'.$controlValue.',(a6) ; set blitter control, fxsr = true, nfsr = false';
            } else {
                $controlValue = 0xc000 | $this->skewed;
                return 'move.w #'.$controlValue.',(a6) ; set blitter control, fxsr = false, nfsr = false';
            }
        }
    }

    private function getBlitterControlSourceRegister(bool $useFxsr, bool $useNfsr): string
    {
        if ($useNfsr) {
            if ($useFxsr) {
                return 'd4';
            } else {
                return 'd3';
            }
        } else {
            if ($useFxsr) {
                return 'd2';
            } else {
                return 'd1';
            }
        }
    }

    private function generateSetEndmaskInstruction(int $endmask, int $endmaskIndex, string $size): string
    {
        $destinations = [
            '$ffff8a28.w',
            '$ffff8a2a.w',
            '$ffff8a2c.w',
        ];

        if ($endmaskIndex < 1 || $endmaskIndex > 3) {
            throw new RuntimeException('Invalid endmask index ');
        }

        /*$source = 'd7';
        if ($endmask != 0xffff && $endmask != 0xffffffff) {*/
            $source = sprintf(
                '#%d',
                $endmask
            );
        //}

        $destination = $destinations[$endmaskIndex - 1];

        return sprintf(
            'move.%s %s,%s ; set endmask%d',
            $size,
            $source,
            $destination,
            $endmaskIndex
        );
    }

    private function calculateSourceYIncrement(array $loopState, int $length): int
    {
        if ($loopState['copyInstructionIterations'] > self::BLITTER_COPY_THRESHOLD) {
            $sourceYIncrement = ($loopState['subsequentSourceAdvance'] + 10) - ($length * 10);

            if ($loopState['useFxsr']) {
                $sourceYIncrement -= 10;
            }

            if ($loopState['useNfsr']) {
                $sourceYIncrement += 10;
            }

        } else {
            $sourceYIncrement = -((10 * ($length - 1)) - 2); // source y increment = (source x increment * (x count - 1)) -2
            if ($loopState['useFxsr']) {
                $sourceYIncrement -= 10;
            }

            if ($loopState['useNfsr']) {
                $sourceYIncrement += 10;
            }

        }

        return $sourceYIncrement;
    }

    private function calculateDestinationYIncrement(array $loopState, int $length): int
    {
        // TODO: needs to act differently when number of lines > 6
        if ($loopState['copyInstructionIterations'] > self::BLITTER_COPY_THRESHOLD) {
            return 168 - ($length * 8);
        } else {
            return -((8 * ($length - 1)) - 2);
        }
    }

    private function generateSourceYIncrementInstruction(int $sourceYIncrement): string
    {
        return sprintf(
            'move.w #%d,$ffff8a22.w ; source y increment (per fxsr eligibility)',
            $sourceYIncrement
        );
    }

    private function generateDestinationYIncrementInstruction(int $destinationYIncrement): string
    {
        return sprintf(
            'move.w #%d,$ffff8a30.w ; dest y increment (per length group)',
            $destinationYIncrement
        );
    }
}


