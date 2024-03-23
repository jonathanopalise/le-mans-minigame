<?php

if ($argc < 2) {
    echo("Usage: php generate_sprite_definitions.php [outputFile]\n");
    exit(1);
}

$outputFilename = $argv[1];

$definitions = [
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -200,
        'track_position' => 30000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LUCAS'],
        'xpos' => 200,
        'track_position' => 50000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LEFT_ARROW'],
        'xpos' => -200,
        'track_position' => 70000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LAMPPOST'],
        'xpos' => 200,
        'track_position' => 90000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -200,
        'track_position' => 110000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TOTAL'],
        'xpos' => 200,
        'track_position' => 130000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_GITANES'],
        'xpos' => -200,
        'track_position' => 150000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_RIGHT_ARROW'],
        'xpos' => 200,
        'track_position' => 170000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_BP'],
        'xpos' => -200,
        'track_position' => 190000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_KONAMI'],
        'xpos' => 200,
        'track_position' => 210000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => -200,
        'track_position' => 230000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_ROUND_TREE'],
        'xpos' => 200,
        'track_position' => 250000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_GITANES'],
        'xpos' => -200,
        'track_position' => 270000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_MOTO_JOURNAL'],
        'xpos' => 200,
        'track_position' => 290000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_TOTAL'],
        'xpos' => -200,
        'track_position' => 310000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LUCAS'],
        'xpos' => 200,
        'track_position' => 330000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_LEFT_ARROW'],
        'xpos' => -200,
        'track_position' => 350000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_BP'],
        'xpos' => 200,
        'track_position' => 370000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_KONAMI'],
        'xpos' => -200,
        'track_position' => 390000,
        'count' => 5,
        'gap' => 3500,
    ],
    [
        'types' => ['SCENERY_TYPE_MOTO_JOURNAL'],
        'xpos' => 200,
        'track_position' => 410000,
        'count' => 5,
        'gap' => 3500,
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
