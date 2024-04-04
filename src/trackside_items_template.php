#include "../trackside_items.h"

extern struct TracksideItem trackside_items[];

struct TracksideItem trackside_items[] = (struct TracksideItem[]) {
    <?php foreach ($tracksideItems as $item) { ?>
    {
        .type = <?php echo($item['type']); ?>,
        .xpos = <?php echo($item['xpos']); ?>,
        .track_position = <?php echo($item['track_position']); ?>
    },
    <?php } ?>
    {
        .type = 0,
        .xpos = 0,
        .track_position = 0x7fffffff
    }
};
 
