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
#include "time_of_day_process.h"
#include "detect_collisions.h"
#include "mixer_init.h"
#include "hud.h"
#include "music.h"
#include "relocate_sprites.h"
#include "lookups.h"
#include "stars.h"
#include "natfeats.h"

void game_loop()
{
    uint16_t player_car_sprite_definition_offset;
    *((volatile uint16_t *)0xffff8240) = 0x0;

    if (!nf_init()) {
        while (1==1) {}
    }

    nf_print("hello from lemans!");

    lookups_init();
    relocate_sprites();
    mixer_init();
    music_init();
    hardware_playfield_init();
    hud_init();
    initialise();
    road_corners_init();
    player_car_initialise();
    opponent_cars_init();
    display_list_init();
    trackside_items_process_init();
    time_of_day_init();
    road_render_init();

    while (1) {
        //music_tick();
        time_of_day_update();
        hud_reduce_time();
        hud_update_digits();
        player_car_handle_inputs();
        opponent_cars_update();
        trackside_items_update_nearest();
        detect_collisions();
        road_corners_update();
        mountains_render();
        road_render();
        erase_stars();
        hardware_playfield_erase_sprites();
        trackside_items_process();
        opponent_cars_process();

        hardware_playfield_update_digits();

        player_car_sprite_definition_offset = player_car_get_sprite_definition();

        if (player_car_invincible_countdown == 0 || player_car_invincible_countdown & 2) {
            display_list_add_sprite(
                &sprite_definitions[player_car_sprite_definition_offset],
                160,
                194 - (player_car_altitude >> 8)
            );
        }

        /*display_list_add_sprite(
            &sprite_definitions[SCENERY_TYPE_LAMPPOST],
            16,
            180
        );*/

        draw_stars();
        display_list_execute();
        hardware_playfield_frame_complete();
    }
}
