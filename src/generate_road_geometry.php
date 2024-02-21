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

//echo("far visible scanline x vector: ".$farVisibleScanlineXVector."\n");
//echo("far visible scanline y vector: ".$farVisibleScanlineYVector."\n");
//echo("near visible scanline x vector: ".$nearVisibleScanlineXVector."\n");
//echo("near visible scanline y vector: ".$nearVisibleScanlineYVector."\n");

$width = 5;

$scanlines = [];

//$currentDegrees = FAR_VISIBLE_SCANLINE_ANGLE_DEGREES;
$unnormalisedSkewAddValuesMultiplier = 10;
for ($scanlineIndex = 0; $scanlineIndex < TOTAL_SCANLINE_COUNT; $scanlineIndex++) {
    $distanceAlongRoad = (ROAD_Y / $yVector) * $xVector;
    $distanceFromEye = sqrt(pow(ROAD_Y,2) + pow($distanceAlongRoad,2));
    //$width = 600000/$distanceAlongRoad;

    //echo(str_pad("degrees: ".$currentDegrees,25));
    //echo(str_pad("xvector: ".round($xVector,3),25));
    //echo(str_pad("yvector: ".round($yVector,3),25));
    //echo(str_pad("distanceAlongRoad: ".round($distanceAlongRoad,3),30));
    //echo(str_pad("distanceFromEye: ".round($distanceFromEye,3),30));
    //echo(str_pad("road width: ".round($width,3),25));
    //echo("\n");

    //$currentDegrees += DEGREE_CHANGE_PER_VISIBLE_SCANLINE;

    $xVector -= $scanlineXVectorAddQuantity;
    $yVector -= $scanlineYVectorAddQuantity;
    $width += 2;

    $unnormalisedSkewAddValues = [];
    for ($index = 0; $index < 256; $index++) {
        $unnormalisedSkewAddValues[] = $index * $unnormalisedSkewAddValuesMultiplier;
    }

    $scanlines[] = [
        'distanceAlongRoad' => $distanceAlongRoad,
        'unnormalisedSkewAddValues' => $unnormalisedSkewAddValues,
    ];

    $unnormalisedSkewAddValuesMultiplier+=2;
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
        '       .unnormalised_skew_add_values = {%s}',
        implode(', ', $scanline['unnormalisedSkewAddValues'])
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

$output = implode("\n", $lines);

$result = file_put_contents($outputFilename, $output);
if ($result === false) {
    echo("Unable to write generated road geometry data");
    exit(1);
}

echo("Wrote generated road geometry data to " . $outputFilename . "\n"); 
