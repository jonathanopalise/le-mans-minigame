#include "trackside_items_process.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "trackside_items.h"
#include "display_list.h"

struct TracksideItem *current_nearest_trackside_item;

void trackside_items_process_init()
{
    current_nearest_trackside_item = trackside_items;
}

void trackside_items_process()
{
    int8_t trackside_item_scanline_index;
    struct RoadScanline *road_scanline;
    int16_t sprite_index;
    int16_t screen_xpos;

    // TODO: player_car_track_position should be camera_track_position
    // we'll need to derive player_car_track_position for collision calcs

    while (player_car_track_position > current_nearest_trackside_item->track_position) {
        current_nearest_trackside_item++;
        if (current_nearest_trackside_item->track_position == 0xffffffff) {
            current_nearest_trackside_item = trackside_items;
            break;
        }
    }

    struct TracksideItem *current_trackside_item = current_nearest_trackside_item;
    int32_t current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;

    while (current_trackside_item_player_relative_position >=0 && current_trackside_item_player_relative_position < 35000) {
        //if (current_trackside_item_player_relative_position >= 0) {
            trackside_item_scanline_index = distance_to_scanline_lookup[current_trackside_item_player_relative_position];
            if (trackside_item_scanline_index != -1) {
                road_scanline = &road_scanlines[trackside_item_scanline_index];
                sprite_index = (current_trackside_item->type + 7) - (trackside_item_scanline_index / 6);
                if (sprite_index < current_trackside_item->type) {
                    sprite_index = current_trackside_item->type;
                }

                if (current_trackside_item->xpos > 0) {
                    screen_xpos = (((road_scanline->current_logical_xpos + road_scanline->object_xpos_add_values[current_trackside_item->xpos]) >> 16));
                } else {
                    screen_xpos = (((road_scanline->current_logical_xpos - road_scanline->object_xpos_add_values[-current_trackside_item->xpos]) >> 16));
                }
                screen_xpos += 160;

                display_list_add_sprite(
                    &sprite_definitions[sprite_index],
                    screen_xpos,
                    (119 + trackside_item_scanline_index)
                );
            }

        //}

        current_trackside_item++;
        current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;
        /*if (current_trackside_item->track_position == 0xffffffff) {
            break;
        }*/
    }
}

