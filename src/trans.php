<?php

$offsets = [];

for ($ypos = 0; $ypos <= 12; $ypos++) {
    for ($xpos = 0; $xpos < 20; $xpos++) {
        $offsets[] = ($ypos * 16 * 160) + $xpos * 8;
    }
}

shuffle($offsets);

$index = 0;
for ($ypos = 0; $ypos <= 12; $ypos++) {
    echo("    ");
    for ($xpos = 0; $xpos < 20; $xpos++) {
        echo($offsets[$index]) . ", ";
        $index++;
    }
    echo("\n");
}
