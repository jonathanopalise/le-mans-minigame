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
        if ($this->skewed & 1) {
            return ['rts'];
        }

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

                    $spans = $nfsrBasedSpanCollection->getSpans();

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
                            'copyInstructionIterations' => 1,
                            'useFxsr' => $useFxsr,
                            'useNfsr' => $useNfsr,
                            'changedEndmasks' => $changedEndmasks,
                        ];

                        if ($key == array_key_first($spans)) {
                            $loopState = $state;
                        } else {
                            if ($sourceAdvance != $oldSourceAdvance || $destinationAdvance != $oldDestinationAdvance || count($changedEndmasks)) {
                                // NOTE: destination y increment will change depending upon whether it's a dbra loop or blitter loop 

                                $sourceYIncrement = $this->calculateSourceYIncrement($loopState, $length);
                                $destinationYIncrement = $this->calculateDestinationYIncrement($loopState, $length);
                                $loopIndex = $this->addConfirmCopyInstructions(
                                    $loopState,
                                    $instructionStream,
                                    $loopIndex,
                                    $sourceYIncrement != $oldSourceYIncrement ? $sourceYIncrement : null,
                                    $destinationYIncrement != $oldDestinationYIncrement ? $destinationYIncrement: null
                                );
                                $oldSourceYIncrement = $sourceYIncrement;
                                $oldDestinationYIncrement = $destinationYIncrement;

                                $loopState = $state;
                            } else {
                                $loopState['copyInstructionIterations']++;
                            }
                        }

                        if ($key == array_key_last($spans)) {
                            $sourceYIncrement = $this->calculateSourceYIncrement($loopState, $length);
                            $destinationYIncrement = $this->calculateDestinationYIncrement($loopState, $length);
                            $loopIndex = $this->addConfirmCopyInstructions(
                                $loopState,
                                $instructionStream,
                                $loopIndex,
                                $sourceYIncrement != $oldSourceYIncrement ? $sourceYIncrement: null,
                                $destinationYIncrement != $oldDestinationYIncrement ? $destinationYIncrement: null
                            );
                            $oldDestinationYIncrement = $destinationYIncrement;
                        }
                    }

                }
            }
        }

        $instructionStream->add('rts');

        if (count($instructionStream->getArray()) == 2) {
            echo("FAIL2");
            exit(1);
        }

        return $instructionStream->getArray();
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
        $sourceAdvance = $loopState['loopStartSourceAdvance'];
        $destinationAdvance = $loopState['loopStartDestinationAdvance'];
        $changedEndmasks = $loopState['changedEndmasks'];

        $instructionStream->add('');

        $endmaskInstructionStream = $this->generateEndmaskInstructionStream($changedEndmasks);
        $instructionStream->appendStream($endmaskInstructionStream);

        if ($sourceYIncrement) { 
            $instructionStream->add(
                $this->generateSourceYIncrementInstruction($sourceYIncrement)
            );
        }

        if ($destinationYIncrement) { 
            $instructionStream->add(
                $this->generateDestinationYIncrementInstruction($destinationYIncrement)
            );
        }

        if ($copyInstructionIterations > self::BLITTER_COPY_THRESHOLD) {

            //$instructionStream->add('move.l #'.rand().',$2DC6C0');

            $instructionStream->add('moveq.l #'. $copyInstructionIterations . ',d6');

            $copyInstructionStream = $this->generateCopyInstructionStream(
                $sourceAdvance,
                $destinationAdvance,
                'd6',
                $useFxsr,
                $useNfsr
            );
            $instructionStream->appendStream($copyInstructionStream);

            $instructionStream->add('rept 3');
            $copyInstructionStream = $this->generateCopyInstructionStream(
                2,
                2,
                'd6',
                $useFxsr,
                $useNfsr
            );
            $instructionStream->appendStream($copyInstructionStream);
            $instructionStream->add('endr');

            $resetDestinationAdvance = (($copyInstructionIterations - 1) * $destinationAdvance) - 6;
            $instructionStream->add(
                sprintf(
                    'lea.l %d(a1),a1 ; calc destination address into a1',
                    $resetDestinationAdvance
                )
            );

            $resetSourceAdvance = (($copyInstructionIterations - 1) * $sourceAdvance) - 6;
            $instructionStream->add(
                sprintf(
                    'lea.l %d(a0),a0 ; calc source address into a0',
                    $resetSourceAdvance
                )
            );

        } else {
            $copyInstructionStream = $this->generateCopyInstructionStream(
                $sourceAdvance,
                $destinationAdvance,
                'd0',
                $useFxsr,
                $useNfsr 
            );          

            if ($copyInstructionIterations > 1) {
                $this->addLoopInstructions($instructionStream, $copyInstructionStream, $copyInstructionIterations, $loopIndex);
                $loopIndex++;
            } else {
                $instructionStream->appendStream($copyInstructionStream);
            }
        }

        return $loopIndex;
    }

    private function addLoopInstructions(
        InstructionStream $instructionStream,
        InstructionStream $copyInstructionStream,
        $copyInstructionIterations,
        $loopIndex
    ) {
        $instructionStream->add('');
        $instructionStream->add('moveq.l #'.($copyInstructionIterations - 1).',d6');
        $instructionStream->add('.loop'.$loopIndex.':');

        $instructionStream->appendStream($copyInstructionStream);

        $instructionStream->add('dbra d6,.loop'.$loopIndex);
    }

    private function generateCopyInstructionStream(int $sourceAdvance, int $destinationAdvance, string $ycountSource, bool $useFxsr, bool $useNfsr, $repeatControl = null): InstructionStream
    {
        $copyInstructionStream = new InstructionStream();
        $copyInstructionStream->add(
            sprintf(
                'lea.l %d(a0),a0 ; calc source address into a0',
                $sourceAdvance
            )
        );
        $copyInstructionStream->add('move.w a0,(a3) ; set source address');

        $copyInstructionStream->add(
            sprintf(
                'lea.l %d(a1),a1 ; calc destination address into a1',
                $destinationAdvance
            )
        );
        $copyInstructionStream->add('move.w a1,(a4) ; set destination address');

        if ($repeatControl) {
            $copyInstructionStream->add('rept '.$repeatControl);
        }

        $copyInstructionStream->add(
            $this->generateYCountInstruction($ycountSource)
        );

        $copyInstructionStream->add(
            $this->generateBlitterControlInstruction($useFxsr, $useNfsr)
        );

        if ($repeatControl) {
            $copyInstructionStream->add('endr');
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

        if (isset($changedEndmasks[1])) {
            $endmaskInstructionStream->add(
                $this->generateSetEndmaskInstruction($changedEndmasks[1], 1)
            );
        }

        if (isset($changedEndmasks[2])) {
            $endmaskInstructionStream->add(
                $this->generateSetEndmaskInstruction($changedEndmasks[2], 2)
            );
        }

        if (isset($changedEndmasks[3])) {
            $endmaskInstructionStream->add(
                $this->generateSetEndmaskInstruction($changedEndmasks[3], 3)
            );
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

        if ($useNfsr) {
            if ($useFxsr) {
                $copyInstructions[] = 'move.w d4,(a6) ; set blitter control, fxsr = true, nfsr = true';
            } else {
                $copyInstructions[] = 'move.w d3,(a6) ; set blitter control, fxsr = false, nfsr = true';
            }
        } else {
            if ($useFxsr) {
                $copyInstructions[] = 'move.w d2,(a6) ; set blitter control, fxsr = true, nfsr = false';
            } else {
                $copyInstructions[] = 'move.w d1,(a6) ; set blitter control, fxsr = false, nfsr = false';
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

    private function generateSetEndmaskInstruction(int $endmask, int $endmaskIndex): string
    {
        $destinations = [
            '(a2)',
            '$ffff8a2a.w',
            '$ffff8a2c.w',
        ];

        if ($endmaskIndex < 1 || $endmaskIndex > 3) {
            throw new RuntimeException('Invalid endmask index ');
        }

        $source = 'd7';
        if ($endmask != 0xffff) {
            $source = sprintf(
                '#$%x',
                $endmask
            );
        }

        $destination = $destinations[$endmaskIndex - 1];

        return sprintf(
            'move.w %s,%s ; set endmask%d',
            $source,
            $destination,
            $endmaskIndex
        );
    }

    private function calculateSourceYIncrement(array $loopState, int $length): int
    {
        if ($loopState['copyInstructionIterations'] > self::BLITTER_COPY_THRESHOLD) {
            $width = $this->widthInSixteenPixelBlocks;
            if ($this->skewed) {
                $width--;
            }

            $sourceYIncrement = ($width * 10) - ($length * 10);
            $sourceYIncrement += 10;

            //$sourceYIncrement = -((10 * ($length - 1)) - 2); // source y increment = (source x increment * (x count - 1)) -2
        } else {
            $sourceYIncrement = -((10 * ($length - 1)) - 2); // source y increment = (source x increment * (x count - 1)) -2
        }

        if ($loopState['useFxsr']) {
            $sourceYIncrement -= 10;
        }
        if ($loopState['useNfsr']) {
            $sourceYIncrement += 10;
        }

        return $sourceYIncrement;
    }

    private function calculateDestinationYIncrement(array $loopState, int $length): int
    {
        // TODO: needs to act differently when number of lines > 6
        if ($loopState['copyInstructionIterations'] > self::BLITTER_COPY_THRESHOLD) {
            //$test= -((8 * ($length - 1)) - 2);
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


