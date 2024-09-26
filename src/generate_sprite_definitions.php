<?php

require_once 'library.php';
require_once 'sprite_spans.php';

include 'sprite_definitions.php';

function convertStringToByteArray()
{
}

if ($argc < 3) {
    echo("Usage: php generate_sprite_definitions.php [inputFile] [outputFile]\n");
    exit(1);
}

$inputFilename = $argv[1];
$definitionsOutputFilename = $argv[2];

if (!file_exists($inputFilename)) {
    echo("Input filename does not exist\n");
    exit(1);
}


$indexedBitmap = IndexedBitmap::loadGif($inputFilename);
$exportedSprites = [];

foreach ($definitions as $definition) {

    if (isset($definition['originx'])) {
        $originX = $definition['originx'];
    } else {
        $originX = intval($definition['width'] / 2);
    }

    if (isset($definition['originy'])) {
        $originY = $definition['originy'];
    } else {
        $originY = $definition['height'] - 1;
    }

    $croppedIndexedBitmap = $indexedBitmap->extractRegionToIndexedBitmap(
        $definition['left'],
        $definition['top'],
        $definition['width'],
        $definition['height'],
        $originX,
        $originY
    )->getCopyRoundedTo16PixelDivisibleWidth();

    if (isset($definition['includeMaskWords'])) {
        $includeMaskWords = $definition['includeMaskWords'];
    } else {
        $includeMaskWords = true;
    }

    if (isset($definition['bytesPerDestLine'])) {
        $bytesPerDestLine = $definition['bytesPerDestLine'];
    } else {
        $bytesPerDestLine = 160;
    }

    $maskedSprite = SpriteConvertor::createMaskedSprite($croppedIndexedBitmap);
    $planarDataWords = $maskedSprite->exportToWords(true, $includeMaskWords);

    $exportedSprite = [
        'origin_x' => $croppedIndexedBitmap->getOriginX(),
        'origin_y' => $croppedIndexedBitmap->getOriginY(),
        'source_data_width' => $maskedSprite->getWidth(),
        'source_data_height' => $maskedSprite->getHeight(),
        'longest_right_end' => $maskedSprite->getLongestRightEnd(),
        'words' => $planarDataWords,
    ];

    for ($skew = 0; $skew < 16; $skew++) {
        $skewedMaskedSprite = $maskedSprite->getShiftedCopy($skew);

        // this becomes the input for the compiled sprite
        $maskWords = $skewedMaskedSprite->exportToWords(false, true);
        // convert word data to byte data
        $skewedMaskCharData= '';
        foreach ($maskWords as $word) {
            $skewedMaskCharData .= chr($word >> 8);
            $skewedMaskCharData .= chr($word & 255);
        }

        echo("skewed mask char data length is: ".strlen($skewedMaskCharData)."\n");

        //echo("bitplane data length ".strlen($skewedBitplaneCharData)."\n");
        //echo("mask data length ".strlen($skewedMaskCharData)."\n");

        $widthInPixels = $skewedMaskedSprite->getWidth();
        $widthIn16PixelBlocks = $skewedMaskedSprite->getWidth() / 16;

        if (str_contains($definition['label'], 'yellow-car') || str_contains($definition['label'], 'blue-car')) {
            $instructions = [];
        } else {
            if (isset($definition['permittedSkews'])) {
                $permittedSkews = $definition['permittedSkews'];
            } else {
                $permittedSkews = [0, 2, 4, 6, 8, 10, 12, 14];
            }

            if (in_array($skew, $permittedSkews)) {
                //echo("** attempting ".$definition['label']." skew ".$skew."\n");
                $builder = new CompiledSpriteBuilder(
                    $skewedMaskCharData,
                    $includeMaskWords ? 10 : 8,
                    $widthIn16PixelBlocks,
                    $skewedMaskedSprite->getHeight(),
                    $skew,
                    $bytesPerDestLine
                );
                $instructions = $builder->runFirstPass();
            } else {
                $instructions = [];
            }
        }

        if (count($instructions)) {
            $processedInstructions = [];
            foreach ($instructions as $instruction) {
                $processedInstructions[] = '    ' . $instruction;
            }

            $filenameWithoutExtension = sys_get_temp_dir() . '/' . $definition['label'] . '-' . $skew;
            $sourceFilename = $filenameWithoutExtension. '.s';
            $outputFilename = $filenameWithoutExtension. '.bin';

            printf(
                "Writing source for %s skew %d to file %s\n",
                $definition['label'],
                $skew,
                $sourceFilename
            );

            file_put_contents($sourceFilename, implode("\n", $processedInstructions));

            // TODO: pass in name of vasm command
            $assembleCommand = sprintf(
                'vasmm68k_mot %s -Fbin -o %s',
                $sourceFilename,
                $outputFilename
            );

            printf(
                "Assembling source in file %s\n",
                $sourceFilename
            );

            $result = exec($assembleCommand);
            if ($result === false) {
                printf("assembly failed\n");
                exit(1);
            }

            $binaryCode = file_get_contents($outputFilename);
            $exportedSprite['skew_' . $skew] = unpack('C*', $binaryCode); 
        } else {
            //echo("failed to export ".$definition['label']." skew ".$skew."\n");
            $exportedSprite['skew_' . $skew] = null;
        }
    }

    $exportedSprites[] = $exportedSprite;
}


ob_start();
require('sprite_definitions_template.php');
$output = ob_get_clean();

$result = file_put_contents($definitionsOutputFilename, $output);
if ($result === false) {
    echo("Unable to write ground sprites data");
    exit(1);
}

echo("sprite definitions generation complete!\n");
