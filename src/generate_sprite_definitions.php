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

    $maskedSprite = SpriteConvertor::createMaskedSprite($croppedIndexedBitmap);
    $planarData = $maskedSprite->exportToPlanarData();

    $exportedSprite = [
        'origin_x' => $croppedIndexedBitmap->getOriginX(),
        'origin_y' => $croppedIndexedBitmap->getOriginY(),
        'source_data_width' => $maskedSprite->getWidth(),
        'source_data_height' => $maskedSprite->getHeight(),
        'words' => $planarData->getWords(),
    ];

    for ($skew = 0; $skew < 16; $skew++) {
        /*printf(
            "masked sprite width: %d\n",
            $maskedSprite->getWidth()
        );*/
        $skewedMaskedSprite = $maskedSprite->getShiftedCopy($skew);
        /*printf(
            "skewed masked sprite width: %d\n",
            $skewedMaskedSprite->getWidth()
        );*/
        //$skewedMaskedSprite = $maskedSprite;
        $planarData = $maskedSprite->exportToPlanarData();
        $skewedPlanarData = $skewedMaskedSprite->exportToPlanarData();

        /*$planarDataWords = $planarData->getWords();
        $skewedPlanarDataWords = $skewedPlanarData->getWords();
        if ($planarDataWords != $skewedPlanarDataWords) {
            var_dump(array_slice($planarDataWords, 0, 10));
            var_dump(array_slice($skewedPlanarDataWords, 0, 10));
            echo("FAIL");
            exit(1);
        }*/

        // convert word data to byte data
        $skewedCharData= '';
        $words = $skewedPlanarData->getWords();

        foreach ($words as $word) {
            $skewedCharData .= chr($word >> 8);
            $skewedCharData .= chr($word & 255);
        }

        //echo("-------------------\n");
        $widthInPixels = $skewedMaskedSprite->getWidth();
        //var_dump($widthInPixels);
        $widthIn16PixelBlocks = $skewedMaskedSprite->getWidth() / 16;
        //var_dump($widthIn16PixelBlocks);
        //printf("skew is %d\n", $skew);
        //printf("width in 16 pixel blocks: %d\n", $widthIn16PixelBlocks);

        if (str_contains($definition['label'], 'yellow-car') || str_contains($definition['label'], 'blue-car')) {
            $instructions = [];
        } else {
            if (isset($definition['permittedSkews'])) {
                $permittedSkews = $definition['permittedSkews'];
            } else {
                $permittedSkews = [0, 2, 4, 6, 8, 10, 12, 14];
            }

            if (in_array($skew, $permittedSkews)) {
                $builder = new CompiledSpriteBuilder(
                    $skewedCharData,
                    $widthIn16PixelBlocks,
                    $skewedMaskedSprite->getHeight(),
                    $skew
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
