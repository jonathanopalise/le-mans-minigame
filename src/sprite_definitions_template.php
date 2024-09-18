<?php

function exportArrayContent($data, $cType = null) {
    if (is_array($data)) {
        return sprintf(
            '( %s[] ){ %s }',
            $cType,
            implode(', ',$data)
        );

    } else {
        return '0';
    }
}

?>

#include "../sprite_definitions.h"

struct SpriteDefinition sprite_definitions[] = {
    <?php foreach ($exportedSprites as $key => $sprite) { ?>
        {
        <?php echo($sprite['origin_x']); ?>,
        <?php echo($sprite['origin_y']); ?>,
        <?php echo($sprite['source_data_width']); ?>,
        <?php echo($sprite['source_data_height']); ?>,
        <?php echo($sprite['longest_right_end']); ?>,

        <?php echo(exportArrayContent($sprite['words'], 'uint16_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_0'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_1'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_2'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_3'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_4'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_5'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_6'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_7'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_8'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_9'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_10'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_11'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_12'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_13'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_14'], 'uint8_t')); ?>,
        <?php echo(exportArrayContent($sprite['skew_15'], 'uint8_t')); ?>
    }<?php if ($key !== array_key_last($exportedSprites)) { ?>,<?php } ?>
    <?php } ?>
};

