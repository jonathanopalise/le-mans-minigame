<?php

if ($argc < 2) {
    echo("Usage: php generate_credits_screen_data.php [inputFile] [outputFile]\n");
    exit(1);
}

$inputFilename = $argv[1];
$outputFilename = $argv[2];

function generateSteNibble($value)
{
    $amigaNibble = ($value >> 4);
    return (($amigaNibble >> 1) | (($amigaNibble & 1) << 3));
}

include('library.php');
$bitmapWords = [];
$paletteWords = [];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);

for ($lineIndex = 0; $lineIndex < $indexedBitmap->getHeight(); $lineIndex++) {
    $indexedBitmapLine = $indexedBitmap->getLineAt($lineIndex);
    $bitplane0Words = $indexedBitmapLine->toBitplaneWordSequence(0);
    $bitplane1Words = $indexedBitmapLine->toBitplaneWordSequence(1);
    $bitplane2Words = $indexedBitmapLine->toBitplaneWordSequence(2);
    $bitplane3Words = $indexedBitmapLine->toBitplaneWordSequence(3);

    $bitplaneMergedWords = [];
    for ($wordIndex = 0; $wordIndex < $bitplane0Words->getLength(); $wordIndex++) {
        $outputWords[] = $bitplane0Words->getWordAt($wordIndex);
        $outputWords[] = $bitplane1Words->getWordAt($wordIndex);
        $outputWords[] = $bitplane2Words->getWordAt($wordIndex);
        $outputWords[] = $bitplane3Words->getWordAt($wordIndex);
    }
}

$paletteWords = [];
$image = imagecreatefromgif($inputFilename);
$colourCount = imagecolorstotal($image);

for ($index = 0; $index < $colourCount; $index++) {
    $colours = imagecolorsforindex($image, $index);

    $red = $colours['red'];
    $green = $colours['green'];
    $blue = $colours['blue'];

    $steRed = generateSteNibble($red);
    $steGreen = generateSteNibble($green);
    $steBlue = generateSteNibble($blue);

    $paletteWords[] = ($steRed << 8) | ($steGreen << 4) | ($steBlue);
}


$lines = [
    '#include "../credits_screen_data.h"',
    '#include <inttypes.h>',      
    '',
    'uint16_t credits_screen_bitmap[] = {',
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
        '',
        'uint16_t credits_screen_palette[] = {',
    ]
);

foreach ($paletteWords as $key => $paletteWord) {
    $line = (string)$paletteWord;
    
    if ($key !== array_key_last($paletteWords)) {
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
    echo("Unable to write credits screen data");
    exit(1);
}   

echo("Wrote credits screen data data to ".$outputFilename."\n"); 
