<?php

if ($argc < 2) {
    echo("Usage: php generate_sprite_definitions.php [outputFile]\n");
    exit(1);
}

$outputFilename = $argv[1];

/*$definitions = [
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -230,
        'track_position' => 30000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LUCAS'],
        'xpos' => 230,
        'track_position' => 50000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LEFT_ARROW'],
        'xpos' => -230,
        'track_position' => 70000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LAMPPOST'],
        'xpos' => 230,
        'track_position' => 90000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -230,
        'track_position' => 110000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_GITANES'],
        'xpos' => 230,
        'track_position' => 130000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TALL_TREE'],
        'xpos' => -230,
        'track_position' => 150000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_RIGHT_ARROW'],
        'xpos' => 230,
        'track_position' => 170000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_SHORT_TREE'],
        'xpos' => -230,
        'track_position' => 190000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_KONAMI'],
        'xpos' => 230,
        'track_position' => 210000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -230,
        'track_position' => 230000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TALL_TREE'],
        'xpos' => 230,
        'track_position' => 250000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_GITANES'],
        'xpos' => -230,
        'track_position' => 270000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_MOTO_JOURNAL'],
        'xpos' => 230,
        'track_position' => 290000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TOTAL'],
        'xpos' => -230,
        'track_position' => 310000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LUCAS'],
        'xpos' => 230,
        'track_position' => 330000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LEFT_ARROW'],
        'xpos' => -230,
        'track_position' => 350000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_BP'],
        'xpos' => 230,
        'track_position' => 370000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_KONAMI'],
        'xpos' => -230,
        'track_position' => 390000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TALL_TREE'],
        'xpos' => 230,
        'track_position' => 410000,
        'count' => 5,
        'gap' => 3500,
    ],
];*/

$definitions = [
    [
        'types' => ['SCENERY_TYPE_LAMPPOST'],
        'xpos' => -230,
        'track_position' => 9000,
        'count' => 9,
        'gap' => 6000,
    ],
    [
        'types' => ['SCENERY_TYPE_GITANES'],
        'xpos' => -230,
        'track_position' => 60000,
        'count' => 4,
        'gap' => 6000,
    ],
    [
        'types' => ['SCENERY_TYPE_LAMPPOST'],
        'xpos' => -230,
        'track_position' => 90000,
        'count' => 13,
        'gap' => 7000,
    ],
    [
        'types' => ['SCENERY_TYPE_LAMPPOST'],
        'xpos' => 230,
        'track_position' => 90000,
        'count' => 13,
        'gap' => 7000,
    ],
    [
        'types' => ['SCENERY_TYPE_RIGHT_ARROW'],
        'xpos' => -230,
        'track_position' => 240000,
        'count' => 7,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_GITANES',
            'SCENERY_TYPE_MICHELIN',
            'SCENERY_TYPE_TOTAL',
            'SCENERY_TYPE_KONAMI',
            'SCENERY_TYPE_BP',
        ],
        'xpos' => -230,
        'track_position' => 280000,
        'count' => 5,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_MOTO_JOURNAL',
            'SCENERY_TYPE_LUCAS'
        ],
        'xpos' => 230,
        'track_position' => 290000,
        'count' => 2,
        'gap' => 5000,
    ],
    [
        'types' => ['SCENERY_TYPE_BP'],
        'xpos' => 230,
        'track_position' => 310000,
        'count' => 8,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 310000,
        'count' => 8,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 370000,
        'count' => 1,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 390000,
        'count' => 8,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 230,
        'track_position' => 445000,
        'count' => 3,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => 230,
        'track_position' => 475000,
        'count' => 6,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_LEFT_ARROW',
        ],
        'xpos' => 230,
        'track_position' => 525000,
        'count' => 7,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => -230,
        'track_position' => 550000,
        'count' => 8,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => 230,
        'track_position' => 630000,
        'count' => 10,
        'gap' => 10000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 680000,
        'count' => 4,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 740000,
        'count' => 7,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_LUCAS',
        ],
        'xpos' => 230,
        'track_position' => 790000,
        'count' => 4,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 800000,
        'count' => 7,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
            'SCENERY_TYPE_TALL_TREE',
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => -230,
        'track_position' => 850000,
        'count' => 9,
        'gap' => 6000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 800000,
        'count' => 7,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 970000,
        'count' => 10,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 1050000,
        'count' => 8,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => 255,
        'track_position' => 1120000,
        'count' => 2,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => 230,
        'track_position' => 1123500,
        'count' => 1,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => 230,
        'track_position' => 1160000,
        'count' => 10,
        'gap' => 10000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => 255,
        'track_position' => 1165000,
        'count' => 9,
        'gap' => 10000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => 255,
        'track_position' => 1165000,
        'count' => 9,
        'gap' => 10000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -250,
        'track_position' => 1200000,
        'count' => 5,
        'gap' => 14000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 230,
        'track_position' => 1285000,
        'count' => 5,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
            'SCENERY_TYPE_ROUND_TREE',
            'SCENERY_TYPE_SHORT_TREE',
        ],
        'xpos' => -230,
        'track_position' => 1325000,
        'count' => 8,
        'gap' => 14000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => -255,
        'track_position' => 1332000,
        'count' => 7,
        'gap' => 14000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => -255,
        'track_position' => 1332000,
        'count' => 7,
        'gap' => 14000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 1450000,
        'count' => 4,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 230,
        'track_position' => 1453500,
        'count' => 4,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 1482500,
        'count' => 6,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_LAMPPOST',
        ],
        'xpos' => -230,
        'track_position' => 1660000,
        'count' => 5,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 1710000,
        'count' => 20,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_LUCAS',
            'SCENERY_TYPE_MOTO_JOURNAL',
            'SCENERY_TYPE_MICHELIN',
            'SCENERY_TYPE_TOTAL',
        ],
        'xpos' => 230,
        'track_position' => 1770000,
        'count' => 4,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_MICHELIN',
        ],
        'xpos' => 230,
        'track_position' => 1860000,
        'count' => 4,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_GITANES',
        ],
        'xpos' => -230,
        'track_position' => 1895000,
        'count' => 10,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 230,
        'track_position' => 1970000,
        'count' => 10,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_KONAMI',
        ],
        'xpos' => 230,
        'track_position' => 2030000,
        'count' => 4,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_KONAMI',
        ],
        'xpos' => -230,
        'track_position' => 2040000,
        'count' => 4,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
            'SCENERY_TYPE_ROUND_TREE',
        ],
        'xpos' => -250,
        'track_position' => 2065000,
        'count' => 3,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_SHORT_TREE',
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 250,
        'track_position' => 2070000,
        'count' => 2,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_RIGHT_ARROW',
        ],
        'xpos' => -230,
        'track_position' => 2091000,
        'count' => 8,
        'gap' => 5000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 2131000,
        'count' => 10,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => 230,
        'track_position' => 2208000,
        'count' => 10,
        'gap' => 7000,
    ],
    [
        'types' => [
            'SCENERY_TYPE_TALL_TREE',
        ],
        'xpos' => -230,
        'track_position' => 2285000,
        'count' => 10,
        'gap' => 7000,
    ],
];



function cmp($a, $b)
{
    return $a["track_position"] > $b["track_position"];
}

$tracksideItems = [];
foreach ($definitions as $definition) {
    $trackPosition = $definition['track_position'];
    $types = $definition['types'];
    $gap = $definition['gap'];
    $typesArrayIndex = 0;

    for ($index = 0; $index < $definition['count']; $index++) {
        $tracksideItems[] = [
            'type' => $types[$typesArrayIndex],
            'xpos' => $definition['xpos'],
            'track_position' => $trackPosition,
        ];

        $trackPosition += $gap;

        $typesArrayIndex++;
        if ($typesArrayIndex == count($types)) {
            $typesArrayIndex = 0;
        }
    }
}

usort($tracksideItems, "cmp");

ob_start();
require('trackside_items_template.php');
$output = ob_get_clean();

$result = file_put_contents($outputFilename, $output);
if ($result === false) {
    echo("Unable to write trackside items data");
    exit(1);
}

echo("trackside items generation complete!\n");
