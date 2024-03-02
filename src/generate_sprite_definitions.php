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
    [
        'label' => 'car',
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

