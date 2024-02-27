<?php

/*

- near_visible_scanline_angle_degrees
- far_visible_scanline_angle_degrees
- visible_scanline_count
- supplemental_scanline_count
- road_y_position
- curve_start_angle (the angle at which curves start to be applied)

*/

include('library.php');

if ($argc < 2) {
    echo("Usage: php generate_road_geometry.php [outputFile]\n");
    exit(1);
}       
        
$outputFilename = $argv[1];

const NEAR_VISIBLE_SCANLINE_ANGLE_DEGREES = 25;
const FAR_VISIBLE_SCANLINE_ANGLE_DEGREES = 1;
const VISIBLE_SCANLINE_COUNT = 80;
const SUPPLEMENTAL_SCANLINE_COUNT = 20;
const DEGREE_CHANGE_PER_VISIBLE_SCANLINE = (NEAR_VISIBLE_SCANLINE_ANGLE_DEGREES - FAR_VISIBLE_SCANLINE_ANGLE_DEGREES) / (VISIBLE_SCANLINE_COUNT-1);
const TOTAL_SCANLINE_COUNT = VISIBLE_SCANLINE_COUNT + SUPPLEMENTAL_SCANLINE_COUNT;
const ROAD_Y = 1000;
const PLAYER_CAR_SCANLINE = 75;

//echo("degree change per visible scanline: ".DEGREE_CHANGE_PER_VISIBLE_SCANLINE."\n");


$radians = deg2rad(FAR_VISIBLE_SCANLINE_ANGLE_DEGREES);
$farVisibleScanlineXVector = cos($radians);
$farVisibleScanlineYVector = sin($radians);

$radians = deg2rad(NEAR_VISIBLE_SCANLINE_ANGLE_DEGREES);
$nearVisibleScanlineXVector = cos($radians);
$nearVisibleScanlineYVector = sin($radians);

$scanlineXVectorAddQuantity = ($farVisibleScanlineXVector - $nearVisibleScanlineXVector) / VISIBLE_SCANLINE_COUNT;
$scanlineYVectorAddQuantity = ($farVisibleScanlineYVector - $nearVisibleScanlineYVector) / VISIBLE_SCANLINE_COUNT;

$xVector = $farVisibleScanlineXVector;
$yVector = $farVisibleScanlineYVector;

$scanlines = [];

$unnormalisedSkewAddValuesMultiplier = 10;
for ($scanlineIndex = 0; $scanlineIndex < TOTAL_SCANLINE_COUNT; $scanlineIndex++) {
    $distanceAlongRoad = (ROAD_Y / $yVector) * $xVector;
    $xVector -= $scanlineXVectorAddQuantity;
    $yVector -= $scanlineYVectorAddQuantity;

    // note the magic number to make sure that normalisedSkewAddValues[1] on the player car scanline is exactly 65536
    $unnormalisedSkewAddValues = [];
    for ($index = 0; $index < 256; $index++) {
        $unnormalisedSkewAddValues[] = intval((floatval($index) * 211.408) * $unnormalisedSkewAddValuesMultiplier);
    }

    $scanlines[] = [
        'distanceAlongRoad' => $distanceAlongRoad,
        'unnormalisedSkewAddValues' => $unnormalisedSkewAddValues,
    ];

    $unnormalisedSkewAddValuesMultiplier+=4;
}

$cornerValue = 0;
$cornerAddValue = 0;
// start from player scanline and go up
for ($scanlineIndex = PLAYER_CAR_SCANLINE; $scanlineIndex >= 0; $scanlineIndex--) {
    $unnormalisedCornerAddValues = [];
    for ($index = 0; $index < 256; $index++) {
        $unnormalisedCornerAddValues[] = $cornerValue * $index;
    }
    $scanlines[$scanlineIndex]['unnormalisedCornerAddValues'] = $unnormalisedCornerAddValues;

    $cornerValue += $cornerAddValue;
    $cornerAddValue += 10;
}

// start from scanline below player scanline and go down
$sourceScanlineIndex = PLAYER_CAR_SCANLINE - 1;
for ($scanlineIndex = PLAYER_CAR_SCANLINE + 1; $scanlineIndex < count($scanlines); $scanlineIndex++) {
    $scanlines[$scanlineIndex]['unnormalisedCornerAddValues'] = $scanlines[$sourceScanlineIndex]['unnormalisedCornerAddValues'];
    $sourceScanlineIndex--;
}

// should probably fail build here if value is not 65536
echo("unnormalisedSkewAddValue[1] on player scanline: ".$scanlines[PLAYER_CAR_SCANLINE]['unnormalisedSkewAddValues'][1]."\n");

$distanceToScanlineLookup = [];
for ($distanceAlongRoad = 0; $distanceAlongRoad < 65536; $distanceAlongRoad++) {
    // start from nearest scanline
    $lookupValue = -1;

    for ($scanlineIndex = 0; $scanlineIndex < TOTAL_SCANLINE_COUNT - 2; $scanlineIndex++) {
        $furtherScanlineDistance = intval($scanlines[$scanlineIndex]['distanceAlongRoad']);
        $nearerScanlineDistance = intval($scanlines[$scanlineIndex + 1]['distanceAlongRoad']);

        if (($distanceAlongRoad >= $nearerScanlineDistance) && ($distanceAlongRoad < $furtherScanlineDistance)) {
            $lookupValue = $scanlineIndex;
            break;
        }

    }

    $distanceToScanlineLookup[] = $lookupValue;
}

$lines = [
    '#include "../road_geometry.h"',
    '#include <inttypes.h>',
    '',
    'struct RoadScanline road_scanlines[] = (struct RoadScanline[]) {',
];

foreach ($scanlines as $key => $scanline) {
    $lines[] = '    {';

    $lines[] = sprintf(
        '       .distance_along_road = %d,',
        $scanline['distanceAlongRoad']
    );

    $lines[] = sprintf(
        '       .logical_xpos_add_values = {%s},',
        implode(', ', $scanline['unnormalisedSkewAddValues'])
    );

    $lines[] = sprintf(
        '       .logical_xpos_corner_add_values = {%s}',
        implode(', ', $scanline['unnormalisedCornerAddValues'])
    );

    $line = '    }';
    if ($key !== array_key_last($scanlines)) {
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

$lines = array_merge(
    $lines,
    [
        '',
        'int16_t distance_to_scanline_lookup[] = {',
    ]
);

foreach ($distanceToScanlineLookup as $key => $item) {
    $line = '    '.$item;
    if ($key !== array_key_last($distanceToScanlineLookup)) {
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
    echo("Unable to write generated road geometry data");
    exit(1);
}

echo("Wrote generated road geometry data to " . $outputFilename . "\n"); 
