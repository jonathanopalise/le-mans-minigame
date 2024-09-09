<?php

if ($argc < 2) {
    echo("Usage: php generate_time_of_day.php [outputFile]\n");
    exit(1);
}       

$outputFilename = $argv[1];

function generateSteNibble($value)
{
    $amigaNibble = ($value >> 4);
    return (($amigaNibble >> 1) | (($amigaNibble & 1) << 3));
}

function generateStePaletteWord($red, $green, $blue)
{
    $steRed = generateSteNibble($red);
    $steGreen = generateSteNibble($green);
    $steBlue = generateSteNibble($blue);

    return ($steRed << 8) | ($steGreen << 4) | ($steBlue);
}

const SKY_TOP = 'SKY_TOP';
const SKY_BOTTOM = 'SKY_BOTTOM';

const RED = 0;
const GREEN = 1;
const BLUE = 2;

const AM_0 = [
    SKY_TOP => [0,0,0],
    SKY_BOTTOM => [0,0,0],
];

const AM_4 = [
    SKY_TOP => [61, 65, 69],
    SKY_BOTTOM => [133,112,54],
];

const AM_6 = [
    SKY_TOP => [56,75,120],
    SKY_BOTTOM => [186,229,118],
];

const AM_8 = [
    SKY_TOP => [56,83,153],
    SKY_BOTTOM => [250,236,239],
];

const PM_4 = [
    SKY_TOP => [87,102,127],
    SKY_BOTTOM => [255,250,76],
];

const PM_6 = [
    SKY_TOP => [36,38,35],
    SKY_BOTTOM => [142,106,32],
];

const PM_8 = [
    SKY_TOP => [25,25,15],
    SKY_BOTTOM => [47,38,11],
];

const MOUNTAIN_COLOURS = [
    [96,64,32],
    [64,0,0],
];

const SCENERY_COLOURS = [
    [36, 109, 109],
    [109, 182, 145],
    [182, 182, 218],
    [182, 0, 0],
    [109, 109, 109],
    [218, 255, 255],
    [2, 0, 175],
    [255, 218, 0],
    [151, 91, 37],
    [42, 124, 192],
];

const GROUND_COLOURS = [
    [100, 111, 54],
    [255, 255, 255],
    [64, 64, 64],
];

$timeOfDayColours = array_fill(0, 96, null);
$timeOfDayColours[4] = PM_4;
$timeOfDayColours[12] = PM_6;
$timeOfDayColours[16] = PM_8;
$timeOfDayColours[32] = AM_0;
$timeOfDayColours[48] = AM_4;
$timeOfDayColours[56] = AM_6;
$timeOfDayColours[64] = AM_8;

foreach ($timeOfDayColours as $key => $value) {
    if (is_null($value)) {
        // we need to lerp to create the rgb values
        $startLerpKey = $key - 1;
        if ($startLerpKey < 0) {
            $startLerpKey = count($timeOfDayColours) - 1;
        }
        while (is_null($timeOfDayColours[$startLerpKey])) {
            $startLerpKey--;
            if ($startLerpKey < 0) {
                $startLerpKey = count($timeOfDayColours) - 1;
            }
        }
        $endLerpKey = $key +1;
        if ($endLerpKey > count($timeOfDayColours) - 1) {
            $endLerpKey = 0;
        }
        while (is_null($timeOfDayColours[$endLerpKey])) {
            $endLerpKey++;
            if ($endLerpKey > count($timeOfDayColours) - 1) {
                $endLerpKey = 0;
            }
        }

        $incrementLerpFraction = true;
        $lerpSize = 0;
        $lerpFraction = 0;
        $currentKey = $startLerpKey;
        while ($currentKey != $endLerpKey) {
            if ($currentKey == $key) {
                $incrementLerpFraction = false;
            }

            if ($incrementLerpFraction) {
                $lerpFraction++;
            }
            $lerpSize++;

            $currentKey++;
            if ($currentKey > count($timeOfDayColours) - 1) {
                $currentKey = 0;
            }
        }

        //echo("LERP at ".$key.":\n");
        //echo("  start: ".$startLerpKey."\n");
        //echo("  end: ".$endLerpKey."\n");
        //echo("  lerp size: ".$lerpSize."\n");
        //echo("  lerp fraction: ".$lerpFraction."\n");

        // top

        $topRedStart = $timeOfDayColours[$startLerpKey][SKY_TOP][RED];
        $topRedEnd = $timeOfDayColours[$endLerpKey][SKY_TOP][RED];

        $topGreenStart = $timeOfDayColours[$startLerpKey][SKY_TOP][GREEN];
        $topGreenEnd = $timeOfDayColours[$endLerpKey][SKY_TOP][GREEN];

        $topBlueStart = $timeOfDayColours[$startLerpKey][SKY_TOP][BLUE];
        $topBlueEnd = $timeOfDayColours[$endLerpKey][SKY_TOP][BLUE];

        $topRedDifference = $topRedEnd - $topRedStart;
        $topGreenDifference = $topGreenEnd - $topGreenStart;
        $topBlueDifference = $topBlueEnd - $topBlueStart;

        $topRed = intval(round($topRedStart + ($topRedEnd - $topRedStart) * $lerpFraction / $lerpSize));
        $topGreen = intval(round($topGreenStart + ($topGreenEnd - $topGreenStart) * $lerpFraction / $lerpSize));
        $topBlue = intval(round($topBlueStart + ($topBlueEnd - $topBlueStart) * $lerpFraction / $lerpSize));

        // bottom

        $bottomRedStart = $timeOfDayColours[$startLerpKey][SKY_BOTTOM][RED];
        $bottomRedEnd = $timeOfDayColours[$endLerpKey][SKY_BOTTOM][RED];

        $bottomGreenStart = $timeOfDayColours[$startLerpKey][SKY_BOTTOM][GREEN];
        $bottomGreenEnd = $timeOfDayColours[$endLerpKey][SKY_BOTTOM][GREEN];

        $bottomBlueStart = $timeOfDayColours[$startLerpKey][SKY_BOTTOM][BLUE];
        $bottomBlueEnd = $timeOfDayColours[$endLerpKey][SKY_BOTTOM][BLUE];

        $bottomRedDifference = $bottomRedEnd - $bottomRedStart;
        $bottomGreenDifference = $bottomGreenEnd - $bottomGreenStart;
        $bottomBlueDifference = $bottomBlueEnd - $bottomBlueStart;

        $bottomRed = intval(round($bottomRedStart + ($bottomRedEnd - $bottomRedStart) * $lerpFraction / $lerpSize));
        $bottomGreen = intval(round($bottomGreenStart + ($bottomGreenEnd - $bottomGreenStart) * $lerpFraction / $lerpSize));
        $bottomBlue = intval(round($bottomBlueStart + ($bottomBlueEnd - $bottomBlueStart) * $lerpFraction / $lerpSize));

        //echo("  top: ".$topRed.",".$topGreen.",".$topBlue."\n");
        //echo("  bottom: ".$bottomRed.",".$bottomGreen.",".$bottomBlue."\n");

        $timeOfDayColours[$key][SKY_TOP] = [$topRed, $topGreen, $topBlue];
        $timeOfDayColours[$key][SKY_BOTTOM] = [$bottomRed, $bottomGreen, $bottomBlue];

    } else {
        //$topRed = $value[SKY_TOP][0];
        //$topGreen = $value[SKY_TOP][1];
        //$topBlue = $value[SKY_TOP][2];
        //$bottomRed = $value[SKY_BOTTOM][0];
        //$bottomGreen = $value[SKY_BOTTOM][1];
        //$bottomBlue = $value[SKY_BOTTOM][2];

        //echo("KEYFRAME at ".$key."\n");
        //echo("  top: ".$topRed.",".$topGreen.",".$topBlue."\n");
        //echo("  bottom: ".$bottomRed.",".$bottomGreen.",".$bottomBlue."\n");
    }
}

foreach ($timeOfDayColours as $key => $timeOfDayColour) {
    $topRed = $timeOfDayColour[SKY_TOP][0];
    $topGreen = $timeOfDayColour[SKY_TOP][1];
    $topBlue = $timeOfDayColour[SKY_TOP][2];
    $bottomRed = $timeOfDayColour[SKY_BOTTOM][0];
    $bottomGreen = $timeOfDayColour[SKY_BOTTOM][1];
    $bottomBlue = $timeOfDayColour[SKY_BOTTOM][2];

    $skyGradientColours = [];

    // stars
    /*if ($key > 8 && $key < 54) {
        $tailLightsRed = 255;
        $tailLightsGreen = 255;
        $tailLightsBlue = 0;
    }*/

    //$skyGradientColours[] = [$starsRed, $starsGreen, $starsBlue];

    for ($gradientIndex = 0; $gradientIndex < 13; $gradientIndex++) {
        $red = intval(round($topRed + (($bottomRed - $topRed) * $gradientIndex / 12)));
        $green = intval(round($topGreen + (($bottomGreen - $topGreen) * $gradientIndex / 12)));
        $blue = intval(round($topBlue + (($bottomBlue - $topBlue) * $gradientIndex / 12)));

        if ($blue < 32) {
            $blue = 32;
        }

        $skyGradientColours[] = [$red, $green, $blue];
    }

    $timeOfDayColours[$key]['skyGradientColours'] = array_reverse($skyGradientColours);


    //$skyTopRed = $timeOfDayColour[SKY_TOP][RED];
    //$skyTopGreen = $timeOfDayColour[SKY_TOP][GREEN];
    //$skyTopBlue = $timeOfDayColour[SKY_TOP][BLUE];

    $skyBottomRed = $timeOfDayColour[SKY_BOTTOM][RED];
    $skyBottomGreen = $timeOfDayColour[SKY_BOTTOM][GREEN];
    $skyBottomBlue = $timeOfDayColour[SKY_BOTTOM][BLUE];

    //$skyAverageRed = intval(round($skyTopRed + (($skyBottomRed - $skyTopRed) / 2)));
    //$skyAverageGreen = intval(round($skyTopGreen + (($skyBottomGreen - $skyTopGreen) / 2)));
    //$skyAverageBlue = intval(round($skyTopBlue + (($skyBottomBlue - $skyTopBlue) / 2)));

    $adjustedMountainColours = [];
    foreach (MOUNTAIN_COLOURS as $mountainColour) {
        $naturalMountainRed = $mountainColour[RED];
        $naturalMountainGreen = $mountainColour[GREEN];
        $naturalMountainBlue = $mountainColour[BLUE];

        $adjustedMountainRed = intval(round($naturalMountainRed * $skyBottomRed / 255));
        $adjustedMountainGreen = intval(round($naturalMountainGreen * $skyBottomGreen / 255));
        $adjustedMountainBlue = intval(round($naturalMountainBlue * $skyBottomBlue / 255));
        if ($adjustedMountainBlue < 32) {
            $adjustedMountainBlue = 32;
        }

        $adjustedMountainColours[] = [$adjustedMountainRed, $adjustedMountainGreen, $adjustedMountainBlue];
    }

    $timeOfDayColours[$key]['adjustedMountainColours'] = $adjustedMountainColours;

    $adjustedSceneryColours = [];
    foreach (SCENERY_COLOURS as $sceneryColour) {
        $naturalSceneryRed = $sceneryColour[RED];
        $naturalSceneryGreen = $sceneryColour[GREEN];
        $naturalSceneryBlue = $sceneryColour[BLUE];

        $normalisedSkyBottomRed = $skyBottomRed < 112 ? 112 : $skyBottomRed;
        $normalisedSkyBottomGreen = $skyBottomGreen < 112 ? 112 : $skyBottomGreen;
        $normalisedSkyBottomBlue = $skyBottomBlue < 112 ? 112 : $skyBottomBlue;

        $adjustedSceneryRed = intval(round($naturalSceneryRed * $normalisedSkyBottomRed / 255));
        $adjustedSceneryGreen = intval(round($naturalSceneryGreen * $normalisedSkyBottomGreen / 255));
        $adjustedSceneryBlue = intval(round($naturalSceneryBlue * $normalisedSkyBottomBlue / 255));

        $adjustedSceneryColours[] = [$adjustedSceneryRed, $adjustedSceneryGreen, $adjustedSceneryBlue];
    }

    // tail lights switch on and off depending upon time of day
    $tailLightsRed = 118;
    $tailLightsGreen = 33;
    $tailLightsBlue = 33;
    if ($key > 8 && $key < 54) {
        $tailLightsRed = 255;
        $tailLightsGreen = 0;
        $tailLightsBlue = 0;
    }

    $adjustedSceneryColours[] = [$tailLightsRed, $tailLightsGreen, $tailLightsBlue];

    $timeOfDayColours[$key]['adjustedSceneryColours'] = $adjustedSceneryColours;

    $adjustedGroundColours = [];
    foreach (GROUND_COLOURS as $groundColourKey => $groundColour) {
        $naturalGroundRed = $groundColour[RED];
        $naturalGroundGreen = $groundColour[GREEN];
        $naturalGroundBlue = $groundColour[BLUE];

        $minimumValue = 128;
        if ($groundColourKey == array_key_first(GROUND_COLOURS)) {
            $minimumValue = 64;
        }

        $normalisedSkyBottomRed = $skyBottomRed < $minimumValue ? $minimumValue : $skyBottomRed;
        $normalisedSkyBottomGreen = $skyBottomGreen < $minimumValue ? $minimumValue : $skyBottomGreen;
        $normalisedSkyBottomBlue = $skyBottomBlue < $minimumValue ? $minimumValue : $skyBottomBlue;

        $adjustedGroundRed = intval(round($naturalGroundRed * $normalisedSkyBottomRed / 255));
        $adjustedGroundGreen = intval(round($naturalGroundGreen * $normalisedSkyBottomGreen / 255));
        $adjustedGroundBlue = intval(round($naturalGroundBlue * $normalisedSkyBottomBlue / 255));

        $adjustedGroundColours[] = [$adjustedGroundRed, $adjustedGroundGreen, $adjustedGroundBlue];
    }

    $timeOfDayColours[$key]['adjustedGroundColours'] = $adjustedGroundColours;
}

$outputWords = [];
foreach ($timeOfDayColours as $key => $timeOfDayColour) {
    // sky gradient

    foreach ($timeOfDayColour['skyGradientColours'] as $rgbArray) {
        $red = $rgbArray[RED];
        $green = $rgbArray[GREEN];
        $blue = $rgbArray[BLUE];

        $outputWords[] = generateStePaletteWord($red, $green, $blue);
    }

    // stars
    $starsRed = 255;
    $starsGreen = 255;
    $starsBlue = 255;
    switch ($key) {
        case 22:
        case 35:
            $starsRed = 64;
            $starsGreen = 64;
            $starsBlue = 64;
            break;
        case 23:
        case 34:
            $starsRed = 128;
            $starsGreen = 128;
            $starsBlue = 128;
            break;
        case 24:
        case 33:
            $starsRed = 192;
            $starsGreen = 192;
            $starsBlue = 192;
            break;
    }

    $outputWords[] = generateStePaletteWord($starsRed, $starsGreen, $starsBlue);

    // mountain colours
    foreach ($timeOfDayColour['adjustedMountainColours'] as $rgbArray) {
        $red = $rgbArray[RED];
        $green = $rgbArray[GREEN];
        $blue = $rgbArray[BLUE];

        $outputWords[] = generateStePaletteWord($red, $green, $blue);
    }

    foreach ($timeOfDayColour['adjustedSceneryColours'] as $rgbArray) {
        $red = $rgbArray[RED];
        $green = $rgbArray[GREEN];
        $blue = $rgbArray[BLUE];

        $outputWords[] = generateStePaletteWord($red, $green, $blue);
    }

    foreach ($timeOfDayColour['adjustedGroundColours'] as $rgbArray) {
        $red = $rgbArray[RED];
        $green = $rgbArray[GREEN];
        $blue = $rgbArray[BLUE];

        $outputWords[] = generateStePaletteWord($red, $green, $blue);
    }
}

$lines = [
    '#include "../time_of_day.h"',
    '#include <inttypes.h>',
    '',
    'uint16_t time_of_day[] = {',
];

foreach ($outputWords as $key => $outputWord) {
    $line = '0x'.(string)dechex($outputWord);

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
    echo("Unable to write time of day data");
    exit(1);
}

echo("Wrote generated time of day data to " . $outputFilename . "\n");

