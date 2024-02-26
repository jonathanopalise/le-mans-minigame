#include "../sprite_definitions.h"

struct SpriteDefinition sprite_definitions[] = {
    <?php foreach ($exportedSprites as $key => $sprite) { ?>
        {
        <?php echo($sprite['origin_x']); ?>,
        <?php echo($sprite['origin_y']); ?>,
        <?php echo($sprite['source_data_width']); ?>,
        <?php echo($sprite['source_data_height']); ?>,
        ( uint16_t[] ){ <?php echo implode(', ', $sprite['words']); ?> }
    }<?php if ($key !== array_key_last($exportedSprites)) { ?>,<?php } ?>
    <?php } ?>
};

