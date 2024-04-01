#include "../status_definitions.h"

struct StatusDefinition status_definitions[] = {
    <?php foreach ($exportedSprites as $key => $sprite) { ?>
        {
        <?php echo($sprite['source_data_width_pixels']); ?>,
        <?php echo($sprite['source_data_height']); ?>,
        ( uint16_t[] ){ <?php echo implode(', ', $sprite['words']); ?> }
    }<?php if ($key !== array_key_last($exportedSprites)) { ?>,<?php } ?>
    <?php } ?>
};

