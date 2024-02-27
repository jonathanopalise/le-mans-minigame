#include "trackside_items.h"

extern struct TracksideItem trackside_items[];

struct TracksideItem trackside_items[] = (struct TracksideItem[]) {
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 5000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 60000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 10000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 140000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = -255,
        .track_position = 190000
    },
    {
        .type = SCENERY_TYPE_ROUND_TREE,
        .xpos = 255,
        .track_position = 220000
    },
    {
        .type = SCENERY_TYPE_TERMINATE,
        .xpos = 255,
        .track_position = 300000
    }
};
 
