<?php

include('library.php');

if ($argc < 3) {
    echo("Usage: php generate_mountain_graphics.php [inputFile] [outputFile]\n");
    exit(1);
}       

$inputFilename = $argv[1];
$outputFilename = $argv[2];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);

// workaround for the fact that gimp insists on removing unused colours from the colour map
for ($ypos = 0; $ypos < $indexedBitmap->getHeight(); $ypos++) {
    for ($xpos = 0; $xpos < $indexedBitmap->getWidth(); $xpos++) {
        $index = $indexedBitmap->getPixel($xpos, $ypos);
        $indexedBitmap->putPixel($xpos, $ypos, $index + 1);
    }
}

$outputWords = [];
$outputWordLines = [];

for ($lineIndex = 0; $lineIndex < $indexedBitmap->getHeight(); $lineIndex++) {
    $indexedBitmapLine = $indexedBitmap->getLineAt($lineIndex);
    $bitplane0Words = $indexedBitmapLine->toBitplaneWordSequence(0);
    $bitplane1Words = $indexedBitmapLine->toBitplaneWordSequence(1);

    $bitplaneMergedWords = [];
    for ($wordIndex = 0; $wordIndex < $bitplane0Words->getLength(); $wordIndex++) {
        $bitplaneMergedWords[] = $bitplane0Words->getWordAt($wordIndex);
        $bitplaneMergedWords[] = $bitplane1Words->getWordAt($wordIndex);
    }

    $outputWords = array_merge($outputWords, $bitplaneMergedWords);
}

$lines = [
    '#include "../mountain_graphics.h"',
    '#include <inttypes.h>',
    '',
    'uint16_t mountain_graphics[] = {',
];

foreach ($outputWords as $key => $outputWord) {
    $line = (string)$outputWord;

    if ($key !== array_key_last($outputWords)) {
        $line .= ',';
    }

    $lines[] = $line;
}

$lines = array_merge(
    $lines,
    [
        '};',
    ]
);

$output = implode("\n", $lines);

$result = file_put_contents($outputFilename, $output);
if ($result === false) {
    echo("Unable to write generated mountain graphics data");
    exit(1);
}

echo("Wrote generated mountain graphics data to " . $outputFilename . "\n"); 
