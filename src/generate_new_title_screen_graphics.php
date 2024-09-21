<?php

function generateSteNibble($value)
{
    $amigaNibble = ($value >> 4);
    return (($amigaNibble >> 1) | (($amigaNibble & 1) << 3));
}


include('library.php');
$outputWords = [];

for ($slice = 0; $slice <= 7 ; $slice++) {
    $indexedBitmap = IndexedBitmap::loadGif('assets/slice-'.$slice.'.gif');


    for ($lineIndex = 0; $lineIndex < $indexedBitmap->getHeight(); $lineIndex++) {
        $indexedBitmapLine = $indexedBitmap->getLineAt($lineIndex);
        $bitplane0Words = $indexedBitmapLine->toBitplaneWordSequence(0);
        $bitplane1Words = $indexedBitmapLine->toBitplaneWordSequence(1);
        $bitplane2Words = $indexedBitmapLine->toBitplaneWordSequence(2);
        $bitplane3Words = $indexedBitmapLine->toBitplaneWordSequence(3);

        $bitplaneMergedWords = [];
        for ($wordIndex = 0; $wordIndex < $bitplane0Words->getLength(); $wordIndex++) {
            $bitplaneMergedWords[] = $bitplane0Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane1Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane2Words->getWordAt($wordIndex);
            $bitplaneMergedWords[] = $bitplane3Words->getWordAt($wordIndex);
        }

        $outputWords = array_merge($outputWords, $bitplaneMergedWords);
    }
}

while (count($outputWords) < 16000) {
    $outputWords[] = 0;
}

$outputPaletteWords = [];
for ($slice = 0; $slice <= 7 ; $slice++) {
    $image = imagecreatefromgif('assets/slice-'. $slice . '.gif');

    $imagePaletteWords = [];
    $colourCount = imagecolorstotal($image);

    for ($index = 0; $index < $colourCount; $index++) {
        $colours = imagecolorsforindex($image, $index);

        $red = $colours['red'];
        $green = $colours['green'];
        $blue = $colours['blue'];

        $steRed = generateSteNibble($red);
        $steGreen = generateSteNibble($green);
        $steBlue = generateSteNibble($blue);

        $imagePaletteWords[] = ($steRed << 8) | ($steGreen << 4) | ($steBlue);
    }

    while (count($imagePaletteWords) < 16) {
        $imagePaletteWords[] = 0;
    }

    var_dump($imagePaletteWords);

    $outputWords = array_merge($outputWords, $imagePaletteWords);
}

$lines = [
    '#include "../new_title_screen_graphics.h"',
    '#include <inttypes.h>',
    '',
    'uint16_t new_title_screen_graphics[] = {',
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

$result = file_put_contents('src/generated/new_title_screen_graphics.c', $output);
if ($result === false) {
    echo("Unable to write generated new title screen graphics data");
    exit(1);
}

echo("Wrote generated new title screen graphics data to src/generated/new_title_screen_graphics.c\n"); 
