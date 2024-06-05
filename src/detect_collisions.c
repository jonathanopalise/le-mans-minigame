#include "detect_collisions.h"
#include "player_car.h"
#include "trackside_items_process.h"
#include "trackside_items.h"
#include "road_geometry.h"
#include "natfeats.h"

void detect_collisions()
{
    struct TracksideItem *current_trackside_item = current_nearest_trackside_item;

    int32_t current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;
    int32_t current_trackside_item_logical_xpos;

    struct RoadScanline *player_scanline = &road_scanlines[75];

    while (current_trackside_item_player_relative_position < 500) {
        if (current_trackside_item->xpos > 0) {
            current_trackside_item_logical_xpos = -player_scanline->object_xpos_add_values[current_trackside_item->xpos];
        } else {
            current_trackside_item_logical_xpos = player_scanline->object_xpos_add_values[-current_trackside_item->xpos];
        }

        if (current_trackside_item->track_position > (player_car_track_position - 400) &&
            current_trackside_item->track_position < (player_car_track_position + 400) &&
            current_trackside_item_logical_xpos > (player_car_logical_xpos - 4000000) && 
            current_trackside_item_logical_xpos < (player_car_logical_xpos + 4000000)
        ) {
            player_car_crash();
        }

        current_trackside_item++;
        current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;
    }
 }
