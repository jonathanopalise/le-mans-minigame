<?php

if ($argc < 2) {
    echo("Usage: php generate_new_title_screen_graphics.php [slicePrefix] [outputFile]\n");
    exit(1);
}

$slicePrefix = $argv[1];
$outputFilename = $argv[2];

function generateSteNibble($value)
{
    $amigaNibble = ($value >> 4);
    return (($amigaNibble >> 1) | (($amigaNibble & 1) << 3));
}


include('library.php');
$outputWords = [];

for ($slice = 0; $slice <= 24; $slice++) {
    $indexedBitmap = IndexedBitmap::loadGif('assets/'.$slicePrefix.'-slice-'.$slice.'.gif');

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
for ($slice = 0; $slice <= 24 ; $slice++) {
    $image = imagecreatefromgif('assets/'.$slicePrefix.'-slice-'. $slice . '.gif');

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

    /*echo("slice ".$slice.": ".$slice."\n");
    foreach ($imagePaletteWords as $word) {
        echo(dechex($word)." ");
    }
    echo("\n");*/

    $outputWords = array_merge($outputWords, $imagePaletteWords);
}


$outputBin = '';
foreach ($outputWords as $outputWord) {
    $outputBin .= chr($outputWord >> 8);
    $outputBin .= chr($outputWord & 255);
}

echo("title screen binary length is ".strlen($outputBin). "\n");

$result = file_put_contents($outputFilename, $outputBin);
if ($result === false) {
    echo("Unable to write generated new title screen graphics data with prefix ".$slicePrefix." to ".$outputFilename);
    exit(1);
}

echo("Wrote generated new title screen graphics data with prefix ".$slicePrefix." to ".$outputFilename."\n"); 
