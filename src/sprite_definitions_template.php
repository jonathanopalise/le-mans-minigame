#include "../sprite_definitions.h"

struct SpriteDefinition sprite_definitions[] = {
    <?php foreach ($exportedSprites as $key => $sprite) { ?>
        {
        <?php echo($sprite['origin_x']); ?>,
        <?php echo($sprite['origin_y']); ?>,
        <?php echo($sprite['source_data_width']); ?>,
        <?php echo($sprite['source_data_height']); ?>,
        ( uint16_t[] ){ <?php echo implode(', ', $sprite['words']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_0']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_1']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_2']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_3']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_4']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_5']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_6']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_7']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_8']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_9']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_10']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_11']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_12']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_13']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_14']); ?> },
        ( uint8_t[] ){ <?php echo implode(', ', $sprite['skew_15']); ?> }
    }<?php if ($key !== array_key_last($exportedSprites)) { ?>,<?php } ?>
    <?php } ?>
};

