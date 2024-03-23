<?php

require_once 'library.php';
require_once 'sprite_spans.php';

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

$definitions = [
    [
        'label' => 'tree-2',
        'left' => 5,
        'top' => 5,
        'width' => 63,
        'height' => 54,
    ],
    [
        'label' => 'tree-3',
        'left' => 79,
        'top' => 18,
        'width' => 47,
        'height' => 40,
    ],
    [
        'label' => 'tree-4',
        'left' => 134,
        'top' => 25,
        'width' => 38,
        'height' => 32,
    ],
    [
        'label' => 'tree-5',
        'left' => 177,
        'top' => 31,
        'width' => 31,
        'height' => 26,
    ],
    [
        'label' => 'tree-6',
        'left' => 210,
        'top' => 34,
        'width' => 28,
        'height' => 22,
    ],
    [
        'label' => 'tree-7',
        'left' => 240,
        'top' => 38,
        'width' => 22,
        'height' => 18,
    ],
    [
        'label' => 'tree-8',
        'left' => 263,
        'top' => 40,
        'width' => 19,
        'height' => 16,
    ],
    [
        'label' => 'tree-9',
        'left' => 286,
        'top' => 43,
        'width' => 14,
        'height' => 12,
    ],

    // red car centre offset 8

    [
        'label' => 'red-car-centre-1',
        'left' => 3,
        'top' => 74,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'red-car-centre-2',
        'left' => 54,
        'top' => 74,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'red-car-centre-3',
        'left' => 93,
        'top' => 74,
        'width' => 28,
        'height' => 14,
    ],
    [
        'label' => 'red-car-centre-4',
        'left' => 123,
        'top' => 74,
        'width' => 23,
        'height' => 12,
    ],
    [
        'label' => 'red-car-centre-5',
        'left' => 149,
        'top' => 74,
        'width' => 21,
        'height' => 11,
    ],
    [
        'label' => 'red-car-centre-6',
        'left' => 172,
        'top' => 74,
        'width' => 16,
        'height' => 9,
    ],
    [
        'label' => 'red-car-centre-7',
        'left' => 190,
        'top' => 74,
        'width' => 14,
        'height' => 7,
    ],
    [
        'label' => 'red-car-centre-8',
        'left' => 206,
        'top' => 74,
        'width' => 11,
        'height' => 6,
    ],

    // red car left offset 16

    [
        'label' => 'red-car-left-1',
        'left' => 3,
        'top' => 100,
        'width' => 64,
        'height' => 25,
    ],
    [
        'label' => 'red-car-left-2',
        'left' => 69,
        'top' => 100,
        'width' => 48,
        'height' => 19,
    ],
    [
        'label' => 'red-car-left-3',
        'left' => 119,
        'top' => 100,
        'width' => 38,
        'height' => 16,
    ],
    [
        'label' => 'red-car-left-4',
        'left' => 159,
        'top' => 100,
        'width' => 31,
        'height' => 12,
    ],
    [
        'label' => 'red-car-left-5',
        'left' => 192,
        'top' => 100,
        'width' => 28,
        'height' => 11,
    ],
    [
        'label' => 'red-car-left-6',
        'left' => 222,
        'top' => 100,
        'width' => 22,
        'height' => 9,
    ],
    [
        'label' => 'red-car-left-7',
        'left' => 246,
        'top' => 100,
        'width' => 19,
        'height' => 8,
    ],
    [
        'label' => 'red-car-left-8',
        'left' => 267,
        'top' => 100,
        'width' => 14,
        'height' => 6,
    ],

    // red car right offset 24

    [
        'label' => 'red-car-right-1',
        'left' => 3,
        'top' => 131,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'red-car-right-2',
        'left' => 69,
        'top' => 131,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'red-car-right-3',
        'left' => 119,
        'top' => 131,
        'width' => 37,
        'height' => 15,
    ],
    [
        'label' => 'red-car-right-4',
        'left' => 158,
        'top' => 131,
        'width' => 31,
        'height' => 12,
    ],
    [
        'label' => 'red-car-right-5',
        'left' => 191,
        'top' => 131,
        'width' => 29,
        'height' => 11,
    ],
    [
        'label' => 'red-car-right-6',
        'left' => 221,
        'top' => 131,
        'width' => 22,
        'height' => 9,
    ],
    [
        'label' => 'red-car-right-7',
        'left' => 245,
        'top' => 131,
        'width' => 18,
        'height' => 8,
    ],
    [
        'label' => 'red-car-right-8',
        'left' => 265,
        'top' => 131,
        'width' => 14,
        'height' => 6,
    ],

    // yellow car centre offset 32

    [
        'label' => 'yellow-car-centre-1',
        'left' => 3,
        'top' => 162,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'yellow-car-centre-2',
        'left' => 54,
        'top' => 162,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'yellow-car-centre-3',
        'left' => 93,
        'top' => 162,
        'width' => 28,
        'height' => 14,
    ],
    [
        'label' => 'yellow-car-centre-4',
        'left' => 123,
        'top' => 162,
        'width' => 24,
        'height' => 12,
    ],
    [
        'label' => 'yellow-car-centre-5',
        'left' => 149,
        'top' => 162,
        'width' => 22,
        'height' => 11,
    ],
    [
        'label' => 'yellow-car-centre-6',
        'left' => 172,
        'top' => 162,
        'width' => 17,
        'height' => 9,
    ],
    [
        'label' => 'yellow-car-centre-7',
        'left' => 190,
        'top' => 162,
        'width' => 14,
        'height' => 7,
    ],
    [
        'label' => 'yellow-car-centre-8',
        'left' => 206,
        'top' => 192,
        'width' => 12,
        'height' => 6,
    ],

    // yellow car left offset 40

    [
        'label' => 'yellow-car-left-1',
        'left' => 3,
        'top' => 189,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'yellow-car-left-2',
        'left' => 69,
        'top' => 189,
        'width' => 47,
        'height' => 19,
    ],
    [
        'label' => 'yellow-car-left-3',
        'left' => 119,
        'top' => 189,
        'width' => 38,
        'height' => 16,
    ],
    [
        'label' => 'yellow-car-left-4',
        'left' => 159,
        'top' => 189,
        'width' => 31,
        'height' => 13,
    ],
    [
        'label' => 'yellow-car-left-5',
        'left' => 192,
        'top' => 189,
        'width' => 28,
        'height' => 11,
    ],
    [
        'label' => 'yellow-car-left-6',
        'left' => 222,
        'top' => 189,
        'width' => 22,
        'height' => 10,
    ],
    [
        'label' => 'yellow-car-left-7',
        'left' => 246,
        'top' => 189,
        'width' => 19,
        'height' => 8,
    ],
    [
        'label' => 'yellow-car-left-8',
        'left' => 267,
        'top' => 189,
        'width' => 14,
        'height' => 6,
    ],

    // yellow car right offset 48

    [
        'label' => 'yellow-car-right-1',
        'left' => 3,
        'top' => 217,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'yellow-car-right-2',
        'left' => 69,
        'top' => 217,
        'width' => 48,
        'height' => 19,
    ],
    [
        'label' => 'yellow-car-right-3',
        'left' => 119,
        'top' => 217,
        'width' => 38,
        'height' => 15,
    ],
    [
        'label' => 'yellow-car-right-4',
        'left' => 158,
        'top' => 217,
        'width' => 31,
        'height' => 12,
    ],
    [
        'label' => 'yellow-car-right-5',
        'left' => 191,
        'top' => 217,
        'width' => 28,
        'height' => 11,
    ],
    [
        'label' => 'yellow-car-right-6',
        'left' => 221,
        'top' => 217,
        'width' => 22,
        'height' => 9,
    ],
    [
        'label' => 'yellow-car-right-7',
        'left' => 245,
        'top' => 217,
        'width' => 18,
        'height' => 7,
    ],
    [
        'label' => 'yellow-car-right-8',
        'left' => 265,
        'top' => 217,
        'width' => 15,
        'height' => 6,
    ],
 
    // michelin offset 56

    [
        'label' => 'michelin-1',
        'left' => 3,
        'top' => 331,
        'width' => 64,
        'height' => 16,
    ],
    [
        'label' => 'michelin-2',
        'left' => 69,
        'top' => 331,
        'width' => 48,
        'height' => 12,
    ],
    [
        'label' => 'michelin-3',
        'left' => 119,
        'top' => 331,
        'width' => 37,
        'height' => 9,
    ],
    [
        'label' => 'michelin-4',
        'left' => 157,
        'top' => 331,
        'width' => 31,
        'height' => 8,
    ],
    [
        'label' => 'michelin-5',
        'left' => 191,
        'top' => 331,
        'width' => 28,
        'height' => 7,
    ],
    [
        'label' => 'michelin-6',
        'left' => 221,
        'top' => 331,
        'width' => 22,
        'height' => 6,
    ],
    [
        'label' => 'michelin-7',
        'left' => 245,
        'top' => 331,
        'width' => 18,
        'height' => 4,
    ],
    [
        'label' => 'michelin-8',
        'left' => 265,
        'top' => 331,
        'width' => 15,
        'height' => 4,
    ],

    // gitanes offset 64

    [
        'label' => 'gitanes-1',
        'left' => 3,
        'top' => 385,
        'width' => 48,
        'height' => 54,
    ],
    [
        'label' => 'gitanes-2',
        'left' => 56,
        'top' => 385,
        'width' => 37,
        'height' => 41,
    ],
    [
        'label' => 'gitanes-3',
        'left' => 98,
        'top' => 385,
        'width' => 28,
        'height' => 32,
    ],
    [
        'label' => 'gitanes-4',
        'left' => 132,
        'top' => 385,
        'width' => 25,
        'height' => 28,
    ],
    [
        'label' => 'gitanes-5',
        'left' => 162,
        'top' => 385,
        'width' => 21,
        'height' => 24,
    ],
    [
        'label' => 'gitanes-6',
        'left' => 188,
        'top' => 385,
        'width' => 17,
        'height' => 19,
    ],
    [
        'label' => 'gitanes-7',
        'left' => 207,
        'top' => 385,
        'width' => 14,
        'height' => 16,
    ],
    [
        'label' => 'gitanes-8',
        'left' => 224,
        'top' => 385,
        'width' => 11,
        'height' => 12,
    ],

    // moto offset 72

    [
        'label' => 'moto-journal-1',
        'left' => 3,
        'top' => 507,
        'width' => 64,
        'height' => 36,
    ],
    [
        'label' => 'moto-journal-2',
        'left' => 70,
        'top' => 507,
        'width' => 48,
        'height' => 27,
    ],
    [
        'label' => 'moto-journal-3',
        'left' => 120,
        'top' => 507,
        'width' => 37,
        'height' => 21,
    ],
    [
        'label' => 'moto-journal-4',
        'left' => 160,
        'top' => 507,
        'width' => 30,
        'height' => 17,
    ],
    [
        'label' => 'moto-journal-5',
        'left' => 194,
        'top' => 507,
        'width' => 28,
        'height' => 15,
    ],
    [
        'label' => 'moto-journal-6',
        'left' => 225,
        'top' => 507,
        'width' => 22,
        'height' => 12,
    ],
    [
        'label' => 'moto-journal-7',
        'left' => 250,
        'top' => 507,
        'width' => 18,
        'height' => 11,
    ],
    [
        'label' => 'moto-journal-8',
        'left' => 271,
        'top' => 507,
        'width' => 15,
        'height' => 9,
    ],

    // total offset 80

    [
        'label' => 'total-1',
        'left' => 3,
        'top' => 569,
        'width' => 48,
        'height' => 15,
    ],
    [
        'label' => 'total-2',
        'left' => 55,
        'top' => 569,
        'width' => 36,
        'height' => 11,
    ],
    [
        'label' => 'total-3',
        'left' => 95,
        'top' => 569,
        'width' => 27,
        'height' => 8,
    ],
    [
        'label' => 'total-4',
        'left' => 126,
        'top' => 569,
        'width' => 25,
        'height' => 8,
    ],
    [
        'label' => 'total-5',
        'left' => 155,
        'top' => 569,
        'width' => 21,
        'height' => 7,
    ],
    [
        'label' => 'total-6',
        'left' => 180,
        'top' => 569,
        'width' => 18,
        'height' => 5,
    ],
    [
        'label' => 'total-7',
        'left' => 201,
        'top' => 569,
        'width' => 15,
        'height' => 5,
    ],
    [
        'label' => 'total-8',
        'left' => 219,
        'top' => 569,
        'width' => 12,
        'height' => 4,
    ],

    // lucas offset 88

    [
        'label' => 'lucas-1',
        'left' => 3,
        'top' => 352,
        'width' => 64,
        'height' => 27,
    ],
    [
        'label' => 'lucas-2',
        'left' => 69,
        'top' => 352,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'lucas-3',
        'left' => 119,
        'top' => 352,
        'width' => 37,
        'height' => 16,
    ],
    [
        'label' => 'lucas-4',
        'left' => 157,
        'top' => 352,
        'width' => 30,
        'height' => 13,
    ],
    [
        'label' => 'lucas-5',
        'left' => 191,
        'top' => 352,
        'width' => 28,
        'height' => 12,
    ],
    [
        'label' => 'lucas-6',
        'left' => 221,
        'top' => 352,
        'width' => 22,
        'height' => 10,
    ],
    [
        'label' => 'lucas-7',
        'left' => 245,
        'top' => 352,
        'width' => 18,
        'height' => 8,
    ],
    [
        'label' => 'lucas-8',
        'left' => 265,
        'top' => 352,
        'width' => 15,
        'height' => 7,
    ],

    // lamppost offset 96

    [
        'label' => 'lamppost-1',
        'left' => 3,
        'top' => 956,
        'width' => 16,
        'height' => 100,
    ],
    [
        'label' => 'lamppost-2',
        'left' => 24,
        'top' => 956,
        'width' => 13,
        'height' => 77,
    ],
    [
        'label' => 'lamppost-3',
        'left' => 41,
        'top' => 956,
        'width' => 10,
        'height' => 59,
    ],
    [
        'label' => 'lamppost-4',
        'left' => 53,
        'top' => 956,
        'width' => 9,
        'height' => 52,
    ],
    [
        'label' => 'lamppost-5',
        'left' => 67,
        'top' => 956,
        'width' => 8,
        'height' => 47,
    ],
    [
        'label' => 'lamppost-6',
        'left' => 79,
        'top' => 956,
        'width' => 7,
        'height' => 41,
    ],
    [
        'label' => 'lamppost-7',
        'left' => 90,
        'top' => 956,
        'width' => 6,
        'height' => 34,
    ],
    [
        'label' => 'lamppost-8',
        'left' => 100,
        'top' => 956,
        'width' => 5,
        'height' => 28,
    ],

    // left arrow offset 104

    [
        'label' => 'left-arrow-1',
        'left' => 3,
        'top' => 899,
        'width' => 48,
        'height' => 49,
    ],
    [
        'label' => 'left-arrow-2',
        'left' => 56,
        'top' => 899,
        'width' => 36,
        'height' => 37,
    ],
    [
        'label' => 'left-arrow-3',
        'left' => 98,
        'top' => 899,
        'width' => 27,
        'height' => 28,
    ],
    [
        'label' => 'left-arrow-4',
        'left' => 132,
        'top' => 899,
        'width' => 25,
        'height' => 25,
    ],
    [
        'label' => 'left-arrow-5',
        'left' => 162,
        'top' => 899,
        'width' => 21,
        'height' => 21,
    ],
    [
        'label' => 'left-arrow-6',
        'left' => 187,
        'top' => 899,
        'width' => 17,
        'height' => 17,
    ],
    [
        'label' => 'left-arrow-7',
        'left' => 207,
        'top' => 899,
        'width' => 14,
        'height' => 14,
    ],
    [
        'label' => 'left-arrow-8',
        'left' => 224,
        'top' => 899,
        'width' => 11,
        'height' => 12,
    ],

    // right arrow offset 112

    [
        'label' => 'right-arrow-1',
        'left' => 3,
        'top' => 449,
        'width' => 48,
        'height' => 49,
    ],
    [
        'label' => 'right-arrow-2',
        'left' => 56,
        'top' => 449,
        'width' => 36,
        'height' => 37,
    ],
    [
        'label' => 'right-arrow-3',
        'left' => 98,
        'top' => 449,
        'width' => 27,
        'height' => 28,
    ],
    [
        'label' => 'right-arrow-4',
        'left' => 132,
        'top' => 449,
        'width' => 25,
        'height' => 25,
    ],
    [
        'label' => 'right-arrow-5',
        'left' => 162,
        'top' => 449,
        'width' => 21,
        'height' => 21,
    ],
    [
        'label' => 'right-arrow-6',
        'left' => 187,
        'top' => 449,
        'width' => 17,
        'height' => 17,
    ],
    [
        'label' => 'right-arrow-7',
        'left' => 207,
        'top' => 449,
        'width' => 14,
        'height' => 14,
    ],
    [
        'label' => 'right-arrow-8',
        'left' => 224,
        'top' => 449,
        'width' => 11,
        'height' => 12,
    ],

    // konami offset 120

    [
        'label' => 'konami-1',
        'left' => 3,
        'top' => 591,
        'width' => 48,
        'height' => 62,
    ],
    [
        'label' => 'konami-2',
        'left' => 55,
        'top' => 591,
        'width' => 36,
        'height' => 47,
    ],
    [
        'label' => 'konami-3',
        'left' => 95,
        'top' => 591,
        'width' => 27,
        'height' => 34,
    ],
    [
        'label' => 'konami-4',
        'left' => 126,
        'top' => 591,
        'width' => 25,
        'height' => 32,
    ],
    [
        'label' => 'konami-5',
        'left' => 155,
        'top' => 591,
        'width' => 21,
        'height' => 27,
    ],
    [
        'label' => 'konami-6',
        'left' => 180,
        'top' => 591,
        'width' => 18,
        'height' => 23,
    ],
    [
        'label' => 'konami-7',
        'left' => 201,
        'top' => 591,
        'width' => 15,
        'height' => 19,
    ],
    [
        'label' => 'konami-8',
        'left' => 219,
        'top' => 591,
        'width' => 12,
        'height' => 15,
    ],

    // bp offset 128

    [
        'label' => 'bp-1',
        'left' => 3,
        'top' => 659,
        'width' => 48,
        'height' => 56,
    ],
    [
        'label' => 'bp-2',
        'left' => 55,
        'top' => 659,
        'width' => 36,
        'height' => 42,
    ],
    [
        'label' => 'bp-3',
        'left' => 95,
        'top' => 659,
        'width' => 27,
        'height' => 32,
    ],
    [
        'label' => 'bp-4',
        'left' => 126,
        'top' => 659,
        'width' => 25,
        'height' => 29,
    ],
    [
        'label' => 'bp-5',
        'left' => 155,
        'top' => 659,
        'width' => 21,
        'height' => 25,
    ],
    [
        'label' => 'bp-6',
        'left' => 180,
        'top' => 659,
        'width' => 18,
        'height' => 21,
    ],
    [
        'label' => 'bp-7',
        'left' => 201,
        'top' => 659,
        'width' => 15,
        'height' => 17,
    ],
    [
        'label' => 'bp-8',
        'left' => 219,
        'top' => 659,
        'width' => 12,
        'height' => 14,
    ],

    // tall tree offset 136

    [
        'label' => 'tall-tree-1',
        'left' => 3,
        'top' => 721,
        'width' => 32,
        'height' => 94,
    ],
    [
        'label' => 'tall-tree-2',
        'left' => 39,
        'top' => 721,
        'width' => 23,
        'height' => 66,
    ],
    [
        'label' => 'tall-tree-3',
        'left' => 65,
        'top' => 721,
        'width' => 19,
        'height' => 54,
    ],
    [
        'label' => 'tall-tree-4',
        'left' => 86,
        'top' => 721,
        'width' => 17,
        'height' => 48,
    ],
    [
        'label' => 'tall-tree-5',
        'left' => 106,
        'top' => 721,
        'width' => 15,
        'height' => 41,
    ],
    [
        'label' => 'tall-tree-6',
        'left' => 123,
        'top' => 721,
        'width' => 12,
        'height' => 35,
    ],
    [
        'label' => 'tall-tree-7',
        'left' => 138,
        'top' => 721,
        'width' => 11,
        'height' => 30,
    ],
    [
        'label' => 'tall-tree-8',
        'left' => 151,
        'top' => 721,
        'width' => 9,
        'height' => 25,
    ],

    // short tree offset 144

    [
        'label' => 'short-tree-1',
        'left' => 3,
        'top' => 820,
        'width' => 48,
        'height' => 72,
    ],
    [
        'label' => 'short-tree-2',
        'left' => 55,
        'top' => 820,
        'width' => 37,
        'height' => 55,
    ],
    [
        'label' => 'short-tree-3',
        'left' => 95,
        'top' => 820,
        'width' => 29,
        'height' => 43,
    ],
    [
        'label' => 'short-tree-4',
        'left' => 127,
        'top' => 820,
        'width' => 25,
        'height' => 37,
    ],
    [
        'label' => 'short-tree-5',
        'left' => 155,
        'top' => 820,
        'width' => 21,
        'height' => 31,
    ],
    [
        'label' => 'short-tree-6',
        'left' => 179,
        'top' => 820,
        'width' => 18,
        'height' => 27,
    ],
    [
        'label' => 'short-tree-7',
        'left' => 200,
        'top' => 820,
        'width' => 15,
        'height' => 23,
    ],
    [
        'label' => 'short-tree-8',
        'left' => 219,
        'top' => 820,
        'width' => 13,
        'height' => 18,
    ],


    // blue car TBC ...

    [
        'label' => 'blue-car',
        'left' => 105,
        'top' => 200,
        'width' => 48,
        'height' => 25,
    ],
];

$indexedBitmap = IndexedBitmap::loadGif($inputFilename);
$exportedSprites = [];

foreach ($definitions as $definition) {
    $croppedIndexedBitmap = $indexedBitmap->extractRegionToIndexedBitmap(
        $definition['left'],
        $definition['top'],
        $definition['width'],
        $definition['height'],
        intval($definition['width'] / 2),
        $definition['height'] - 1
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
        printf(
            "masked sprite width: %d\n",
            $maskedSprite->getWidth()
        );
        $skewedMaskedSprite = $maskedSprite->getShiftedCopy($skew);
        printf(
            "skewed masked sprite width: %d\n",
            $skewedMaskedSprite->getWidth()
        );
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

        echo("-------------------\n");
        $widthInPixels = $skewedMaskedSprite->getWidth();
        var_dump($widthInPixels);
        $widthIn16PixelBlocks = $skewedMaskedSprite->getWidth() / 16;
        var_dump($widthIn16PixelBlocks);
        printf("skew is %d\n", $skew);
        printf("width in 16 pixel blocks: %d\n", $widthIn16PixelBlocks);

        $builder = new CompiledSpriteBuilder(
            $skewedCharData,
            $widthIn16PixelBlocks,
            $skewedMaskedSprite->getHeight(),
            $skew
        );
        $instructions = $builder->runFirstPass();

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
