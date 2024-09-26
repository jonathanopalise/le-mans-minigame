<?php

// TODO: strict types?

class IndexedBitmapPixel
{
    private $colourIndex;
    private $visible;

    public function __construct(int $colourIndex, bool $visible)
    {
        $this->validateColourIndex($colourIndex);
        $this->colourIndex = $colourIndex;
        $this->visible = $visible;
    }

    public function getColourIndex(): int
    {
        return $this->colourIndex;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    // something very wrong here!
    // i think visible and invisible are inverted
    public function makeVisible(int $colourIndex)
    {
        $this->visible = false;
        $this->setColourIndex($colourIndex);
    }

    public function setColourIndex(int $colourIndex)
    {
        $this->validateColourIndex($colourIndex);
        $this->colourIndex = $colourIndex;
    }

    private function validateColourIndex(int $colourIndex)
    {
        if ($colourIndex < 0 || $colourIndex > 15) {
            throw new RuntimeException('Colour index out of range');
        }
    }
}

class IndexedBitmapLine {
    private $pixels = [];

    public function addPixel(IndexedBitmapPixel $indexedBitmapPixel)
    {
        $this->pixels[] = $indexedBitmapPixel;
    }

    public function getPixel(int $xpos)
    {
        if (!isset($this->pixels[$xpos])) {
            throw new RuntimeException('Unable to fetch pixel at position ' . $xpos);
        }

        return $this->pixels[$xpos];
    }

    public function getWidth()
    {
        return count($this->pixels);
    }

    public function toBitplaneWordSequence($bitplaneIndex)
    {
        $this->validatePixelCount('Can only extract bitplanes where width divisible by 16');

        if ($bitplaneIndex < 0 || $bitplaneIndex > 3) {
            throw new RuntimeException('Bitplane index must be between 0 and 3 inclusive');
        }

        $andValue = 1 << $bitplaneIndex;
        $bits = [];

        $sixteenPixelArrays = array_chunk($this->pixels, 16);
        foreach ($sixteenPixelArrays as $sixteenPixelArray) {
            foreach ($sixteenPixelArray as $pixel) {
                $bits[] = ($pixel->getColourIndex() & $andValue) ? 1 : 0;
            }
        }

        $bitplane = new Bitplane($bits);

        return $bitplane->toWordSequence();
    }

    public function toMaskWordSequence()
    {
        $this->validatePixelCount('Can only extract mask where width divisible by 16');

        $bits = [];

        $sixteenPixelArrays = array_chunk($this->pixels, 16);
        foreach ($sixteenPixelArrays as $sixteenPixelArray) {
            foreach ($sixteenPixelArray as $pixel) {
                $bits[] = $pixel->getVisible() ? 1 : 0;
            }
        }

        $bitplane = new Bitplane($bits);

        return $bitplane->toWordSequence();
    }

    private function validatePixelCount(string $errorMessage)
    {
        $pixelCount = count($this->pixels);
        if ($pixelCount & 15 != 0) {
            throw new RuntimeException($errorMessage);
        }
    }
}

class IndexedBitmap {
    private $lines;
    private $originX;
    private $originY;

    private function __construct(array $lines, int $originX, int $originY)
    {
        $this->lines = $lines;
        $this->originX = $originX;
        $this->originY = $originY;
    }

    public static function loadGif($filename)
    {
        echo("Loading gif ".$filename."\n");

        $distinctPixelIndices = [];

        $image = imagecreatefromgif($filename);
        if ($image === false) {
            throw new RuntimeException('failed to open file ' . $filename);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $transparentIndex = imagecolorexact($image, 255, 0, 255);
        echo("transparent index is ".$transparentIndex."\n");

        $lines = [];
        for ($y = 0; $y < $height; $y++) {
            $line = new IndexedBitmapLine();
            for ($x = 0; $x < $width; $x++) {
                $pixelIndex = imagecolorat($image, $x, $y);

                $distinctPixelIndices[$pixelIndex] = true;

                $visible = $pixelIndex != $transparentIndex;
                if (!$visible) {
                    $pixelIndex = 0;
                }

                $line->addPixel(
                    new IndexedBitmapPixel($pixelIndex, !$visible)
                );
            }
    
            $lines[] = $line;
        }

        echo("distinct pixel indices: ".var_export(array_keys($distinctPixelIndices),1)."\n");

        return new static($lines, 0, 0);
    }

    public static function create($width, $height)
    {
        if ($width < 1) {
            throw new RuntimeException('Width must be 1 or greater');
        }

        if ($height < 1) {
            throw new RuntimeException('Height must be 1 or greater');
        }

        $lines = [];
        for ($y = 0; $y < $height; $y++) {
            $line = new IndexedBitmapLine();
            for ($x = 0; $x < $width; $x++) {
                $line->addPixel(
                    new IndexedBitmapPixel(0, true)
                );
            }
            $lines[] = $line;
        }

        return new static($lines, 0, 0);
    }

    public function putPixel($x, $y, $colourIndex)
    {
        if (!isset($this->lines[$y])) {
            throw new RuntimeException('y value '.$y.' out of range');
        }

        $this->lines[$y]->getPixel($x)->setColourIndex($colourIndex);
    }

    public function getPixelObject($x, $y)
    {
        if (!isset($this->lines[$y])) {
            throw new RuntimeException('y value '.$y.' out of range');
        }

        return $this->lines[$y]->getPixel($x);
    }

    public function getPixel($x, $y)
    {
        if (!isset($this->lines[$y])) {
            throw new RuntimeException('y value '.$y.' out of range');
        }

        return $this->lines[$y]->getPixel($x)->getColourIndex();
    }

    public function extractRegionToIndexedBitmap($left, $top, $width, $height, $originX = 0, $originY = 0)
    {
        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();

        $maxX = $sourceWidth - 1;
        $maxY = $sourceHeight - 1;

        $x1 = $left;
        $y1 = $top;
        $x2 = ($left + $width) - 1;
        $y2 = ($top + $height) - 1;

        if ($width < 1) {
            throw new RuntimeException('Width must be 1 or greater');
        }

        if ($height < 1) {
            throw new RuntimeException('Height must be 1 or greater');
        }

        if ($left < 0) {
            throw new RuntimeException('Left must be greater than 0');
        }

        if ($top < 0) {
            throw new RuntimeException('Top must be greater than 0');
        }

        if ($x2 > $maxX) {
            throw new RuntimeException('Specified extract is beyond right hand side of image');
        }

        if ($y2 > $maxY) {
            throw new RuntimeException('Specified region is beyond bottom of image');
        }

        $lines = [];
        $offset = 0;
        for ($y = $y1; $y <= $y2; $y++) {
            $line = new IndexedBitmapLine();
            for ($x = $x1; $x <= $x2; $x++) {
                $line->addPixel($this->lines[$y]->getPixel($x));
            }
            $lines[] = $line;
        }

        return new static($lines, $originX, $originY);
    }

    public function getScaledCopy(int $scaledWidth, int $scaledHeight)
    {
        $originalWidth = $this->getWidth();
        $originalHeight = $this->getHeight();

        $lines = [];
        $offset = 0;
        for ($y = 0; $y < $scaledHeight; $y++) {
            $line = new IndexedBitmapLine();
            for ($x = 0; $x < $scaledWidth; $x++) {
                $sourceY = intval(($originalHeight / $scaledHeight) * $y);
                $sourceX = intval(($originalWidth / $scaledWidth) * $x);

                $line->addPixel($this->lines[$sourceY]->getPixel($sourceX));
            }
            $lines[] = $line;
        }

        $scaledOriginX = intval($this->originX / $originalWidth * $scaledWidth);
        $scaledOriginY = intval($this->originY / $originalHeight * $scaledHeight);

        return new static($lines, $scaledOriginX, $scaledOriginY);
    }

    public function getCopyRoundedTo16PixelDivisibleWidth()
    {
        $firstLineWidth = $this->lines[0]->getWidth();

        if (($firstLineWidth & 15) == 0) {
            return new static($this->lines, $this->originX, $this->originY);
        }

        $expectedLineLength = ($firstLineWidth + 16) & 0xfff0;

        $lines = [];
        foreach ($this->lines as $line) {
            $lineClone = clone $line;
            while ($lineClone->getWidth() < $expectedLineLength) {
                $lineClone->addPixel(
                    new IndexedBitmapPixel(0, true)
                );
            }
            $lines[] = $lineClone;
        }

        return new static($lines, $this->originX, $this->originY);
    }

    public function matchesDimensionsOf(IndexedBitmap $indexedBitmap)
    {
        return $this->getWidth() == $indexedBitmap->getWidth() &&
            $this->getHeight() == $indexedBitmap->getHeight();
    }

    public function getWidth()
    {
        return $this->lines[0]->getWidth();
    }

    public function getHeight()
    {
        return count($this->lines);
    }

    public function getOriginX()
    {
        return $this->originX;
    }

    public function getOriginY()
    {
        return $this->originY;
    }


    public function getLineAt(int $lineIndex)
    {
        if (!isset($this->lines[$lineIndex])) {
            throw new RuntimeException('Requested non-existent line number');
        }

        return $this->lines[$lineIndex];
    }
}

class Bitplane
{
    private $bits;

    public function __construct(array $bits)
    {
        if ((count($bits) & 15) != 0) {
            throw new RuntimeException('Bit count must be divisible by 16 ('.(count($bits) & 15).' found)');
        }

        foreach ($bits as $bit) {
            if (!is_integer($bit)) {
                throw new RuntimeException('Non-integer value found in bit array: '.var_export($bit,1));
            }

            if ($bit != 0 && $bit != 1) {
                throw new RuntimeException('Bitplanes must only contain 0 or 1');
            }
        }

        $this->bits = $bits;
    }

    public function getShiftedCopy(int $bitsToShift)
    {
        if ($bitsToShift == 0) {
            return clone $this;
        } elseif ($bitsToShift > 15) {
            throw new RuntimeException('Invalid shift value');
        }

        $shiftedBits = array_merge(
            array_fill(0, $bitsToShift, 1),
            $this->bits,
            array_fill(0, 16 - $bitsToShift, 1)
        );

        return new static($shiftedBits);
    }

    public function toWordSequence()
    {
        $words = [];
        $bitsChunks = array_chunk($this->bits, 16);
        foreach ($bitsChunks as $bitsChunk) {
            $word = 0;
            for ($index = 0; $index < 16; $index++) {
                $bit = $bitsChunk[$index];
                $word |= $bit;
                if ($index < 15) {
                    $word <<= 1;
                }
            }
            $words[] = $word;
        }

        return new WordSequence($words);
    }
}

class WordSequence
{
    private $words;

    public function __construct(array $words)
    {
        foreach ($words as $word) {
            if (!is_integer($word)) {
                throw new RuntimeException('Non-integer value found in word array: '.var_export($word,1));
            }

            if ($word < 0 || $word > 65535) {
                throw new RuntimeException('Word sequences must only contain unsigned 16-bit values');
            }

            $this->words = $words;
        }
    }

    public function getShiftedCopy($bitsToShift)
    {
        $bits = [];
        foreach ($this->words as $word) {
            for ($index = 0; $index < 16; $index++) {
                $bits[] = ($word & 32768) >> 15;
                $word <<= 1;
            }
        }

        $bitplane = new Bitplane($bits);
        $shiftedBitplane = $bitplane->getShiftedCopy($bitsToShift);
        return $shiftedBitplane->toWordSequence();
    }

    public function getLength()
    {
        return count($this->words);
    }

    public function getPixelWidth()
    {
        return count($this->words) * 16;
    }

    public function getWordAt($index)
    {
        if (!isset($this->words[$index])) {
            throw new RuntimeException('Unable to fetch word at requested index');
        }

        return $this->words[$index];
    }
}


class SpriteLineBuilder
{
    const MAX_BITPLANE_INDEX = 3;

    private $width;
    private $bitplaneWordSequences = [];
    private $maskWordSequence = [];
    private $masked;

    public function __construct(int $width, bool $masked)
    {
        if ($width & 15 != 0) {
            echo("Sprite line width = ".$width."\n");
            throw new RuntimeException('Sprite line width must be divisible by 16');
        }

        if ($width < 16) {
            throw new RuntimeException('Sprite line width must be at least 16');
        }

        $this->width = $width;
        $this->masked = $masked;
    }

    public function setBitplaneWordSequence(int $bitplaneIndex, WordSequence $wordSequence)
    {
        if ($bitplaneIndex < 0 || $bitplaneIndex > self::MAX_BITPLANE_INDEX) {
            throw new RuntimeException('Bitplane index out of bounds');
        }

        if (!$this->isWordSequenceLengthValid($wordSequence)) {
            throw new RuntimeException('Bitplane word sequence length is not as expected');
        }

        $this->bitplaneWordSequences[$bitplaneIndex] = $wordSequence;
    }

    public function getBitplaneWordSequences()
    {
        return $this->bitplaneWordSequences;
    }

    public function setMaskWordSequence(WordSequence $wordSequence)
    {
        if (!$this->masked) {
            throw new RuntimeException('Cannot set mask word sequence on unmasked sprite');
        }

        if (!$this->isWordSequenceLengthValid($wordSequence)) {
            throw new RuntimeException('Mask word sequence length is not as expected');
        }

        $this->maskWordSequence = $wordSequence;
    }

    public function getMaskWordSequence()
    {
        return $this->maskWordSequence; 
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function build()
    {
        return SpriteLine::createFromSpriteLineBuilder($this);
    }

    public function isComplete()
    {
        if ($this->masked && is_null($this->maskWordSequence)) {
            return false;
        }

        for ($index = 0; $index <= self::MAX_BITPLANE_INDEX; $index++) {
            if (!isset($this->bitplaneWordSequences[$index])) {
                return false;
            }
        }

        return true;
    }

    private function isWordSequenceLengthValid(WordSequence $wordSequence)
    {
        return ($wordSequence->getLength() << 4) == $this->width;
    }
}

class SpriteLine
{
    private $width;
    private $bitplaneWordSequences = [];
    private $maskWordSequence;

    private function __construct()
    {
    }

    public static function createFromSpriteLineBuilder(SpriteLineBuilder $spriteLineBuilder)
    {
        if (!$spriteLineBuilder->isComplete()) {
            throw new RuntimeException('Cannot create SpriteLine from incomplete SpriteLineBuilder');
        }

        $spriteLine = new static();
        $spriteLine->width = $spriteLineBuilder->getWidth();
        $spriteLine->bitplaneWordSequences = $spriteLineBuilder->getBitplaneWordSequences();
        $spriteLine->maskWordSequence = $spriteLineBuilder->getMaskWordSequence();

        return $spriteLine;
    }

    public function isMasked()
    {
        return !is_null($this->maskWordSequence);
    }


    public function getMaskWordAt($wordIndex)
    {
        if (!$this->isMasked()) {
            throw new RuntimeException('Attempt to get mask word for non-masked sprite');
        }

        return $this->maskWordSequence->getWordAt($wordIndex);
    }

    public function getBitplaneWordAt($bitplaneIndex, $wordIndex)
    {
        if (!isset($this->bitplaneWordSequences[$bitplaneIndex])) {
            throw new RuntimeException('Attempt to fetch non-existent bitplane');
        }

        $bitplaneWordSequence = $this->bitplaneWordSequences[$bitplaneIndex];

        return $bitplaneWordSequence->getWordAt($wordIndex);
    }

    public function getWidthInWords()
    {
        return $this->width >> 4;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getShiftedCopy(int $bitsToShift)
    {
        if ($bitsToShift < 0 || $bitsToShift > 15) {
            throw new RuntimeException('Invalid bits to shift value');
        }

        $shiftedSpriteLine = new static();

        for ($index = 0; $index < 4; $index++) {
            $shiftedBitplaneWordSequence = $this->bitplaneWordSequences[$index]->getShiftedCopy($bitsToShift);
            $shiftedSpriteLine->bitplaneWordSequences[$index] = $shiftedBitplaneWordSequence;
        }

        if (isset($this->maskWordSequence)) {
            $shiftedMaskWordSequence = $this->maskWordSequence->getShiftedCopy($bitsToShift);
            $shiftedSpriteLine->maskWordSequence = $shiftedMaskWordSequence;
        }

        $shiftedSpriteLine->width = $shiftedSpriteLine->bitplaneWordSequences[0]->getPixelWidth();

        return $shiftedSpriteLine;
    }

    public function getFinalMaskWord()
    {
        return $this->getMaskWordAt($this->getWidthInWords() - 1);
    }
}

class SpriteBuilder
{
    private $width;
    private $height;
    private $masked;
    private $spriteLines = [];

    public function __construct(int $width, int $height, bool $masked)
    {
        $this->width = $width;
        $this->height = $height;
        $this->masked = $masked;
    }

    public function addSpriteLine(SpriteLine $spriteLine)
    {
        if (!$spriteLine->getWidth() == $this->width) {
            throw new RuntimeException('Cannot add SpriteLine with mismatched width');
        }

        if (!$spriteLine->isMasked() == $this->masked) {
            throw new RuntimeException('Cannot add unmasked sprite line to masked sprite or vice-versa');
        }

        if (count($this->spriteLines) == $this->height) {
            throw new RuntimeException('Too many lines added to sprite');
        }

        $this->spriteLines[] = $spriteLine;
    }

    public function getMasked()
    {
        return $this->masked;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getSpriteLines()
    {
        return $this->spriteLines;
    }

    public function isComplete()
    {
        return count($this->spriteLines) == $this->height;
    }

    public function build()
    {
        return Sprite::createFromSpriteBuilder($this);
    }
}

class Sprite
{
    private $masked;
    private $spriteLines;

    private function __construct()
    {
    }

    public static function createFromSpriteBuilder(SpriteBuilder $spriteBuilder)
    {
        $sprite = new static();

        if (!$spriteBuilder->isComplete()) {
            throw new RuntimeException('Cannot create Sprite from incomplete SpriteBuilder');
        }

        $sprite->masked = $spriteBuilder->getMasked();
        $sprite->spriteLines = $spriteBuilder->getSpriteLines();

        return $sprite;
    }

    public function getShiftedCopy(int $bitsToShift)
    {
        if ($bitsToShift < 0 || $bitsToShift > 15) {
            throw new RuntimeException('Invalid bits to shift value');
        }

        $shiftedSpriteLines = [];

        foreach ($this->spriteLines as $spriteLine) {
            $shiftedSpriteLines[] = $spriteLine->getShiftedCopy($bitsToShift);
        }

        $shiftedSprite = new static();
        $shiftedSprite->masked = $this->masked;
        $shiftedSprite->spriteLines = $shiftedSpriteLines;

        return $shiftedSprite;
    }

    public function getWidth()
    {
        return $this->spriteLines[0]->getWidth();
    }

    public function getHeight()
    {
        return count($this->spriteLines);
    }

    public function exportToCompiledSprite()
    {
    }

    public function exportToWords(bool $includeBitplaneWords, bool $includeMaskWords)
    {
        $words = [];
        $widthInWords = $this->spriteLines[0]->getWidthInWords();

        foreach ($this->spriteLines as $spriteLine) {
            for ($wordIndex = 0; $wordIndex < $widthInWords; $wordIndex++) {
                if ($includeMaskWords && $this->masked) {
                    $words[] = $spriteLine->getMaskWordAt($wordIndex);
                }
                if ($includeBitplaneWords) {
                    for ($bitplaneIndex = 0; $bitplaneIndex < 4; $bitplaneIndex++) {
                        $words[] = $spriteLine->getBitplaneWordAt($bitplaneIndex, $wordIndex);
                    }
                }
            }
        }

        return $words;
    }

    public function getEmptyPixelsOnRight()
    {
        $cumulativeFinalMaskWord = 0xffff;
        foreach ($this->spriteLines as $spriteLine) {
            $cumulativeFinalMaskWord &= $spriteLine->getFinalMaskWord();
        }

        $currentEmptyPixelsCount = 0;
        while (true) {
            if (($cumulativeFinalMaskWord & 1) == 0) {
                return $currentEmptyPixelsCount;
            }
            $currentEmptyPixelsCount++;
            $cumulativeFinalMaskWord >>= 1;
        }
    }

    public function getLongestRightEnd()
    {
        return 16 - $this->getEmptyPixelsOnRight();
    }
}

class PlanarData
{
    private $width;
    private $height;
    private $hasMask;
    private $words;

    public function __construct(int $width, int $height, bool $hasMask, array $words)
    {
        if ($width < 16) {
            throw new RuntimeException('Width must be at least 16');
        }

        if (($width & 15) != 0) {
            throw new RuntimeException('Width must be a multiple of 16');
        }

        $wordsPerSixteenPixelBlock = 4;
        if ($hasMask) {
            $wordsPerSixteenPixelBlock++;
        }

        $sixteenPixelBlocksPerLine = $width >> 4;
        $wordsPerLine = $sixteenPixelBlocksPerLine * $wordsPerSixteenPixelBlock;
        $expectedWordCount = $wordsPerLine * $height;

        if (count($words) != $expectedWordCount) {
            throw RuntimeException('Element count in words array does not match other parameters');
        }

        foreach ($words as $word) {
            if (!is_int($word)) {
                throw new RuntimeException('Array element does not contain int');
            }

            if ($word < 0 || $word > 65535) {
                throw new RuntimeException('Array element is out of word range');
            }
        }

        $this->words = $words;
    }

    public function getWords()
    {
        return $this->words;
    }

    public function exportToAsm(string $identifier)
    {
        $lines = [
            '    public ' . $identifier,
            '',
            $identifier . ':'
        ];

        foreach ($this->words as $word) {
            $lines[] = '    dc.w $' . dechex($word);
        }

        return implode("\n", $lines);
    }
}

class SpriteConvertor
{
    public static function createUnmaskedSprite(IndexedBitmap $bitmapData)
    {
        $sprite = new Sprite($bitmapData->getWidth(), $bitmapData->getHeight(), false);
        $bitmapData->extractBitplanesToSprite($sprite);
    }

    public static function createMaskedSprite(IndexedBitmap $indexedBitmap)
    {
        $indexedBitmapWidth = $indexedBitmap->getWidth();
        $indexedBitmapHeight = $indexedBitmap->getHeight();

        $spriteBuilder = new SpriteBuilder($indexedBitmapWidth, $indexedBitmapHeight, true);

        $lineIndex = 0;
        for ($lineIndex = 0; $lineIndex < $indexedBitmapHeight; $lineIndex++) {
            $spriteLineBuilder = new SpriteLineBuilder($indexedBitmapWidth, true);

            $line = $indexedBitmap->getLineAt($lineIndex);
            for ($index = 0; $index < 4; $index++) {
                $spriteLineBuilder->setBitplaneWordSequence(
                    $index,
                    $line->toBitplaneWordSequence($index)
                );
            }

            $spriteLineBuilder->setMaskWordSequence(
                $line->toMaskWordSequence()
            );

            $spriteBuilder->addSpriteLine(
                $spriteLineBuilder->build()
            );
        }

        return $spriteBuilder->build();
    }
}

