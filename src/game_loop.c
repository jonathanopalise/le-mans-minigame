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
#include "random.h"

#define GAME_OVER_DEFINITION_OFFSET 202

#define GAME_STATE_GLOBAL_INIT 0
#define GAME_STATE_TITLE_SCREEN_INIT 1
#define GAME_STATE_TITLE_SCREEN_LOOP 2
#define GAME_STATE_IN_GAME_INIT 3
#define GAME_STATE_IN_GAME_LOOP 4

uint16_t game_state;

static void global_init()
{
#ifdef __NATFEATS_DEBUG
    if (!nf_init()) {
        while (1==1) {}
    }

    nf_print("hello from lemans!");
#endif

    lookups_init();
    relocate_sprites();
    mixer_init();
    initialise();

    game_state = GAME_STATE_IN_GAME_INIT;
}

static void title_screen_init()
{
}

static void title_screen_loop()
{
}

static void in_game_init()
{
    music_init();
    hud_init();
    hardware_playfield_init();
    road_corners_init();
    player_car_initialise();
    opponent_cars_init();
    display_list_init();
    trackside_items_process_init();
    time_of_day_init();
    road_render_init();
    init_random();

    *((volatile uint16_t *)0xffff8240) = 0x0;
    game_state = GAME_STATE_IN_GAME_LOOP;
}

static void in_game_loop()
{
    static uint16_t player_car_sprite_definition_offset;
    static uint16_t is_night;

    //music_tick();
    is_night = time_of_day_is_night();

    time_of_day_update();
    hud_reduce_time();
    hud_update_digits();
    player_car_handle_inputs();
    opponent_cars_update();
    trackside_items_update_nearest();
    detect_collisions();
    road_corners_update();
    road_render();

    if (!is_night) {
        mountains_render();
    }

    if (drawing_playfield->stars_drawn) {
        erase_stars();
        drawing_playfield->stars_drawn = 0;
    }

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

    if (is_night) {
        draw_stars();
        drawing_playfield->stars_drawn = 1;
    }
    drawing_playfield->stars_drawn = is_night;

    display_list_execute();

    if (frames_since_game_over) {
        {
            hardware_playfield_draw_sprite(
                &sprite_definitions[GAME_OVER_DEFINITION_OFFSET],
                160,
                127
            );
        }
    }

    if (frames_since_game_over > 180) {
        music_stop();
        game_state = GAME_STATE_IN_GAME_INIT;
    } else {
        hardware_playfield_frame_complete();
    }
}

void game_loop()
{
    game_state = GAME_STATE_GLOBAL_INIT;

    while (1) {
        switch (game_state) {
            case GAME_STATE_GLOBAL_INIT:
                global_init();
                break;
            case GAME_STATE_TITLE_SCREEN_INIT:
                title_screen_init();
                break;
            case GAME_STATE_TITLE_SCREEN_LOOP:
                title_screen_loop();
                break;
            case GAME_STATE_IN_GAME_INIT:
                in_game_init();
                break;
            case GAME_STATE_IN_GAME_LOOP:
                in_game_loop();
                break;
        }
    }
}


