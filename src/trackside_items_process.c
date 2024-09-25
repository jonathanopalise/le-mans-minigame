#include "trackside_items_process.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "trackside_items.h"
#include "display_list.h"
#include "lookups.h"

struct TracksideItem *current_nearest_trackside_item;

void trackside_items_process_init()
{
    current_nearest_trackside_item = trackside_items;
}

void trackside_items_update_nearest()
{
    while (camera_track_position > current_nearest_trackside_item->track_position) {
        current_nearest_trackside_item++;
        if (current_nearest_trackside_item->track_position == 0x7fffffff) {
            // so once we get to this point, the nearest trackside item is behind the player!
            // this is why we need the > 0 check in the loop below
            current_nearest_trackside_item = trackside_items;
            break;
        }
    }
}

void trackside_items_process()
{
    int8_t trackside_item_scanline_index;
    struct RoadScanline *road_scanline;
    int16_t sprite_index;
    int16_t screen_xpos;

    // TODO: camera_track_position should be camera_track_position
    // we'll need to derive camera_track_position for collision calcs

    struct TracksideItem *current_trackside_item = current_nearest_trackside_item;
    int32_t current_trackside_item_camera_relative_position = current_trackside_item->track_position - camera_track_position;

    if (current_trackside_item_camera_relative_position < 0) {
        return;
    }

    while (current_trackside_item_camera_relative_position < 40000) {
        trackside_item_scanline_index = distance_to_scanline_lookup[current_trackside_item_camera_relative_position];
        if (trackside_item_scanline_index != -1) {
            road_scanline = road_scanline_pointers[trackside_item_scanline_index];

            if (current_trackside_item->type == SCENERY_TYPE_LAMPPOST) {
                sprite_index = current_trackside_item->type + road_scanline->lamppost_sprite_index_adjust;
            } else {
                sprite_index = current_trackside_item->type + road_scanline->sprite_index_adjust;
            }

            if (current_trackside_item->xpos > 0) {
                screen_xpos = (((road_scanline->current_logical_xpos + road_scanline->object_xpos_add_values[current_trackside_item->xpos]) >> 16));
            } else {
                screen_xpos = (((road_scanline->current_logical_xpos - road_scanline->object_xpos_add_values[-current_trackside_item->xpos]) >> 16));
            }
            screen_xpos += 160;

            display_list_add_sprite(
                // TODO: find way to replace multiply with lookup table
                //&sprite_definitions[sprite_index],
                sprite_definition_pointers[sprite_index],
                screen_xpos,
                (119 + trackside_item_scanline_index)
            );
        }

        current_trackside_item++;
        current_trackside_item_camera_relative_position = current_trackside_item->track_position - camera_track_position;
        /*if (current_trackside_item->track_position == 0xffffffff) {
            break;
        }*/
    }
}

