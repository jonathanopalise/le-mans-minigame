<?php

require_once 'library.php';
require_once 'sprite_spans.php';

function convertStringToByteArray()
{
}

if ($argc < 3) {
    echo("Usage: php generate_sprite_definitions.php [inputFile] [outputFile]\n");
    exit(1);
}

$inputFilename = $argv[1];
$definitionsOutputFilename = $argv[2];

if (!file_exists($inputFilename)) {
    echo("Input filename does not exist\n");
    exit(1);
}

$definitions = [
    [
        'label' => 'tree-2',
        'left' => 5,
        'top' => 5,
        'width' => 63,
        'height' => 54,
    ],
    [
        'label' => 'tree-3',
        'left' => 79,
        'top' => 18,
        'width' => 47,
        'height' => 40,
    ],
    [
        'label' => 'tree-4',
        'left' => 134,
        'top' => 25,
        'width' => 38,
        'height' => 32,
    ],
    [
        'label' => 'tree-5',
        'left' => 177,
        'top' => 31,
        'width' => 31,
        'height' => 26,
    ],
    [
        'label' => 'tree-6',
        'left' => 210,
        'top' => 34,
        'width' => 28,
        'height' => 22,
    ],
    [
        'label' => 'tree-7',
        'left' => 240,
        'top' => 38,
        'width' => 22,
        'height' => 18,
    ],
    [
        'label' => 'tree-8',
        'left' => 263,
        'top' => 40,
        'width' => 19,
        'height' => 16,
    ],
    [
        'label' => 'tree-9',
        'left' => 286,
        'top' => 43,
        'width' => 14,
        'height' => 12,
    ],

    // red car centre

    [
        'label' => 'red-car-centre-1',
        'left' => 3,
        'top' => 74,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'red-car-centre-2',
        'left' => 54,
        'top' => 74,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'red-car-centre-3',
        'left' => 93,
        'top' => 74,
        'width' => 28,
        'height' => 14,
    ],
    [
        'label' => 'red-car-centre-4',
        'left' => 123,
        'top' => 74,
        'width' => 23,
        'height' => 12,
    ],
    [
        'label' => 'red-car-centre-5',
        'left' => 149,
        'top' => 74,
        'width' => 21,
        'height' => 11,
    ],
    [
        'label' => 'red-car-centre-6',
        'left' => 172,
        'top' => 74,
        'width' => 16,
        'height' => 9,
    ],
    [
        'label' => 'red-car-centre-7',
        'left' => 190,
        'top' => 74,
        'width' => 14,
        'height' => 7,
    ],
    [
        'label' => 'red-car-centre-8',
        'left' => 206,
        'top' => 74,
        'width' => 11,
        'height' => 6,
    ],

    // red car left

    [
        'label' => 'red-car-left-1',
        'left' => 3,
        'top' => 100,
        'width' => 64,
        'height' => 25,
    ],
    [
        'label' => 'red-car-left-2',
        'left' => 69,
        'top' => 100,
        'width' => 48,
        'height' => 19,
    ],
    [
        'label' => 'red-car-left-3',
        'left' => 119,
        'top' => 100,
        'width' => 38,
        'height' => 16,
    ],
    [
        'label' => 'red-car-left-4',
        'left' => 159,
        'top' => 100,
        'width' => 31,
        'height' => 12,
    ],
    [
        'label' => 'red-car-left-5',
        'left' => 192,
        'top' => 100,
        'width' => 28,
        'height' => 11,
    ],
    [
        'label' => 'red-car-left-6',
        'left' => 222,
        'top' => 100,
        'width' => 22,
        'height' => 9,
    ],
    [
        'label' => 'red-car-left-7',
        'left' => 246,
        'top' => 100,
        'width' => 19,
        'height' => 8,
    ],
    [
        'label' => 'red-car-left-8',
        'left' => 267,
        'top' => 100,
        'width' => 14,
        'height' => 6,
    ],

    // red car right

    [
        'label' => 'red-car-right-1',
        'left' => 3,
        'top' => 131,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'red-car-right-2',
        'left' => 69,
        'top' => 131,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'red-car-right-3',
        'left' => 119,
        'top' => 131,
        'width' => 37,
        'height' => 15,
    ],
    [
        'label' => 'red-car-right-4',
        'left' => 158,
        'top' => 131,
        'width' => 31,
        'height' => 12,
    ],
    [
        'label' => 'red-car-right-5',
        'left' => 191,
        'top' => 131,
        'width' => 29,
        'height' => 11,
    ],
    [
        'label' => 'red-car-right-6',
        'left' => 221,
        'top' => 131,
        'width' => 22,
        'height' => 9,
    ],
    [
        'label' => 'red-car-right-7',
        'left' => 245,
        'top' => 131,
        'width' => 18,
        'height' => 8,
    ],
    [
        'label' => 'red-car-right-8',
        'left' => 265,
        'top' => 131,
        'width' => 14,
        'height' => 6,
    ],

    // blue car TBC ...

    [
        'label' => 'blue-car',
        'left' => 105,
        'top' => 200,
        'width' => 48,
        'height' => 25,
    ],
];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);
$exportedSprites = [];

foreach ($definitions as $definition) {
    $croppedIndexedBitmap = $indexedBitmap->extractRegionToIndexedBitmap(
        $definition['left'],
        $definition['top'],
        $definition['width'],
        $definition['height'],
        intval($definition['width'] / 2),
        $definition['height'] - 1
    )->getCopyRoundedTo16PixelDivisibleWidth();

    $maskedSprite = SpriteConvertor::createMaskedSprite($croppedIndexedBitmap);
    $planarData = $maskedSprite->exportToPlanarData();

    $exportedSprite = [
        'origin_x' => $croppedIndexedBitmap->getOriginX(),
        'origin_y' => $croppedIndexedBitmap->getOriginY(),
        'source_data_width' => $maskedSprite->getWidth(),
        'source_data_height' => $maskedSprite->getHeight(),
        'words' => $planarData->getWords(),
    ];

    for ($skew = 0; $skew < 16; $skew++) {
        printf(
            "masked sprite width: %d\n",
            $maskedSprite->getWidth()
        );
        $skewedMaskedSprite = $maskedSprite->getShiftedCopy($skew);
        printf(
            "skewed masked sprite width: %d\n",
            $skewedMaskedSprite->getWidth()
        );
        //$skewedMaskedSprite = $maskedSprite;
        $planarData = $maskedSprite->exportToPlanarData();
        $skewedPlanarData = $skewedMaskedSprite->exportToPlanarData();

        /*$planarDataWords = $planarData->getWords();
        $skewedPlanarDataWords = $skewedPlanarData->getWords();
        if ($planarDataWords != $skewedPlanarDataWords) {
            var_dump(array_slice($planarDataWords, 0, 10));
            var_dump(array_slice($skewedPlanarDataWords, 0, 10));
            echo("FAIL");
            exit(1);
        }*/

        // convert word data to byte data
        $skewedCharData= '';
        $words = $skewedPlanarData->getWords();

        /*if ($skewedMaskedSprite->getWidth() == 64 && $skewedMaskedSprite->getHeight() == 25) {

            $expectedRedCarLeft1Words = [65535, 0, 0, 0, 0, 61441, 56, 4036, 4036, 58, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 65535, 0, 0, 0, 0, 32768, 4351, 26880, 27904, 4863, 16383, 0, 32768, 32768, 16384, 65535, 0, 0, 0, 0, 65534, 0, 1, 1, 0, 0, 16387, 33741, 33741, 31794, 4095, 49152, 8192, 8192, 53248, 65535, 0, 0, 0, 0, 65532, 0, 3, 3, 0, 0, 15, 3603, 28179, 37356, 2047, 61440, 18432, 18432, 45056, 65535, 0, 0, 0, 0, 49152, 8176, 8206, 8206, 8177, 0, 3, 31, 61471, 4064, 15, 64960, 45600, 45600, 19920, 65535, 0, 0, 0, 0, 32768, 30712, 30726, 30726, 2041, 0, 3, 11, 65035, 500, 3, 64240, 56584, 56584, 8948, 65535, 0, 0, 0, 0, 0, 64504, 64518, 64519, 1016, 0, 2055, 2055, 34823, 29176, 1, 65404, 61058, 61058, 4476, 65528, 4, 4, 4, 0, 0, 40192, 57087, 40703, 16640, 0, 2055, 2055, 2055, 61944, 0, 65535, 63487, 63487, 2048, 0, 65532, 65532, 65532, 0, 0, 3776, 36671, 3903, 32960, 0, 6655, 39423, 47615, 16384, 0, 65535, 65535, 65535, 0, 0, 65532, 65532, 65532, 0, 0, 3967, 36851, 4083, 32780, 0, 63999, 63999, 63999, 0, 0, 65535, 65535, 65535, 0, 0, 65532, 65532, 65532, 0, 0, 35838, 40956, 40956, 3, 0, 47615, 47615, 47615, 16384, 0, 65535, 65415, 65415, 120, 0, 65532, 65532, 65532, 0, 0, 35583, 36351, 36351, 4608, 0, 63999, 63999, 63999, 0, 0, 65408, 63615, 63615, 1920, 0, 12, 65532, 65532, 0, 0, 35535, 36335, 36303, 544, 0, 30720, 63999, 63999, 0, 0, 127, 65408, 65408, 127, 0, 65520, 12, 12, 65520, 0, 36231, 36551, 36487, 320, 0, 39423, 63488, 63488, 511, 0, 65408, 0, 0, 65535, 1, 0, 2, 2, 65532, 32768, 3719, 4039, 3975, 64, 0, 49152, 63488, 63488, 2047, 0, 0, 0, 0, 65288, 1, 1532, 1534, 1534, 512, 32768, 3975, 4039, 3975, 64, 0, 57280, 65472, 65472, 32, 0, 0, 0, 0, 4351, 1, 1532, 1534, 1534, 64000, 32768, 3975, 4039, 3975, 80, 0, 57280, 65472, 65472, 63, 0, 128, 136, 136, 65296, 1, 1532, 1534, 1534, 512, 49152, 1923, 1987, 1923, 72, 0, 57280, 65472, 65472, 32, 0, 128, 4240, 4240, 2336, 1, 3072, 3118, 3118, 976, 64512, 899, 963, 899, 88, 0, 49152, 49152, 49152, 16352, 0, 128, 2208, 2208, 1344, 7, 5112, 5112, 5112, 0, 65024, 387, 451, 387, 72, 0, 49120, 49120, 49120, 16384, 0, 2112, 3072, 3072, 704, 15, 26608, 26608, 26608, 0, 65280, 130, 194, 130, 81, 0, 65472, 65472, 65472, 0, 0, 2688, 3200, 3200, 832, 31, 0, 0, 0, 0, 65408, 64, 64, 64, 0, 0, 0, 0, 0, 0, 0, 2304, 2895, 2895, 1200, 31, 0, 2304, 2304, 5248, 65504, 0, 0, 0, 0, 0, 0, 17535, 17535, 34816, 48, 0, 61440, 61440, 4032, 63, 0, 4608, 4608, 11520, 65504, 0, 0, 0, 1, 127, 0, 34816, 34816, 29696, 65528, 0, 0, 0, 0, 63, 0, 0, 0, 0, 65520, 0, 0, 0, 0, 255, 0, 0, 0, 0, 65534, 0, 0, 0, 0, 255, 0, 0, 0, 0];

            var_dump($words);

            printf("comparing %d and %d\n", count($words), count($fooWords));
            exit(1);
        }*/

        foreach ($words as $word) {
            $skewedCharData .= chr($word >> 8);
            $skewedCharData .= chr($word & 255);
        }

        $widthInPixels = $skewedMaskedSprite->getWidth();
        var_dump($widthInPixels);
        $widthIn16PixelBlocks = $skewedMaskedSprite->getWidth() / 16;
        var_dump($widthIn16PixelBlocks);
        printf("width in 16 pixel blocks: %d\n", $widthIn16PixelBlocks);

        $builder = new CompiledSpriteBuilder(
            $skewedCharData,
            $widthIn16PixelBlocks,
            $skewedMaskedSprite->getHeight(),
            $skew
        );
        $instructions = $builder->runFirstPass();

        $processedInstructions = [];
        foreach ($instructions as $instruction) {
            $processedInstructions[] = '    ' . $instruction;
        }

        $filenameWithoutExtension = sys_get_temp_dir() . '/' . $definition['label'] . '-' . $skew;
        $sourceFilename = $filenameWithoutExtension. '.s';
        $outputFilename = $filenameWithoutExtension. '.bin';

        printf(
            "Writing source for %s skew %d to file %s\n",
            $definition['label'],
            $skew,
            $sourceFilename
        );

        file_put_contents($sourceFilename, implode("\n", $processedInstructions));

        // TODO: pass in name of vasm command
        $assembleCommand = sprintf(
            'vasmm68k_mot %s -Fbin -o %s',
            $sourceFilename,
            $outputFilename
        );

        printf(
            "Assembling source in file %s\n",
            $sourceFilename
        );

        $result = exec($assembleCommand);
        if ($result === false) {
            printf("assembly failed\n");
            exit(1);
        }

        $binaryCode = file_get_contents($outputFilename);
        $exportedSprite['skew_' . $skew] = unpack('C*', $binaryCode); 
    }

    $exportedSprites[] = $exportedSprite;
}


ob_start();
require('sprite_definitions_template.php');
$output = ob_get_clean();

$result = file_put_contents($definitionsOutputFilename, $output);
if ($result === false) {
    echo("Unable to write ground sprites data");
    exit(1);
}

echo("sprite definitions generation complete!\n");
