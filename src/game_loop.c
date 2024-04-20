#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
#include "vbl_handler.h"
#include "road_movement.h"
#include "mountains_render.h"
#include "road_render.h"
#include "road_render_fast.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "trackside_items.h"
#include "trackside_items_process.h"
#include "display_list.h"
#include "opponent_cars.h"
#include "time_of_day_process.h"
#include "detect_collisions.h"
#include "natfeats.h"

void game_loop()
{
    uint16_t player_car_sprite_definition_offset;
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
    time_of_day_init();
    road_render_init();

    while (1) {
        time_of_day_update();
        player_car_handle_inputs();
        opponent_cars_update();
        trackside_items_update_nearest();
        detect_collisions();
        road_corners_update();
        mountains_render();
        road_render_fast();
        hardware_playfield_erase_sprites();
        trackside_items_process();
        opponent_cars_process();

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

        display_list_execute();
        hardware_playfield_frame_complete();
    }
}
