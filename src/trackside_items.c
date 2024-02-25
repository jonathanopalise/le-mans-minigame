#include "trackside_items.h"

extern struct TracksideItem trackside_items[];

struct TracksideItem trackside_items[] = (struct TracksideItem[]) {
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -400,
        .track_position = 5000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 400,
        .track_position = 19000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -400,
        .track_position = 15000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 400,
        .track_position = 20000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -400,
        .track_position = 25000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 400,
        .track_position = 30000
    },
    {
        .type = SCENERY_TYPE_TERMINATE,
        .xpos = 400,
        .track_position = 30000
    }
};
 
