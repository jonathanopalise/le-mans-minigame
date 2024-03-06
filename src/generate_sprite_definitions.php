<?php

require_once 'library.php';

if ($argc < 3) {
    echo("Usage: php generate_sprite_definitions.php [inputFile] [outputFile]\n");
    exit(1);
}

$inputFilename = $argv[1];
$outputFilename = $argv[2];

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

    $exportedSprites[] = [
        'origin_x' => $croppedIndexedBitmap->getOriginX(),
        'origin_y' => $croppedIndexedBitmap->getOriginY(),
        'source_data_width' => $maskedSprite->getWidth(),
        'source_data_height' => $maskedSprite->getHeight(),
        'words' => $planarData->getWords(),
    ];
}


ob_start();
require('sprite_definitions_template.php');
$output = ob_get_clean();

$result = file_put_contents($outputFilename, $output);
if ($result === false) {
    echo("Unable to write ground sprites data");
    exit(1);
}

