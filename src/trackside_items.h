#ifndef __TRACKSIDE_ITEMS_H
#define __TRACKSIDE_ITEMS_H

#include <inttypes.h>

#define SCENERY_TYPE_TERMINATE 0
#define SCENERY_TYPE_ROUND_TREE 1
#define SCENERY_TYPE_PINE_TREE 2
#define SCENERY_TYPE_DEAD_TREE 3
#define TRACKSIDE_ITEM_COUNT 7

struct TracksideItem {
    uint16_t type;
    int32_t xpos;
    uint32_t track_position;
};

extern struct TracksideItem trackside_items[];

#endif
