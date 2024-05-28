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
        'left' => 3,
        'top' => 5,
        'width' => 64,
        'height' => 55,
    ],
    [
        'label' => 'tree-3',
        'left' => 71,
        'top' => 5,
        'width' => 48,
        'height' => 42,
    ],
    [
        'label' => 'tree-4',
        'left' => 123,
        'top' => 5,
        'width' => 36,
        'height' => 31,
    ],
    [
        'label' => 'tree-5',
        'left' => 163,
        'top' => 5,
        'width' => 27,
        'height' => 24,
    ],
    [
        'label' => 'tree-6',
        'left' => 194,
        'top' => 5,
        'width' => 20,
        'height' => 18,
    ],
    [
        'label' => 'tree-7',
        'left' => 218,
        'top' => 5,
        'width' => 15,
        'height' => 14,
    ],
    [
        'label' => 'tree-8',
        'left' => 237,
        'top' => 5,
        'width' => 11,
        'height' => 10,
    ],
    [
        'label' => 'tree-9',
        'left' => 252,
        'top' => 5,
        'width' => 8,
        'height' => 8,
    ],

    // red car centre offset 8

    [
        'label' => 'red-car-centre-1',
        'left' => 3,
        'top' => 1387,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'red-car-centre-2',
        'left' => 55,
        'top' => 1387,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'red-car-centre-3',
        'left' => 95,
        'top' => 1387,
        'width' => 27,
        'height' => 14,
    ],
    [
        'label' => 'red-car-centre-4',
        'left' => 126,
        'top' => 1387,
        'width' => 20,
        'height' => 10,
    ],
    [
        'label' => 'red-car-centre-5',
        'left' => 150,
        'top' => 1387,
        'width' => 15,
        'height' => 8,
    ],
    [
        'label' => 'red-car-centre-6',
        'left' => 169,
        'top' => 1387,
        'width' => 11,
        'height' => 6,
    ],
    [
        'label' => 'red-car-centre-7',
        'left' => 184,
        'top' => 1387,
        'width' => 8,
        'height' => 4,
    ],
    [
        'label' => 'red-car-centre-8',
        'left' => 196,
        'top' => 1387,
        'width' => 6,
        'height' => 3,
    ],

    // red car left offset 16

    [
        'label' => 'red-car-left-1',
        'left' => 3,
        'top' => 1474,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'red-car-left-2',
        'left' => 71,
        'top' => 1474,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'red-car-left-3',
        'left' => 123,
        'top' => 1474,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'red-car-left-4',
        'left' => 163,
        'top' => 1474,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'red-car-left-5',
        'left' => 194,
        'top' => 1474,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'red-car-left-6',
        'left' => 218,
        'top' => 1474,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'red-car-left-7',
        'left' => 237,
        'top' => 1474,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'red-car-left-8',
        'left' => 252,
        'top' => 1474,
        'width' => 8,
        'height' => 3,
    ],

    // red car right offset 24

    [
        'label' => 'red-car-right-1',
        'left' => 3,
        'top' => 1564,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'red-car-right-2',
        'left' => 71,
        'top' => 1564,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'red-car-right-3',
        'left' => 123,
        'top' => 1564,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'red-car-right-4',
        'left' => 163,
        'top' => 1564,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'red-car-right-5',
        'left' => 194,
        'top' => 1564,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'red-car-right-6',
        'left' => 218,
        'top' => 1564,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'red-car-right-7',
        'left' => 237,
        'top' => 1564,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'red-car-right-8',
        'left' => 252,
        'top' => 1564,
        'width' => 8,
        'height' => 3,
    ],

    // yellow car centre offset 32

    [
        'label' => 'yellow-car-centre-1',
        'left' => 3,
        'top' => 1416,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'yellow-car-centre-2',
        'left' => 55,
        'top' => 1416,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'yellow-car-centre-3',
        'left' => 95,
        'top' => 1416,
        'width' => 27,
        'height' => 14,
    ],
    [
        'label' => 'yellow-car-centre-4',
        'left' => 126,
        'top' => 1416,
        'width' => 20,
        'height' => 10,
    ],
    [
        'label' => 'yellow-car-centre-5',
        'left' => 150,
        'top' => 1416,
        'width' => 15,
        'height' => 8,
    ],
    [
        'label' => 'yellow-car-centre-6',
        'left' => 169,
        'top' => 1416,
        'width' => 11,
        'height' => 6,
    ],
    [
        'label' => 'yellow-car-centre-7',
        'left' => 184,
        'top' => 1416,
        'width' => 8,
        'height' => 4,
    ],
    [
        'label' => 'yellow-car-centre-8',
        'left' => 196,
        'top' => 1416,
        'width' => 6,
        'height' => 3,
    ],

    // yellow car left offset 40

    [
        'label' => 'yellow-car-left-1',
        'left' => 3,
        'top' => 1504,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'yellow-car-left-2',
        'left' => 71,
        'top' => 1504,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'yellow-car-left-3',
        'left' => 123,
        'top' => 1504,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'yellow-car-left-4',
        'left' => 163,
        'top' => 1504,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'yellow-car-left-5',
        'left' => 194,
        'top' => 1504,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'yellow-car-left-6',
        'left' => 218,
        'top' => 1504,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'yellow-car-left-7',
        'left' => 237,
        'top' => 1504,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'yellow-car-left-8',
        'left' => 252,
        'top' => 1504,
        'width' => 8,
        'height' => 3,
    ],

    // yellow car right offset 48

    [
        'label' => 'yellow-car-right-1',
        'left' => 3,
        'top' => 1594,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'yellow-car-right-2',
        'left' => 71,
        'top' => 1594,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'yellow-car-right-3',
        'left' => 123,
        'top' => 1594,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'yellow-car-right-4',
        'left' => 163,
        'top' => 1594,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'yellow-car-right-5',
        'left' => 194,
        'top' => 1594,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'yellow-car-right-6',
        'left' => 218,
        'top' => 1594,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'yellow-car-right-7',
        'left' => 237,
        'top' => 1594,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'yellow-car-right-8',
        'left' => 252,
        'top' => 1594,
        'width' => 8,
        'height' => 3,
    ],
 
    // blue car centre offset 8

    [
        'label' => 'blue-car-centre-1',
        'left' => 3,
        'top' => 1445,
        'width' => 48,
        'height' => 25,
    ],
    [
        'label' => 'blue-car-centre-2',
        'left' => 55,
        'top' => 1445,
        'width' => 36,
        'height' => 19,
    ],
    [
        'label' => 'blue-car-centre-3',
        'left' => 95,
        'top' => 1445,
        'width' => 27,
        'height' => 14,
    ],
    [
        'label' => 'blue-car-centre-4',
        'left' => 126,
        'top' => 1445,
        'width' => 20,
        'height' => 10,
    ],
    [
        'label' => 'blue-car-centre-5',
        'left' => 150,
        'top' => 1445,
        'width' => 15,
        'height' => 8,
    ],
    [
        'label' => 'blue-car-centre-6',
        'left' => 169,
        'top' => 1445,
        'width' => 11,
        'height' => 6,
    ],
    [
        'label' => 'blue-car-centre-7',
        'left' => 184,
        'top' => 1445,
        'width' => 8,
        'height' => 4,
    ],
    [
        'label' => 'blue-car-centre-8',
        'left' => 196,
        'top' => 1445,
        'width' => 6,
        'height' => 3,
    ],

    // blue car left offset 16

    [
        'label' => 'blue-car-left-1',
        'left' => 3,
        'top' => 1534,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'blue-car-left-2',
        'left' => 71,
        'top' => 1534,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'blue-car-left-3',
        'left' => 123,
        'top' => 1534,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'blue-car-left-4',
        'left' => 163,
        'top' => 1534,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'blue-car-left-5',
        'left' => 194,
        'top' => 1534,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'blue-car-left-6',
        'left' => 218,
        'top' => 1534,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'blue-car-left-7',
        'left' => 237,
        'top' => 1534,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'blue-car-left-8',
        'left' => 252,
        'top' => 1534,
        'width' => 8,
        'height' => 3,
    ],

    // blue car right offset 24

    [
        'label' => 'blue-car-right-1',
        'left' => 3,
        'top' => 1624,
        'width' => 64,
        'height' => 26,
    ],
    [
        'label' => 'blue-car-right-2',
        'left' => 71,
        'top' => 1624,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'blue-car-right-3',
        'left' => 123,
        'top' => 1624,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'blue-car-right-4',
        'left' => 163,
        'top' => 1624,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'blue-car-right-5',
        'left' => 194,
        'top' => 1624,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'blue-car-right-6',
        'left' => 218,
        'top' => 1624,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'blue-car-right-7',
        'left' => 237,
        'top' => 1624,
        'width' => 11,
        'height' => 4,
    ],
    [
        'label' => 'blue-car-right-8',
        'left' => 252,
        'top' => 1624,
        'width' => 8,
        'height' => 3,
    ],

    // michelin offset 56 UPDATED

    [
        'label' => 'michelin-1',
        'left' => 3,
        'top' => 331,
        'width' => 64,
        'height' => 16,
    ],
    [
        'label' => 'michelin-2',
        'left' => 71,
        'top' => 331,
        'width' => 48,
        'height' => 12,
    ],
    [
        'label' => 'michelin-3',
        'left' => 123,
        'top' => 331,
        'width' => 36,
        'height' => 9,
    ],
    [
        'label' => 'michelin-4',
        'left' => 163,
        'top' => 331,
        'width' => 27,
        'height' => 7,
    ],
    [
        'label' => 'michelin-5',
        'left' => 194,
        'top' => 331,
        'width' => 20,
        'height' => 5,
    ],
    [
        'label' => 'michelin-6',
        'left' => 218,
        'top' => 331,
        'width' => 15,
        'height' => 4,
    ],
    [
        'label' => 'michelin-7',
        'left' => 237,
        'top' => 331,
        'width' => 11,
        'height' => 3,
    ],
    [
        'label' => 'michelin-8',
        'left' => 252,
        'top' => 331,
        'width' => 8,
        'height' => 2,
    ],

    // gitanes offset 64 UPDATED

    [
        'label' => 'gitanes-1',
        'left' => 3,
        'top' => 385,
        'width' => 48,
        'height' => 54,
    ],
    [
        'label' => 'gitanes-2',
        'left' => 55,
        'top' => 385,
        'width' => 36,
        'height' => 41,
    ],
    [
        'label' => 'gitanes-3',
        'left' => 95,
        'top' => 385,
        'width' => 27,
        'height' => 30,
    ],
    [
        'label' => 'gitanes-4',
        'left' => 126,
        'top' => 385,
        'width' => 20,
        'height' => 23,
    ],
    [
        'label' => 'gitanes-5',
        'left' => 150,
        'top' => 385,
        'width' => 15,
        'height' => 17,
    ],
    [
        'label' => 'gitanes-6',
        'left' => 169,
        'top' => 385,
        'width' => 11,
        'height' => 12,
    ],
    [
        'label' => 'gitanes-7',
        'left' => 184,
        'top' => 385,
        'width' => 8,
        'height' => 9,
    ],
    [
        'label' => 'gitanes-8',
        'left' => 196,
        'top' => 385,
        'width' => 6,
        'height' => 7,
    ],

    // moto offset 72 UPDATED

    [
        'label' => 'moto-journal-1',
        'left' => 3,
        'top' => 507,
        'width' => 64,
        'height' => 36,
    ],
    [
        'label' => 'moto-journal-2',
        'left' => 71,
        'top' => 507,
        'width' => 48,
        'height' => 27,
    ],
    [
        'label' => 'moto-journal-3',
        'left' => 123,
        'top' => 507,
        'width' => 36,
        'height' => 20,
    ],
    [
        'label' => 'moto-journal-4',
        'left' => 163,
        'top' => 507,
        'width' => 27,
        'height' => 15,
    ],
    [
        'label' => 'moto-journal-5',
        'left' => 194,
        'top' => 507,
        'width' => 20,
        'height' => 11,
    ],
    [
        'label' => 'moto-journal-6',
        'left' => 218,
        'top' => 507,
        'width' => 15,
        'height' => 8,
    ],
    [
        'label' => 'moto-journal-7',
        'left' => 237,
        'top' => 507,
        'width' => 11,
        'height' => 6,
    ],
    [
        'label' => 'moto-journal-8',
        'left' => 252,
        'top' => 507,
        'width' => 8,
        'height' => 5,
    ],

    // total offset 80 UPDATED

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
        'width' => 20,
        'height' => 6,
    ],
    [
        'label' => 'total-5',
        'left' => 150,
        'top' => 569,
        'width' => 15,
        'height' => 5,
    ],
    [
        'label' => 'total-6',
        'left' => 169,
        'top' => 569,
        'width' => 11,
        'height' => 3,
    ],
    [
        'label' => 'total-7',
        'left' => 184,
        'top' => 569,
        'width' => 8,
        'height' => 3,
    ],
    [
        'label' => 'total-8',
        'left' => 196,
        'top' => 569,
        'width' => 6,
        'height' => 2,
    ],

    // lucas offset 88 UPDATED

    [
        'label' => 'lucas-1',
        'left' => 3,
        'top' => 352,
        'width' => 64,
        'height' => 27,
    ],
    [
        'label' => 'lucas-2',
        'left' => 71,
        'top' => 352,
        'width' => 48,
        'height' => 20,
    ],
    [
        'label' => 'lucas-3',
        'left' => 123,
        'top' => 352,
        'width' => 36,
        'height' => 15,
    ],
    [
        'label' => 'lucas-4',
        'left' => 163,
        'top' => 352,
        'width' => 27,
        'height' => 11,
    ],
    [
        'label' => 'lucas-5',
        'left' => 194,
        'top' => 352,
        'width' => 20,
        'height' => 8,
    ],
    [
        'label' => 'lucas-6',
        'left' => 218,
        'top' => 352,
        'width' => 15,
        'height' => 6,
    ],
    [
        'label' => 'lucas-7',
        'left' => 237,
        'top' => 352,
        'width' => 11,
        'height' => 5,
    ],
    [
        'label' => 'lucas-8',
        'left' => 252,
        'top' => 352,
        'width' => 8,
        'height' => 3,
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

    // left arrow offset 104 UPDATED

    [
        'label' => 'left-arrow-1',
        'left' => 3,
        'top' => 899,
        'width' => 48,
        'height' => 49,
    ],
    [
        'label' => 'left-arrow-2',
        'left' => 55,
        'top' => 899,
        'width' => 36,
        'height' => 37,
    ],
    [
        'label' => 'left-arrow-3',
        'left' => 95,
        'top' => 899,
        'width' => 27,
        'height' => 28,
    ],
    [
        'label' => 'left-arrow-4',
        'left' => 126,
        'top' => 899,
        'width' => 20,
        'height' => 20,
    ],
    [
        'label' => 'left-arrow-5',
        'left' => 150,
        'top' => 899,
        'width' => 15,
        'height' => 15,
    ],
    [
        'label' => 'left-arrow-6',
        'left' => 169,
        'top' => 899,
        'width' => 11,
        'height' => 11,
    ],
    [
        'label' => 'left-arrow-7',
        'left' => 184,
        'top' => 899,
        'width' => 8,
        'height' => 8,
    ],
    [
        'label' => 'left-arrow-8',
        'left' => 196,
        'top' => 899,
        'width' => 6,
        'height' => 6,
    ],

    // right arrow offset 112 UPDATED

    [
        'label' => 'right-arrow-1',
        'left' => 3,
        'top' => 449,
        'width' => 48,
        'height' => 49,
    ],
    [
        'label' => 'right-arrow-2',
        'left' => 55,
        'top' => 449,
        'width' => 36,
        'height' => 37,
    ],
    [
        'label' => 'right-arrow-3',
        'left' => 95,
        'top' => 449,
        'width' => 27,
        'height' => 28,
    ],
    [
        'label' => 'right-arrow-4',
        'left' => 126,
        'top' => 449,
        'width' => 20,
        'height' => 20,
    ],
    [
        'label' => 'right-arrow-5',
        'left' => 150,
        'top' => 449,
        'width' => 15,
        'height' => 15,
    ],
    [
        'label' => 'right-arrow-6',
        'left' => 169,
        'top' => 449,
        'width' => 11,
        'height' => 11,
    ],
    [
        'label' => 'right-arrow-7',
        'left' => 184,
        'top' => 449,
        'width' => 8,
        'height' => 8,
    ],
    [
        'label' => 'right-arrow-8',
        'left' => 196,
        'top' => 449,
        'width' => 6,
        'height' => 6,
    ],

    // konami offset 120 UPDATED

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
        'height' => 35,
    ],
    [
        'label' => 'konami-4',
        'left' => 126,
        'top' => 591,
        'width' => 20,
        'height' => 26,
    ],
    [
        'label' => 'konami-5',
        'left' => 150,
        'top' => 591,
        'width' => 15,
        'height' => 19,
    ],
    [
        'label' => 'konami-6',
        'left' => 169,
        'top' => 591,
        'width' => 11,
        'height' => 14,
    ],
    [
        'label' => 'konami-7',
        'left' => 184,
        'top' => 591,
        'width' => 8,
        'height' => 10,
    ],
    [
        'label' => 'konami-8',
        'left' => 196,
        'top' => 591,
        'width' => 6,
        'height' => 7,
    ],

    // bp offset 128 UPDATED

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
        'width' => 20,
        'height' => 23,
    ],
    [
        'label' => 'bp-5',
        'left' => 150,
        'top' => 659,
        'width' => 15,
        'height' => 18,
    ],
    [
        'label' => 'bp-6',
        'left' => 169,
        'top' => 659,
        'width' => 11,
        'height' => 13,
    ],
    [
        'label' => 'bp-7',
        'left' => 184,
        'top' => 659,
        'width' => 8,
        'height' => 9,
    ],
    [
        'label' => 'bp-8',
        'left' => 196,
        'top' => 659,
        'width' => 6,
        'height' => 7,
    ],

    // tall tree offset 136 UPDATED

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
        'width' => 24,
        'height' => 70,
    ],
    [
        'label' => 'tall-tree-3',
        'left' => 67,
        'top' => 721,
        'width' => 18,
        'height' => 53,
    ],
    [
        'label' => 'tall-tree-4',
        'left' => 89,
        'top' => 721,
        'width' => 13,
        'height' => 38,
    ],
    [
        'label' => 'tall-tree-5',
        'left' => 105,
        'top' => 721,
        'width' => 10,
        'height' => 29,
    ],
    [
        'label' => 'tall-tree-6',
        'left' => 120,
        'top' => 721,
        'width' => 7,
        'height' => 21,
    ],
    [
        'label' => 'tall-tree-7',
        'left' => 131,
        'top' => 721,
        'width' => 5,
        'height' => 15,
    ],
    [
        'label' => 'tall-tree-8',
        'left' => 140,
        'top' => 721,
        'width' => 4,
        'height' => 11,
    ],

    // short tree offset 144 UPDATED

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
        'width' => 36,
        'height' => 54,
    ],
    [
        'label' => 'short-tree-3',
        'left' => 95,
        'top' => 820,
        'width' => 27,
        'height' => 41,
    ],
    [
        'label' => 'short-tree-4',
        'left' => 126,
        'top' => 820,
        'width' => 20,
        'height' => 30,
    ],
    [
        'label' => 'short-tree-5',
        'left' => 150,
        'top' => 820,
        'width' => 15,
        'height' => 23,
    ],
    [
        'label' => 'short-tree-6',
        'left' => 169,
        'top' => 820,
        'width' => 11,
        'height' => 17,
    ],
    [
        'label' => 'short-tree-7',
        'left' => 184,
        'top' => 820,
        'width' => 8,
        'height' => 12,
    ],
    [
        'label' => 'short-tree-8',
        'left' => 196,
        'top' => 820,
        'width' => 6,
        'height' => 9,
    ],

    // red car flip

    [
        'label' => 'red-car-flip-1',
        'left' => 3,
        'top' => 1724,
        'width' => 105,
        'height' => 41,
    ],
    [
        'label' => 'red-car-flip-2',
        'left' => 111,
        'top' => 1724,
        'width' => 108,
        'height' => 37,
    ],
    [
        'label' => 'red-car-flip-3',
        'left' => 118,
        'top' => 1766,
        'width' => 111,
        'height' => 51,
    ],
    [
        'label' => 'red-car-flip-4',
        'left' => 118,
        'top' => 1830,
        'width' => 108,
        'height' => 37,
    ],
    [
        'label' => 'red-car-flip-5',
        'left' => 3,
        'top' => 1828,
        'width' => 105,
        'height' => 39,
    ],
    [
        'label' => 'red-car-flip-6',
        'left' => 3,
        'top' => 1766,
        'width' => 112,
        'height' => 59,
    ],








    // blue car TBC ...
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

        if (str_contains($definition['label'], 'yellow-car') || str_contains($definition['label'], 'blue-car')) {
            $instructions = ['rts'];
        } else {
            $builder = new CompiledSpriteBuilder(
                $skewedCharData,
                $widthIn16PixelBlocks,
                $skewedMaskedSprite->getHeight(),
                $skew
            );
            $instructions = $builder->runFirstPass();
        }

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
