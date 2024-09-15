#ifndef __TRACKSIDE_ITEMS_H
#define __TRACKSIDE_ITEMS_H

#include <inttypes.h>

#define SCENERY_TYPE_ROUND_TREE 0
#define SCENERY_TYPE_MICHELIN (56 + 28)
#define SCENERY_TYPE_GITANES (64 + 28)
#define SCENERY_TYPE_MOTO_JOURNAL (72 + 28)
#define SCENERY_TYPE_TOTAL (80 + 28)
#define SCENERY_TYPE_LUCAS (88 + 28)
#define SCENERY_TYPE_LAMPPOST (96 + 28)
#define SCENERY_TYPE_LEFT_ARROW (104 + 28)
#define SCENERY_TYPE_RIGHT_ARROW (112 + 28)
#define SCENERY_TYPE_KONAMI (120 + 28)
#define SCENERY_TYPE_BP (128 + 28)
#define SCENERY_TYPE_TALL_TREE (136 + 28)
#define SCENERY_TYPE_SHORT_TREE (144 + 28)
#define SCENERY_TYPE_RED_CAR_FLIP (152 + 28)
#define SCENERY_TYPE_CHECKPOINT_GANTRY (158 + 28)
#define SCENERY_TYPE_CHECKPOINT_TOP (166 + 28)
#define SCENERY_TYPE_LEFT_ARROW_YELLOW 217
#define SCENERY_TYPE_RIGHT_ARROW_YELLOW 225

struct TracksideItem {
    uint16_t type;
    int32_t xpos;
    int32_t track_position;
};

extern struct TracksideItem trackside_items[];

#endif
