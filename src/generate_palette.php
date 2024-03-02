<?php

if ($argc < 2) {
    echo("usage: generate_palette [inputFile]");
    exit(1);
}

function generateSteNibble($value)
{
    $amigaNibble = ($value >> 4);
    return (($amigaNibble >> 1) | (($amigaNibble & 1) << 3));
}

$image = imagecreatefromgif($argv[1]);
if ($image === false) {
    echo("unable to open palette\n");
    exit(1);
}


$stePalette = [];
for ($index = 0; $index < 16; $index++) {
    $colours = imagecolorsforindex($image, $index);

    $red = $colours['red'];
    $green = $colours['green'];
    $blue = $colours['blue'];

    $steRed = generateSteNibble($red);
    $steGreen = generateSteNibble($green);
    $steBlue = generateSteNibble($blue);

    $stePalette[] = ($steRed << 8) | ($steGreen << 4) | ($steBlue);
}

$identifier = '_palette';

$lines = [
    '    public ' . $identifier,
    '',
    $identifier . ':'
];

foreach ($stePalette as $entry) {
    $lines[] = '    dc.w $' . dechex($entry);
}

$contents = implode("\n", $lines);

echo($contents."\n");
