#ifndef __TRACKSIDE_ITEMS_H
#define __TRACKSIDE_ITEMS_H

#include <inttypes.h>

#define SCENERY_TYPE_ROUND_TREE 0
#define SCENERY_TYPE_MICHELIN 56
#define SCENERY_TYPE_GITANES 64
#define SCENERY_TYPE_MOTO_JOURNAL 72
#define SCENERY_TYPE_TOTAL 80
#define SCENERY_TYPE_LUCAS 88
#define SCENERY_TYPE_LAMPPOST 96

struct TracksideItem {
    uint16_t type;
    int32_t xpos;
    uint32_t track_position;
};

extern struct TracksideItem trackside_items[];

#endif
