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
#include "trackside_items_process.h"
#include "display_list.h"
#include "opponent_cars.h"
#include "draw_compiled_sprite.h"
#include "time_of_day.h"
#include "natfeats.h"

void game_loop()
{
    uint16_t player_car_sprite_definition_offset;
    uint32_t quarter_hour_countdown = 60*1;
    uint32_t time_of_day_offset = 0;

    *((volatile uint16_t *)0xffff8240) = 0x0;

    if (!nf_init()) {
        while (1==1) {}
    }

    nf_print("hello from lemans!");

    hardware_playfield_init();
    initialise();
    road_corners_init();
    player_car_initialise();
    opponent_cars_init();
    display_list_init();
    trackside_items_process_init();
    int16_t xpos = 160;
    //*((volatile uint16_t *)0xffff8242) = 0x40;
    //*((volatile uint16_t *)0xffff8244) = 0x777;
    //*((volatile uint16_t *)0xffff8246) = 0x222;

    uint16_t *src = &time_of_day[time_of_day_offset];
    uint16_t *dest = &sky_gradient;
    *dest++ = *src++; // 2 colours for mountains
    *dest++ = *src++;
    *dest++ = *src++; // remaining colours for sky
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;

    dest = &scenery_colours;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;

    dest = &ground_colours;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;

    while (1) {
        quarter_hour_countdown--;
        if (quarter_hour_countdown == 0) {

            quarter_hour_countdown = 60*1;
            time_of_day_offset += 29;
            if (time_of_day_offset == 29*96) {
                time_of_day_offset = 0;
            }

            src = &time_of_day[time_of_day_offset];
            dest = &sky_gradient;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;

            dest = &scenery_colours;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;

            dest = &ground_colours;
            *dest++ = *src++;
            *dest++ = *src++;
            *dest++ = *src++;
        }

        //*((volatile uint16_t *)0xffff8240) = 0x0;
        player_car_handle_inputs();
        opponent_cars_update();
        //*((volatile uint16_t *)0xffff8240) = 0x740; // yellow - road geometry calculations
        road_corners_update();
        //*((volatile uint16_t *)0xffff8240) = 0x044; // turqoise - render mountains
        mountains_render();
        //*((volatile uint16_t *)0xffff8240) = 0x770; // yellow - render road
        road_render();
        //*((volatile uint16_t *)0xffff8240) = 0x004; // blue - erase sprites
        hardware_playfield_erase_sprites();
        //*((volatile uint16_t *)0xffff8240) = 0x040; // green - add trackside items to display list
        trackside_items_process();
        opponent_cars_process();
        //*((volatile uint16_t *)0xffff8240) = 0x400; // red

        player_car_sprite_definition_offset = 8;
        if (player_car_speed > 0) {
            if (player_car_steering <= -250) {
                player_car_sprite_definition_offset += 16;
            } else if (player_car_steering >= 250) {
                player_car_sprite_definition_offset += 8;
            }
        }

        display_list_add_sprite(
            &sprite_definitions[player_car_sprite_definition_offset],
            160,
            194
        );

        //*((volatile uint16_t *)0xffff8240) = 0x333; // grey

        display_list_execute();
        hardware_playfield_frame_complete();


        //draw_compiled_sprite(hidden_hardware_playfield->buffer);

        //*((volatile uint16_t *)0xffff8240) = 0x070; // green

        //waiting_for_vbl = 1;
        //while (waiting_for_vbl) {}
        //hardware_playfield_handle_vbl();

        //*((volatile uint16_t *)0xffff8240) = 0x400; // red
    }
}
