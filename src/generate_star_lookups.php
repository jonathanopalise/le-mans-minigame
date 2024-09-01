<?php

if ($argc < 2) {
    echo("Usage: php generate_star_lookups.php [outputFile]\n");
    exit(1);
}       
        
$outputFilename = $argv[1];

$andValues = [
    0b0111111111111111,
    0b1011111111111111,
    0b1101111111111111,
    0b1110111111111111,
    0b1111011111111111,
    0b1111101111111111,
    0b1111110111111111,
    0b1111111011111111,
    0b1111111101111111,
    0b1111111110111111,
    0b1111111111011111,
    0b1111111111101111,
    0b1111111111110111,
    0b1111111111111011,
    0b1111111111111101,
    0b1111111111111110,
];

$orValues = [
    0b1000000000000000,
    0b0100000000000000,
    0b0010000000000000,
    0b0001000000000000,
    0b0000100000000000,
    0b0000010000000000,
    0b0000001000000000,
    0b0000000100000000,
    0b0000000010000000,
    0b0000000001000000,
    0b0000000000100000,
    0b0000000000010000,
    0b0000000000001000,
    0b0000000000000100,
    0b0000000000000010,
    0b0000000000000001,
];

$eraseValues = [];
$plotValues = [];

for ($backgroundColour = 0; $backgroundColour < 16; $backgroundColour++) {
    $backgroundWord1 = ($backgroundColour & 1) ? 0xffff : 0;
    $backgroundWord2 = ($backgroundColour >> 1 & 1) ? 0xffff: 0;
    $backgroundWord3 = ($backgroundColour >> 2 & 1) ? 0xffff: 0;
    $backgroundWord4 = ($backgroundColour >> 3 & 1) ? 0xffff: 0;

    $eraseValues[] = $backgroundWord1;
    $eraseValues[] = $backgroundWord2;
    $eraseValues[] = $backgroundWord3;
    $eraseValues[] = $backgroundWord4;

    for ($starOffset = 0; $starOffset < 16; $starOffset++) {
        $word1 = ($backgroundWord1 & $andValues[$starOffset]) | $orValues[$starOffset];
        $word2 = ($backgroundWord2 & $andValues[$starOffset]) | $orValues[$starOffset];
        $word3 = ($backgroundWord3 & $andValues[$starOffset]) | $orValues[$starOffset];
        $word4 = ($backgroundWord4 & $andValues[$starOffset]) | $orValues[$starOffset];

        //$word1 = $backgroundWord1;
        //$word2 = $backgroundWord2;
        //$word3 = $backgroundWord3;
        //$word4 = $backgroundWord4;

        $plotValues[] = $word1;
        $plotValues[] = $word2;
        $plotValues[] = $word3;
        $plotValues[] = $word4;
    }
}

$lines = [
    '#include "../star_lookups.h"',
    '',
    'uint16_t star_erase_values[] = {',
];

foreach ($eraseValues as $key => $eraseValue) {
    $line = '0x'.dechex($eraseValue);

    if ($key !== array_key_last($eraseValues)) {
        $line .= ',';
    }

    $lines[] = $line;
}

$lines = array_merge(
    $lines,
    [
        '};',
        '',
        'uint16_t star_plot_values[] = {',
    ]
);

foreach ($plotValues as $key => $plotValue) {
    $line = '0x'.dechex($plotValue);

    if ($key !== array_key_last($plotValues)) {
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
    echo("Unable to write generated star lookup data");
    exit(1);
}

echo("Wrote generated star lookup data to " . $outputFilename . "\n");
