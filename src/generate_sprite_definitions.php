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
        'label' => 'tree',
        'left' => 5,
        'top' => 5,
        'width' => 63,
        'height' => 54,
    ],
];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);
$exportedSprites = [];

foreach ($definitions as $definition) {
    $indexedBitmap = $indexedBitmap->extractRegionToIndexedBitmap(
        $definition['left'],
        $definition['top'],
        $definition['width'],
        $definition['height'],
        intval($definition['width'] / 2),
        $definition['height'] - 1
    )->getCopyRoundedTo16PixelDivisibleWidth();

    $maskedSprite = SpriteConvertor::createMaskedSprite($indexedBitmap);
    $planarData = $maskedSprite->exportToPlanarData();

    $exportedSprites[] = [
        'origin_x' => $indexedBitmap->getOriginX(),
        'origin_y' => $indexedBitmap->getOriginY(),
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

