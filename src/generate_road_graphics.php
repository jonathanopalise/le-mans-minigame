<?php

include('library.php');

if ($argc < 2) {
    echo("Usage: php generate_road_graphics.php [outputFile]\n");
    exit(1);
}       
        
$outputFilename = $argv[1];


const COLOUR_WHITE = 2;           // 0110 -> 0110
const COLOUR_LIGHT_ASPHALT = 3;  // 1101 -> 1101
const COLOUR_GRASS_1 = 1;        // 1100 -> 1000

$padding = 22;

$byteOffsets = [];
$outputWords = [];
$outputWordLines = [];

$roadLinesColour = COLOUR_WHITE;
$asphaltColour = COLOUR_LIGHT_ASPHALT;
$grassColour = COLOUR_GRASS_1;

$actualPixelWidth = 10;
for ($index = 0; $index < 80; $index++) {
    $roundedPixelWidth = (($actualPixelWidth - 1) & 0xffe0) + 32 + ($padding * 16);

    $textureStep = 1.0 / (float)$actualPixelWidth;
    $texturePosition = 0;
    $midpointTexturePosition = $textureStep * ($roundedPixelWidth / 2);

    $roadMultiplier = 0.75;

    $leftRumbleStripLeft = ($midpointTexturePosition + 0.46  * $roadMultiplier);
    $leftRumbleStripRight = ($midpointTexturePosition + 0.54 * $roadMultiplier);

    $rightRumbleStripLeft = ($midpointTexturePosition - 0.54 * $roadMultiplier);
    $rightRumbleStripRight = ($midpointTexturePosition - 0.46 * $roadMultiplier);


    $whiteLine1Left = ($midpointTexturePosition + 0.42 * $roadMultiplier);
    $whiteLine1Right = ($midpointTexturePosition + 0.44 * $roadMultiplier);

    $whiteLine2Left = ($midpointTexturePosition + 0.46 * $roadMultiplier);
    $whiteLine2Right = ($midpointTexturePosition + 0.48 * $roadMultiplier);

    $whiteLine3Left = ($midpointTexturePosition - 0.44 * $roadMultiplier);
    $whiteLine3Right = ($midpointTexturePosition - 0.42 * $roadMultiplier);

    $whiteLine6Left = ($midpointTexturePosition - 0.01 * $roadMultiplier);
    $whiteLine6Right = ($midpointTexturePosition + 0.01 * $roadMultiplier);

    $asphaltLeft = ($midpointTexturePosition - 0.5 * $roadMultiplier);
    $asphaltRight = ($midpointTexturePosition + 0.5 * $roadMultiplier);

    $indexedBitmap = IndexedBitmap::create($roundedPixelWidth, 1);

    for ($xpos = 0; $xpos < $roundedPixelWidth; $xpos++) {
        if (($texturePosition > $leftRumbleStripLeft) && ($texturePosition < $leftRumbleStripRight)) {
            $pixelColour = $roadLinesColour; // left rumble strip
        } elseif (($texturePosition > $rightRumbleStripLeft) && ($texturePosition < $rightRumbleStripRight)) {
            $pixelColour = $roadLinesColour; // right rumble strip
        } elseif (($texturePosition > $whiteLine1Left) && ($texturePosition < $whiteLine1Right)) {
            $pixelColour = $roadLinesColour;
        } elseif (($texturePosition > $whiteLine2Left) && ($texturePosition < $whiteLine2Right)) {
            $pixelColour = $roadLinesColour;
        } elseif (($texturePosition > $whiteLine3Left) && ($texturePosition < $whiteLine3Right)) {
           $pixelColour = $roadLinesColour;
        } elseif (($texturePosition > $whiteLine6Left) && ($texturePosition < $whiteLine6Right)) {
            $pixelColour = $roadLinesColour;
        } elseif (($texturePosition > $asphaltLeft) && ($texturePosition < $asphaltRight)) {
            $pixelColour = $asphaltColour;
        } else {
            $pixelColour = $grassColour;
        }

        $indexedBitmap->putPixel($xpos, 0, $pixelColour);
        $texturePosition += $textureStep;
    }

    $indexedBitmapFirstLine = $indexedBitmap->getLineAt(0);
    $bitplane0Words = $indexedBitmapFirstLine->toBitplaneWordSequence(0);
    $bitplane1Words = $indexedBitmapFirstLine->toBitplaneWordSequence(1);
    
    $bitplaneMergedWords = [];
    for ($wordIndex = 0; $wordIndex < $bitplane0Words->getLength(); $wordIndex++) {
        $bitplaneMergedWords[] = $bitplane0Words->getWordAt($wordIndex);
        $bitplaneMergedWords[] = $bitplane1Words->getWordAt($wordIndex);
    }

    // rounded pixel width: 160
    // bytes width: 80 (4 pixels per byte)
    // does count($outputBytes) need to be multiplied by 4? (long words)
    // start with offset
    // move to centre of graphics data
    // move 160 pixels back

    $byteOffsets[] = ((count($outputWords) * 2) + ($roundedPixelWidth / 8)) - (160/4);

    $outputWords = array_merge($outputWords, $bitplaneMergedWords);
    $outputWordLines[] = $bitplaneMergedWords;

    $actualPixelWidth+=4;
}

$lines = [
    '#include "../road_graphics.h"',
    '#include <inttypes.h>',
    '',
    'uint32_t byte_offsets[] = {',
];


foreach ($byteOffsets as $key => $byteOffset) {
    $line = (string)$byteOffset;

    if ($key !== array_key_last($byteOffsets)) {
        $line .= ',';
    }

    $lines[] = $line;
}

$lines = array_merge(
    $lines,
    [
        '};',
        '',
        'uint16_t gfx_data[] = {',
    ]
);

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
    echo("Unable to write generated road graphics data");
    exit(1);
}

echo("Wrote generated road graphics data to " . $outputFilename . "\n"); 
