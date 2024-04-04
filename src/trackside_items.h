#ifndef __TRACKSIDE_ITEMS_H
#define __TRACKSIDE_ITEMS_H

#include <inttypes.h>

#define SCENERY_TYPE_ROUND_TREE 0
#define SCENERY_TYPE_MICHELIN (56 + 24)
#define SCENERY_TYPE_GITANES (64 + 24)
#define SCENERY_TYPE_MOTO_JOURNAL (72 + 24)
#define SCENERY_TYPE_TOTAL (80 + 24)
#define SCENERY_TYPE_LUCAS (88 + 24)
#define SCENERY_TYPE_LAMPPOST (96 + 24)
#define SCENERY_TYPE_LEFT_ARROW (104 + 24)
#define SCENERY_TYPE_RIGHT_ARROW (112 + 24)
#define SCENERY_TYPE_KONAMI (120 + 24)
#define SCENERY_TYPE_BP (128 + 24)
#define SCENERY_TYPE_TALL_TREE (136 + 24)
#define SCENERY_TYPE_SHORT_TREE (144 + 24)
#define SCENERY_TYPE_RED_CAR_FLIP (152 + 24)

struct TracksideItem {
    uint16_t type;
    int32_t xpos;
    int32_t track_position;
};

extern struct TracksideItem trackside_items[];

#endif
