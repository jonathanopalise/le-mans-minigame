<?php

class Register
{
    private $name;
    private $currentValue = null;
    private $lastReadOffset = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function clearCurrentValue()
    {
        $this->currentValue = null;
    }

    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    public function setCurrentValue(int $value): void
    {
        $this->currentValue = $value;
    }

    public function setLastReadOffset(int $lastReadOffset): void
    {
        $this->lastReadOffset = $lastReadOffset;
    }

    public function getLastReadOffset()
    {
        return $this->lastReadOffset;
    }

    public function clearLastReadOffset(): void
    {
        $this->lastReadOffset = null;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class ValueApplicationDetails
{
    const APPLICATION_METHOD_IMMEDIATE = 'APPLICATION_METHOD_IMMEDIATE';
    const APPLICATION_METHOD_REGISTER = 'APPLICATION_METHOD_REGISTER';

    const APPLICATION_METHODS = [
        self::APPLICATION_METHOD_IMMEDIATE,
        self::APPLICATION_METHOD_REGISTER,
    ];

    private $applied = false;
    private $method;
    private $sourceRegisterName;
    private $value;
    private $instructions = [];
    private $block;

    public function __construct(SixteenPixelBlock $block)
    {
        $this->block = $block;
    }

    public function markAsApplied(string $method, ?string $sourceRegisterName, int $value)
    {
        if (!in_array($method, self::APPLICATION_METHODS)) {
            throw new \Exception('Invalid application method');
        }

        if ($method == self::APPLICATION_METHOD_IMMEDIATE && $sourceRegisterName !== null) {
            throw new \Exception('Source register should not be specified for immediate applications');
        }

        if ($method == self::APPLICATION_METHOD_REGISTER && $sourceRegisterName === null) {
            throw new \Exception('Source register must be specified for register applications');
        }

        if ($method == self::APPLICATION_METHOD_REGISTER && !in_array($sourceRegisterName, RegisterCollection::KNOWN_REGISTERS)) {
            var_dump(RegisterCollection::KNOWN_REGISTERS);
            throw new \Exception('Unknown source register name ' . $sourceRegisterName);
        }

        $this->applied = true;
        $this->method = $method;
        $this->sourceRegisterName = $sourceRegisterName;
        $this->value = $value;
    }

    public function appliedValueUsingRegister(int $value): bool
    {
        return $this->applied && $this->method == self::APPLICATION_METHOD_REGISTER && $this->value == $value;
    }

    public function getSourceRegisterName()
    {
        return $this->sourceRegisterName;
    }

    public function addInstruction(string $instruction)
    {
        printf("    %s\n", $instruction);
        $this->instructions[] = $instruction;
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function getBlock()
    {
        return $this->block;
    }
}

class SixteenPixelBlock {
    private ValueApplicationDetails $maskApplicationDetails;
    private ValueApplicationDetails $bitplanesZeroAndOneApplicationDetails;
    private ValueApplicationDetails $bitplanesTwoAndThreeApplicationDetails;

    private int $sourceOffset;
    private int $destinationOffset;

    public function __construct(int $sourceOffset, int $destinationOffset)
    {
        $this->sourceOffset = $sourceOffset;
        $this->destinationOffset = $destinationOffset;
        $this->maskApplicationDetails = new ValueApplicationDetails($this);
        $this->bitplanesZeroAndOneApplicationDetails = new ValueApplicationDetails($this);
        $this->bitplanesTwoAndThreeApplicationDetails = new ValueApplicationDetails($this);
    }

    public function appliedValueUsingRegister($value): bool
    {
        return $this->maskApplicationDetails->appliedValueUsingRegister($value) ||
            $this->bitplanesZeroAndOneApplicationDetails->appliedValueUsingRegister($value) ||
            $this->bitplanesTwoAndThreeApplicationDetails->appliedValueUsingRegister($value);
    }

    public function getValueApplicationDetailsThatAppliedValueUsingRegister($value): ValueApplicationDetails
    {
        if ($this->maskApplicationDetails->appliedValueUsingRegister($value)) {
            return $this->maskApplicationDetails;
        }

        if ($this->bitplanesZeroAndOneApplicationDetails->appliedValueUsingRegister($value)) {
            return $this->bitplanesZeroAndOneApplicationDetails;
        }

        if ($this->bitplanesTwoAndThreeApplicationDetails->appliedValueUsingRegister($value)) {
            return $this->bitplanesTwoAndThreeApplicationDetails;
        }

        throw new \Exception('FAIL'); 
    }

    public function getDestinationOffset(): int
    {
        return $this->destinationOffset;
    }

    public function getSourceOffset(): int
    {
        return $this->sourceOffset;
    }

    public function markMaskAsApplied(string $method, ?string $sourceRegisterName, int $value): void
    {
        $this->maskApplicationDetails->markAsApplied($method, $sourceRegisterName, $value);
    }

    public function markBitplanesZeroAndOneAsApplied(string $method, ?string $sourceRegisterName, int $value): void
    {
        $this->bitplanesZeroAndOneApplicationDetails->markAsApplied($method, $sourceRegisterName, $value);
    }

    public function markBitplanesTwoAndThreeAsApplied(string $method, ?string $sourceRegisterName, int $value): void
    {
        $this->bitplanesTwoAndThreeApplicationDetails->markAsApplied($method, $sourceRegisterName, $value);
    }

    public function addMaskInstruction(string $instruction): void
    {
        $this->maskApplicationDetails->addInstruction($instruction);
    }

    public function getMaskApplicationDetails()
    {
        return $this->maskApplicationDetails;
    }

    public function getBitplanesZeroAndOneApplicationDetails()
    {
        return $this->bitplanesZeroAndOneApplicationDetails;
    }

    public function getBitplanesTwoAndThreeApplicationDetails()
    {
        return $this->bitplanesTwoAndThreeApplicationDetails;
    }

    public function getMaskInstructions(): array
    {
        return $this->maskApplicationDetails->getInstructions();
    }
}

class SixteenPixelBlockCollection
{
    private array $blocks = [];

    public function addBlock(SixteenPixelBlock $block)
    {
        $this->blocks[] = $block;
    }

    public function hasBlockThatAppliedValueUsingRegister($value)
    {
        foreach ($this->blocks as $block) {
            if ($block->appliedValueUsingRegister($value)) {
                return true;
            }
        }

        return false;
    }

    public function getBlockThatAppliedValueUsingRegister($value)
    {
        foreach ($this->blocks as $block) {
            if ($block->appliedValueUsingRegister($value)) {
                return $block;
            }
        }

        throw new \Exception('Unable to find block that applied value using register');
    }

    public function getInstructionStream()
    {
        $instructions = [];

        foreach ($this->blocks as $block) {
            foreach ($block->getMaskApplicationDetails()->getInstructions() as $instruction) {
                $instructions[] = $instruction;
            }

            foreach ($block->getBitplanesZeroAndOneApplicationDetails()->getInstructions() as $instruction) {
                $instructions[] = $instruction;
            }

            foreach ($block->getBitplanesTwoAndThreeApplicationDetails()->getInstructions() as $instruction) {
                $instructions[] = $instruction;
            }
        }

        return $instructions;
    }

    public function getBlockByOffset($offset): SixteenPixelBlock
    {
        return $this->blocks[$offset];
    }

    public function getValueApplicationDetailsThatAppliedValueUsingRegister($value): ValueApplicationDetails
    {
        foreach ($this->blocks as $block) {
            if ($block->appliedValueUsingRegister($value)) {
                return $block->getValueApplicationDetailsThatAppliedValueUsingRegister($value);
            }
        }

        throw new \Exception('FAIL');
    }
}

class RegisterCollection
{
    const REG_D1 = 'd1';
    const REG_D2 = 'd2';
    const REG_D3 = 'd3';
    const REG_D4 = 'd4';
    const REG_D5 = 'd5';
    const REG_D6 = 'd6';
    const REG_D7 = 'd7';
    const REG_A0 = 'a0';
    const REG_A1 = 'a1';
    const REG_A2 = 'd2';
    const REG_A3 = 'a3';
    const REG_A4 = 'a4';
    const REG_A5 = 'a5';
    const REG_A6 = 'a6';

    const KNOWN_REGISTERS = [
        self:: REG_D1,
        self:: REG_D2,
        self:: REG_D3,
        self:: REG_D4,
        self:: REG_D5,
        self:: REG_D6,
        self:: REG_D7,
        self:: REG_A0,
        self:: REG_A1,
        self:: REG_A2,
        self:: REG_A3,
        self:: REG_A4,
        self:: REG_A5,
        self:: REG_A6,
    ];

    private $registers;

    public function __construct()
    {
        $this->registers = [
            self::REG_D1 => new Register(self::REG_D1),
            self::REG_D2 => new Register(self::REG_D2),
            self::REG_D3 => new Register(self::REG_D3),
            self::REG_D4 => new Register(self::REG_D4),
            self::REG_D5 => new Register(self::REG_D5),
            self::REG_D6 => new Register(self::REG_D6),
            self::REG_D7 => new Register(self::REG_D7),
            self::REG_A0 => new Register(self::REG_A0),
            self::REG_A1 => new Register(self::REG_A1),
            self::REG_A2 => new Register(self::REG_A2),
            self::REG_A3 => new Register(self::REG_A3),
            self::REG_A4 => new Register(self::REG_A4),
            self::REG_A5 => new Register(self::REG_A5),
            self::REG_A6 => new Register(self::REG_A6),
        ];
    }

    public function hasRegisterWithValue($value)
    {
        foreach ($this->registers as $register) {
            if ($register->getCurrentValue() == $value) {
                return true;
            }
        }

        return false;
    }

    public function getRegisterWithValue(int $offset, int $value)
    {
        foreach ($this->registers as $register) {
            if ($register->getCurrentValue() == $value) {
                $register->setLastReadOffset($offset);
                return $register;
            }
        }

        return false;
    }

    public function allocateValueToMostEligibleRegister(int $value)
    {
        foreach ($this->registers as $register) {
            if ($register->getCurrentValue() === null) {
                $register->clearLastReadOffset();
                $register->setCurrentValue($value);

                /*printf(
                    "allocated value 0x%x to empty register %s\n",
                    $value,
                    $register->getName()
                );*/

                return;
            }
        }

        $registerWithOldestLastReadValue = $this->getRegisterWithOldestLastReadOffset();
        $registerWithOldestLastReadValue->clearLastReadOffset();
        $registerWithOldestLastReadValue->setCurrentValue($value);

        /*printf(
            "replace existing value in register %s with value 0x%x\n",
            $registerWithOldestLastReadValue->getName(),
            $value
        );*/
    }

    private function getRegisterWithOldestLastReadOffset()
    {
        $registerWithOldestReadValue = null;

        foreach ($this->registers as $register) {
            if ($register->getCurrentValue() === null) {
                throw new \Exception('Should only be called when registers are fully populated');
            }

            //printf("register %s was last read at offset %d\n",$register->getName(), $register->getLastReadOffset());
            if ($registerWithOldestReadValue === null) {
                $registerWithOldestReadValue = $register;
            } else {
                if ($register->getLastReadOffset() < $registerWithOldestReadValue->getLastReadOffset()) {
                    $registerWithOldestReadValue = $register;
                }
            }
        }

        //printf("returning register %s\n", $registerWithOldestReadValue->getName());
        return $registerWithOldestReadValue;
    }
}

class CompiledSpriteBuilder {

    const FRAMEBUFFER_BYTES_PER_LINE = 160;
    const BYTES_PER_16_PIXELS = 8;

	private string $data;
	private SixteenPixelBlockCollection $sixteenPixelBlockCollection;
    private RegisterCollection $registerCollection;
    private array $uniqueLongValues = [];
	private int $widthInSixteenPixelBlocks;
	private int $heightInLines;

	public function __construct(string $data, int $widthInSixteenPixelBlocks, int $heightInLines)
	{
		$this->data = $data; // this will need to be an array of bytes!
		$this->widthInSixteenPixelBlocks = $widthInSixteenPixelBlocks;
		$this->heightInLines = $heightInLines;
        $this->sixteenPixelBlockCollection = new SixteenPixelBlockCollection();
        $this->registerCollection = new RegisterCollection();

        $sourceOffset = 0;
        $destinationOffset = 0;
        $bytesToSkipAfterEachLine = self::FRAMEBUFFER_BYTES_PER_LINE - $widthInSixteenPixelBlocks * self::BYTES_PER_16_PIXELS;

		for ($y = 0; $y < $this->heightInLines; $y++) {
            for ($x = 0; $x < $this->widthInSixteenPixelBlocks; $x++) {
                $this->sixteenPixelBlockCollection->addBlock(
                    new SixteenPixelBlock($sourceOffset, $destinationOffset)
                );

                $sourceOffset += 10;
                $destinationOffset += 8;
            }
            $destinationOffset += $bytesToSkipAfterEachLine;
        }
	}

    public function getInstructionStream(): array
    {
        $this->populateAndSortUniqueValues();
        $this->runFirstPass();

        return $this->sixteenPixelBlockCollection->getInstructionStream();
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

	private function populateAndSortUniqueValues()
	{
        $sourceOffset = 0;

		for ($y = 0; $y < $this->heightInLines; $y++) {
            for ($x = 0; $x < $this->widthInSixteenPixelBlocks; $x++) {
                $maskWordValue = $this->getWordAtSourceOffset($sourceOffset);
                $maskValue = ($maskWordValue << 16) | $maskWordValue;
                $sourceOffset += 2;
                $bitplanesZeroAndOneValue = $this->getLongAtSourceOffset($sourceOffset);
                $sourceOffset += 4;
                $bitplanesTwoAndThreeValue = $this->getLongAtSourceOffset($sourceOffset);
                $sourceOffset += 4;

                $this->addUniqueLongValue($maskValue);
                $this->addUniqueLongValue($bitplanesZeroAndOneValue);
                $this->addUniqueLongValue($bitplanesTwoAndThreeValue);
            }
		}
	}

    private function addUniqueLongValue($value)
    {
        if (!isset($this->uniqueLongValues[$value])) {
            $this->uniqueLongValues[$value] = 0;
        }

        $this->uniqueLongValues[$value]++;
    }

    private function isValueSingleUse($value)
    {
        if (!isset($this->uniqueLongValues[$value])) {
            throw new \Exception('Requested value not found in unique long values');
        }

        return $this->uniqueLongValues[$value] == 1; 
    }

    private function handleSingleUseMaskValue(SixteenPixelBlock $block, int $maskValue)
    {
        // mask value is only used once - and.l the mask using an immediate value
        $block->addMaskInstruction(
            sprintf(
                'move.l #0x%x,d0',
                $maskValue
            )
        );
        $block->addMaskInstruction(
            sprintf(
                'and.l d0,(a1)',
                $maskValue
            )
        );
        $block->addMaskInstruction(
            sprintf(
                'and.l d0,4(a1)',
                $maskValue
            )
        );
    }

    private function handleMaskValueAlreadyInRegister(SixteenPixelBlock $block, int $maskValue, int $sourceOffset)
    {
        $register = $this->registerCollection->getRegisterWithValue($sourceOffset, $maskValue);
        $block->addMaskInstruction(
            sprintf(
                'and.l %s,(a1)',
                $register->getName()
            )
        );
        $block->addMaskInstruction(
            sprintf(
                'and.l %s,4(a1)',
                $register->getName()
            )
        );
        $block->markMaskAsApplied(
            ValueApplicationDetails::APPLICATION_METHOD_REGISTER,
            $register->getName(),
            $register->getCurrentValue()
        );
    }

    private function handleMaskValueFromRegisterAllocation(SixteenPixelBlock $block, int $maskValue, int $sourceOffset)
    {
        $this->registerCollection->allocateValueToMostEligibleRegister($maskValue);
        $register = $this->registerCollection->getRegisterWithValue($sourceOffset, $maskValue);
        $block->addMaskInstruction(
            sprintf(
                'move.l #0x%x,%s',
                $maskValue,
                $register->getName()
            )
        );
        $block->addMaskInstruction(
            sprintf(
                'and.l %s,(a1)',
                $register->getName()
            )
        );
        $block->addMaskInstruction(
            sprintf(
                'and.l %s,4(a1)',
                $register->getName()
            )
        );
        $block->markMaskAsApplied(
            ValueApplicationDetails::APPLICATION_METHOD_REGISTER,
            $register->getName(),
            $register->getCurrentValue()
        );
    }

    private function handleMaskValueFromPreviousInstruction(int $maskValue, int $destinationOffset)
    {
        $valueApplicationDetails = $this->sixteenPixelBlockCollection->getValueApplicationDetailsThatAppliedValueUsingRegister($maskValue);
        $valueApplicationDetails->addInstruction(
            sprintf(
                'and.l %s,%d(a1)',
                $valueApplicationDetails->getSourceRegisterName(),
                $destinationOffset - $valueApplicationDetails->getBlock()->getDestinationOffset(),
            )
        );
        $valueApplicationDetails->addInstruction(
            sprintf(
                'and.l %s,%d(a1)',
                $valueApplicationDetails->getSourceRegisterName(),
                ($destinationOffset - $valueApplicationDetails->getBlock()->getDestinationOffset()) + 4,
            )
        );
    }

    private function handleTwoColourPlanes($applicationDetails, int $currentValue, int $sourceOffset, string $mnemonic)
    {
        if ($this->isValueSingleUse($currentValue)) {
            // <mnemonic>.l immediate bitplanes 0 and 1 with post increment
            $applicationDetails->addInstruction(
                sprintf(
                    '%s.l #0x%x,(a1)+',
                    $mnemonic,
                    $currentValue
                )
            );
        } elseif ($this->registerCollection->hasRegisterWithValue($currentValue)) {
            $register = $this->registerCollection->getRegisterWithValue($sourceOffset, $currentValue);
            $applicationDetails->addInstruction(
                sprintf(
                    '%s.l %s,(a1)+',
                    $mnemonic,
                    $register->getName()
                )
            );
        } else {
            $this->registerCollection->allocateValueToMostEligibleRegister($currentValue);
            $register = $this->registerCollection->getRegisterWithValue($sourceOffset, $currentValue);
            $applicationDetails->addInstruction(
                sprintf(
                    'move.l #0x%x,%s',
                    $currentValue,
                    $register->getName()
                )
            );
            $applicationDetails->addInstruction(
                sprintf(
                    '%s.l %s,(a1)+',
                    $mnemonic,
                    $register->getName()
                )
            );
        }
    }

    private function runFirstPass()
    {
        $sixteenPixelBlockOffset = 0;
        $destinationOffset = 0;
        $previouslyWrittenDestinationOffset = 0;

		for ($y = 0; $y < $this->heightInLines; $y++) {
            printf("** new line!\n");
            for ($x = 0; $x < $this->widthInSixteenPixelBlocks; $x++) {
                $currentSixteenPixelBlock = $this->sixteenPixelBlockCollection->getBlockByOffset($sixteenPixelBlockOffset);

                $sourceOffset = $currentSixteenPixelBlock->getSourceOffset();

                //echo("destination offset is ".$destinationOffset."\n");

                $maskWordValue = $this->getWordAtSourceOffset($sourceOffset);
                $maskValue = ($maskWordValue << 16) | $maskWordValue;

                $bitplanesZeroAndOneValue = $this->getLongAtSourceOffset($sourceOffset + 2);
                $bitplanesTwoAndThreeValue = $this->getLongAtSourceOffset($sourceOffset + 6);

                if ($maskValue != 0xffffffff) {
                    // we need to do something for this block of 16 pixels
                    $requiredDestinationOffset = $currentSixteenPixelBlock->getDestinationOffset();
                    //printf("required destination offset is %d, current destination offset is %d\n",$requiredDestinationOffset,$destinationOffset);

                    if ($destinationOffset < $requiredDestinationOffset) {
                        $currentSixteenPixelBlock->addMaskInstruction(
                            sprintf(
                                'lea %d(a1),a1',
                                $requiredDestinationOffset - $destinationOffset
                            )
                        );
                        $destinationOffset = $requiredDestinationOffset;
                    }

                    if ($maskValue == 0 || $maskValue == 0xffffffff) {
                        // nothing to do here, move on
                        // if mask value is 0, we'll need to MOVE the bitplane words
                    } elseif ($this->isValueSingleUse($maskValue)) {
                        $this->handleSingleUseMaskValue($currentSixteenPixelBlock, $maskValue);
                    } elseif ($this->registerCollection->hasRegisterWithValue($maskValue)) {
                        // we have the mask value in a register - and.l the mask with a register value
                        $this->handleMaskValueAlreadyInRegister($currentSixteenPixelBlock, $maskValue, $sourceOffset);
                    } elseif ($this->sixteenPixelBlockCollection->hasBlockThatAppliedValueUsingRegister($maskValue)) {
                        // set the mask here from a previous point in the instruction stream
                        $this->handleMaskValueFromPreviousInstruction($maskValue, $destinationOffset);
                    } else {
                        // clear out the oldest register value and replace with a new one
                        // we now have the mask value in a register - and.l the mask with a register value
                        $this->handleMaskValueFromRegisterAllocation($currentSixteenPixelBlock, $maskValue, $sourceOffset);
                    }

                    // now handle bitplanes 0 and 1

                    $mnemonic = 'or';
                    if ($maskValue == 0) {
                        $mnemonic = 'move';
                    }

                    $applicationDetails = $currentSixteenPixelBlock->getBitplanesZeroAndOneApplicationDetails();
                    if ($bitplanesZeroAndOneValue > 0) {
                        $this->handleTwoColourPlanes($applicationDetails, $bitplanesZeroAndOneValue, $sourceOffset, $mnemonic);
                        $destinationOffset += 4;
                    }

                    if ($bitplanesZeroAndOneValue == 0 && $bitplanesTwoAndThreeValue != 0) {
                        $applicationDetails->addInstruction('addq.l #4,a1');
                        $destinationOffset += 4;
                    }

                    $applicationDetails = $currentSixteenPixelBlock->getBitplanesTwoAndThreeApplicationDetails();

                    if ($bitplanesTwoAndThreeValue > 0) {
                        $this->handleTwoColourPlanes($applicationDetails, $bitplanesTwoAndThreeValue, $sourceOffset, $mnemonic);
                        $destinationOffset += 4;
                    }

                }

                $sixteenPixelBlockOffset++;
            }
		}
    }
}

$treeWords = [65535, 0, 0, 0, 0, 65389, 144, 0, 146, 0, 45567, 3072, 0, 19968, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 34645, 26794, 0, 30890, 0, 231, 18704, 0, 65304, 0, 53247, 4096, 0, 12288, 0, 65535, 0, 0, 0, 0, 32897, 32320, 0, 32638, 0, 4, 45225, 0, 65531, 0, 4095, 49152, 0, 61440, 0, 65533, 2, 0, 2, 0, 49412, 16089, 0, 16123, 0, 2, 47761, 0, 65533, 0, 4095, 4096, 0, 61440, 0, 65529, 6, 0, 6, 0, 49216, 674, 0, 16319, 0, 12, 898, 0, 65523, 0, 511, 28672, 0, 65024, 0, 65496, 39, 0, 39, 0, 0, 42958, 0, 65535, 0, 0, 16388, 0, 65535, 0, 5119, 0, 0, 60416, 0, 65280, 119, 0, 255, 0, 0, 17511, 0, 65535, 0, 0, 49284, 0, 65535, 0, 207, 16384, 0, 65328, 0, 62208, 2238, 0, 3327, 0, 0, 33029, 0, 65535, 0, 0, 41145, 0, 65535, 0, 1039, 6240, 0, 64496, 0, 59904, 282, 0, 5631, 0, 0, 2152, 0, 65535, 0, 0, 276, 0, 65535, 0, 31, 57472, 0, 65504, 0, 63488, 356, 0, 2047, 0, 0, 5099, 0, 65535, 0, 0, 224, 0, 65535, 0, 63, 2688, 0, 65472, 0, 55296, 961, 0, 10239, 0, 0, 48576, 0, 65535, 0, 0, 3203, 0, 65535, 0, 273, 16518, 0, 65262, 0, 7680, 8348, 0, 57855, 0, 0, 59195, 0, 65535, 0, 0, 5121, 0, 65535, 0, 3, 2500, 0, 65532, 0, 34304, 31187, 0, 31231, 0, 0, 6224, 0, 65535, 0, 0, 33320, 0, 65535, 0, 3, 2076, 0, 65532, 0, 49152, 15081, 0, 16383, 0, 0, 34960, 0, 65535, 0, 0, 20896, 0, 65535, 0, 19, 1088, 0, 65516, 0, 57344, 3580, 0, 8191, 0, 0, 25552, 0, 65535, 0, 0, 5600, 0, 65535, 0, 3, 1248, 0, 65532, 0, 20480, 36064, 0, 45055, 0, 0, 12665, 0, 65535, 0, 0, 40008, 0, 65535, 0, 11, 8452, 0, 65524, 0, 34816, 16656, 0, 30719, 0, 0, 14400, 0, 65535, 0, 0, 23296, 0, 65535, 0, 1, 150, 0, 65534, 0, 50176, 14826, 0, 15359, 0, 0, 53480, 0, 65535, 0, 0, 257, 0, 65535, 0, 1, 8416, 0, 65534, 0, 49408, 12888, 0, 16127, 0, 0, 17541, 0, 65535, 0, 0, 10312, 0, 65535, 0, 1, 33810, 0, 65534, 0, 61696, 2561, 0, 3839, 0, 0, 1050, 0, 65535, 0, 0, 49824, 0, 65535, 0, 3, 56400, 0, 65532, 0, 59392, 5089, 0, 6143, 0, 0, 49152, 0, 65535, 0, 0, 40960, 0, 65535, 0, 1, 34828, 0, 65534, 0, 63520, 657, 0, 2015, 0, 49152, 77, 0, 16383, 0, 0, 40548, 0, 65535, 0, 7, 22784, 0, 65528, 0, 64592, 416, 0, 943, 0, 0, 33608, 0, 65535, 0, 0, 16443, 0, 65535, 0, 15, 34864, 0, 65520, 0, 65472, 33, 0, 63, 0, 0, 54113, 8192, 57343, 8192, 8192, 1658, 49152, 8191, 49152, 519, 44272, 0, 65016, 0, 65415, 96, 0, 120, 0, 16384, 11297, 4096, 45055, 4096, 8320, 39008, 16384, 40831, 16384, 207, 11552, 0, 65328, 0, 65409, 120, 0, 126, 0, 28672, 34656, 2048, 34815, 2048, 2624, 1042, 0, 62911, 0, 7, 96, 0, 65528, 0, 65324, 146, 0, 211, 0, 12556, 51282, 1536, 51443, 1536, 15488, 121, 33024, 17023, 33024, 31, 47456, 0, 65504, 0, 65045, 298, 0, 490, 0, 14848, 50190, 0, 50687, 0, 3584, 328, 24576, 37375, 24576, 32799, 2432, 0, 32736, 0, 65515, 16, 0, 20, 0, 61440, 3648, 0, 4095, 0, 0, 23818, 0, 65535, 0, 255, 59136, 0, 65280, 0, 65504, 0, 0, 31, 0, 49152, 7872, 23, 16360, 23, 0, 10690, 32768, 32767, 32768, 61439, 4096, 0, 4096, 0, 65533, 0, 0, 2, 0, 49676, 11376, 0, 15859, 0, 1029, 16962, 2048, 62458, 2048, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 61730, 3777, 0, 3805, 0, 1664, 39204, 24576, 39295, 24576, 16383, 0, 0, 49152, 0, 65528, 7, 0, 7, 0, 24582, 34080, 0, 40953, 0, 3018, 42036, 20480, 42037, 20480, 12287, 20480, 0, 53248, 0, 65531, 4, 0, 4, 0, 1158, 22592, 0, 64377, 0, 510, 3585, 24576, 40449, 24576, 4095, 61440, 0, 61440, 0, 65535, 0, 0, 0, 0, 60930, 68, 0, 4605, 0, 8191, 0, 49152, 8192, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65408, 56, 0, 127, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 0, 0, 7, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 1, 2, 1, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 1, 2, 1, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 0, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 0, 49152, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 32768, 16384, 32768, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65532, 2, 0, 3, 0, 16383, 0, 49152, 0, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 2, 0, 7, 0, 16383, 0, 49152, 0, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65528, 6, 1, 6, 1, 8191, 0, 49152, 8192, 49152, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65472, 8, 49, 14, 49, 1023, 32768, 19456, 45056, 19456, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65024, 20, 457, 54, 457, 255, 16384, 13056, 52224, 13056, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 63488, 0, 1075, 972, 1075, 63, 0, 56384, 9088, 56384, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 61440, 16, 4079, 16, 4079, 31, 0, 59360, 6144, 59360, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 64512, 0, 1023, 0, 1023, 127, 0, 65408, 0, 65408, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65472, 0, 63, 0, 63, 2047, 0, 63488, 0, 63488, 65535, 0, 0, 0, 0];
$treeWidth = 4;
$treeHeight = 54;

$carWords = [65535, 0, 0, 0, 0, 32769, 2016, 14364, 14364, 18402, 65535, 0, 0, 0, 0, 65534, 0, 0, 0, 1, 0, 32766, 32769, 32769, 32766, 32767, 0, 0, 0, 32768, 65532, 0, 1, 1, 2, 0, 65279, 0, 0, 65535, 16383, 0, 32768, 32768, 16384, 65528, 0, 3, 3, 4, 0, 32126, 33825, 33825, 31710, 8191, 0, 49152, 49152, 8192, 63488, 496, 1543, 1543, 504, 0, 32446, 39993, 39993, 25542, 31, 4032, 57376, 57376, 8128, 36864, 10224, 10247, 10247, 2040, 0, 15740, 64575, 64575, 960, 9, 4068, 57364, 57364, 8160, 36864, 8192, 12287, 12287, 0, 0, 32446, 64575, 64575, 960, 9, 4, 65524, 65524, 0, 32768, 16383, 16383, 16383, 0, 0, 65535, 65535, 65535, 0, 1, 65532, 65532, 65532, 0, 32768, 16383, 16383, 16383, 0, 0, 65535, 65535, 65535, 0, 1, 65532, 65532, 65532, 0, 32768, 16383, 16383, 16383, 0, 0, 65535, 61455, 61455, 4080, 1, 65532, 65532, 65532, 0, 32768, 0, 8191, 8191, 8192, 0, 0, 65535, 65535, 0, 1, 0, 65528, 65528, 4, 0, 49151, 32768, 32768, 32767, 0, 65535, 0, 0, 65535, 0, 65533, 1, 1, 65534, 0, 32768, 57344, 57344, 8191, 0, 0, 0, 0, 65535, 0, 1, 63, 63, 65472, 0, 49024, 65408, 65408, 0, 0, 8196, 8196, 8196, 4104, 0, 509, 511, 511, 0, 0, 49024, 65408, 65408, 127, 0, 0, 0, 0, 65535, 0, 509, 511, 511, 65024, 0, 49024, 65408, 65408, 64, 0, 0, 4104, 4104, 2448, 0, 509, 511, 511, 512, 0, 32800, 59424, 59424, 6080, 0, 0, 2064, 2064, 1440, 0, 1025, 1063, 1063, 984, 0, 65528, 65528, 65528, 0, 0, 0, 1056, 1056, 960, 0, 8191, 8191, 8191, 0, 0, 65534, 65534, 65534, 0, 0, 960, 0, 0, 960, 0, 32767, 32767, 32767, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2016, 0, 0, 0, 0, 0, 0, 0, 12928, 12928, 2304, 0, 0, 576, 576, 1440, 0, 0, 332, 332, 144, 0, 0, 16287, 16287, 0, 0, 30750, 32769, 32769, 32766, 0, 0, 63996, 63996, 0, 31, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 63488, 0, 0, 0, 0, 31, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 63488, 0, 0, 0, 0, 32895, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65025, 0, 0, 0, 0];
$carWidth = 3;
$carHeight = 25;

$str= '';
foreach ($carWords as $word) {
    $str .= chr($word >> 8);
    $str .= chr($word & 255);
}

$builder = new CompiledSpriteBuilder($str, $carWidth, $carHeight);
$instructionStream = $builder->getInstructionStream();

//var_dump($instructionStream);

