#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
#include "vbl_handler.h"
#include "road_movement.h"
#include "mountains_render.h"
#include "road_render.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "trackside_items.h"
#include "display_list.h"

struct TracksideItem* pointers_to_trackside_items[] = {
    &trackside_items[4],
    &trackside_items[6],
    &trackside_items[5],
};

void game_loop()
{
    hardware_playfield_init();
    initialise();
    player_car_initialise();
    display_list_init();
    int16_t xpos = 0;
    struct RoadScanline *road_scanline;
    struct TracksideItem *current_trackside_item;
    uint32_t trackside_item_relative_position;
    int16_t trackside_item_scanline_index;
    int16_t sprite_index;

    *((volatile uint16_t *)0xffff8242) = 0x40;
    *((volatile uint16_t *)0xffff8244) = 0x777;
    *((volatile uint16_t *)0xffff8246) = 0x222;

    while (1) {
        player_car_handle_inputs();
        *((volatile uint16_t *)0xffff8240) = 0x440; // yellow
        road_corners_update();
        *((volatile uint16_t *)0xffff8240) = 0x044; // green/blue combination
        //road_movement_update();
        //*((volatile uint16_t *)0xffff8240) = 0x404; // purple
        mountains_render();
        road_render();
        *((volatile uint16_t *)0xffff8240) = 0x004; // blue
        hardware_playfield_erase_sprites();

        current_trackside_item = trackside_items;        
        for (uint16_t trackside_item_index = 0; trackside_item_index < TRACKSIDE_ITEM_COUNT; trackside_item_index++) {
            trackside_item_relative_position = current_trackside_item->track_position - player_car_track_position;
            if (trackside_item_relative_position > 0 && trackside_item_relative_position < 45000) {
                trackside_item_scanline_index = distance_to_scanline_lookup[trackside_item_relative_position];
                if (trackside_item_scanline_index != -1) {
                    road_scanline = &road_scanlines[trackside_item_scanline_index];
                    sprite_index = 7 - (trackside_item_scanline_index / 6);
                    if (sprite_index < 0) {
                        sprite_index = 0;
                    }

                    display_list_add_sprite(
                        &sprite_definitions[sprite_index],
                        160 + (((road_scanline->current_logical_xpos + road_scanline->logical_xpos_add_values[160]) >> 16)),
                        (119 + trackside_item_scanline_index)
                    );
                }
            }

            current_trackside_item++;
        }

        display_list_add_sprite(
            &sprite_definitions[8],
            160,
            194
        );

        display_list_execute();

        *((volatile uint16_t *)0xffff8240) = 0x040; // green

        waiting_for_vbl = 1;
        while (waiting_for_vbl) {}

        xpos++;
        if (xpos > 320) {
            xpos = 0;
        }
        hardware_playfield_handle_vbl();

        *((volatile uint16_t *)0xffff8240) = 0x400; // red
    }
}
