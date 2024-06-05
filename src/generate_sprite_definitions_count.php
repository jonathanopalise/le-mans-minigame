<?php

require_once 'library.php';
require_once 'sprite_spans.php';

include 'sprite_definitions.php';

if ($argc < 2) {
    echo("Usage: php generate_sprite_definitions_count.php [outputFile]\n");
    exit(1);
}

$outputFilename = $argv[1];

$output = '#define SPRITE_DEFINITIONS_COUNT '.count($definitions);

$result = file_put_contents($outputFilename, $output);
if ($result === false) {
    echo("Unable to write sprite definitions count data");
    exit(1);
}

echo("sprite definitions count file write complete!\n");
