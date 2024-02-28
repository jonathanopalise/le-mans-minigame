#include "trackside_items.h"

extern struct TracksideItem trackside_items[];

struct TracksideItem trackside_items[] = (struct TracksideItem[]) {
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 35000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 40000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 45000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 50000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 55000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 60000
    },
    {
        .type = SCENERY_TYPE_TERMINATE,
        .xpos = 255,
        .track_position = 65000
    }
};
 
