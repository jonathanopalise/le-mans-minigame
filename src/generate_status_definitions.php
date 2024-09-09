<?php

require_once 'library.php';

if ($argc < 3) {
    echo("Usage: php generate_status_definitions.php [inputFile] [outputFile]\n");
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
        'label' => 'caption-time',
        'left' => 3,
        'top' => 1152,
        'width' => 36,
        'height' => 9,
        'targetLine' => 8,
    ],
    [
        'label' => 'caption-score',
        'left' => 3,
        'top' => 1141,
        'width' => 45,
        'height' => 9,
        'targetLine' => 8,
    ],
    [
        'label' => 'caption-high',
        'left' => 3,
        'top' => 1163,
        'width' => 36,
        'height' => 9,
        'targetLine' => 8,
    ],
     [
        'label' => 'large-digit-0',
        'left' => 183,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-1',
        'left' => 3,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-2',
        'left' => 23,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-3',
        'left' => 43,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-4',
        'left' => 63,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-5',
        'left' => 83,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-6',
        'left' => 103,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-7',
        'left' => 123,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-8',
        'left' => 143,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'large-digit-9',
        'left' => 163,
        'top' => 1092,
        'width' => 18,
        'height' => 18,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-0',
        'left' => 93,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-1',
        'left' => 3,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-2',
        'left' => 13,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-3',
        'left' => 23,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-4',
        'left' => 33,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-5',
        'left' => 43,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-6',
        'left' => 53,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-7',
        'left' => 63,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-8',
        'left' => 73,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'small-digit-9',
        'left' => 83,
        'top' => 1130,
        'width' => 9,
        'height' => 9,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-0',
        'left' => 53,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-1',
        'left' => 66,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-2',
        'left' => 78,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-3',
        'left' => 91,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-4',
        'left' => 104,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-5',
        'left' => 117,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-6',
        'left' => 130,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-7',
        'left' => 143,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-8',
        'left' => 156,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
    [
        'label' => 'speedo-9',
        'left' => 169,
        'top' => 2348,
        'width' => 9,
        'height' => 12,
        'targetLine' => 19,
    ],
];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);
$exportedSprites = [];

$gradientColours = [
    15,15,15,15,15,
    14,14,14,14,14,
    13,13,13,13,13,
    12,12,12,12,12,
    11,11,11,11,11,
    10,10,10,10,10,
    9,9,9,9,9,
    8,8,8,8,8,
    7,7,7,7,7,
    6,6,6,6,6,
    5,5,5,5,5,
    4,4,4,4,4,
];

foreach ($definitions as $definition) {
    $croppedIndexedBitmap = $indexedBitmap->extractRegionToIndexedBitmap(
        $definition['left'],
        $definition['top'],
        $definition['width'],
        $definition['height'],
    )->getCopyRoundedTo16PixelDivisibleWidth();

    $targetLineIndex = $definition['targetLine'];
    for ($ypos = 0; $ypos < $croppedIndexedBitmap->getHeight(); $ypos++) {
        for ($xpos = 0; $xpos < $croppedIndexedBitmap->getWidth(); $xpos++) {
            $pixel = $croppedIndexedBitmap->getPixelObject($xpos, $ypos);
            // something very wrong needs to be fixed in library here
            if ($pixel->getVisible()) {
                $pixel->makeVisible($gradientColours[$targetLineIndex]);
            } else {
                if ($pixel->getColourIndex() == 9) {
                    $pixel->setColourIndex(1);
                }
            }
        }
        $targetLineIndex++;
    }

    $bitplaneMergedWords = [];
    for ($line = 0; $line < $definition['height']; $line++) {
        $indexedBitmapLine = $croppedIndexedBitmap->getLineAt($line);
        $bitplane0Words = $indexedBitmapLine->toBitplaneWordSequence(0);
        $bitplane1Words = $indexedBitmapLine->toBitplaneWordSequence(1);
        $bitplane2Words = $indexedBitmapLine->toBitplaneWordSequence(2);
        $bitplane3Words = $indexedBitmapLine->toBitplaneWordSequence(3);

        for ($wordIndex = 0; $wordIndex < $bitplane0Words->getLength(); $wordIndex++) {
            $bitplaneMergedWords[] = $bitplane0Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane1Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane2Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane3Words->getWordAt($wordIndex);
        }
    }

    $exportedSprite = [
        'source_data_width_pixels' => $definition['width'],
        'source_data_height' => $definition['height'],
        'words' => $bitplaneMergedWords,
    ];

    $exportedSprites[] = $exportedSprite;
}


ob_start();
require('status_definitions_template.php');
$output = ob_get_clean();

$result = file_put_contents($definitionsOutputFilename, $output);
if ($result === false) {
    echo("Unable to write status sprites data");
    exit(1);
}

echo("status definitions generation complete!\n");
