#include <string.h>
#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
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
#include "title_screen_graphics.h"
#include "natfeats.h"
#include "random.h"

#define GAME_OVER_DEFINITION_OFFSET 202
#define GET_READY_DEFINITION_OFFSET 203
#define GO_DEFINITION_OFFSET 204

#define GAME_STATE_GLOBAL_INIT 0
#define GAME_STATE_TITLE_SCREEN_INIT 1
#define GAME_STATE_TITLE_SCREEN_LOOP 2
#define GAME_STATE_IN_GAME_INIT 3
#define GAME_STATE_IN_GAME_LOOP 4

uint16_t game_state;
uint16_t joy_fire, last_joy_fire;
volatile uint16_t waiting_for_vbl;

uint16_t title_screen_palette[] = {0x0,0x5,0x888,0xd00,0x133,0xa3b,0x333,0x1b6,0x55d,0xec7,0xdde,0xfda,0xff0,0xeff,0x0,0x0};

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
    hardware_playfield_global_init();

    game_state = GAME_STATE_TITLE_SCREEN_INIT;
}

static void title_screen_init()
{
    memcpy((void *)0xffff8240, title_screen_palette, 32);

    waiting_for_vbl = 1;
    game_state = GAME_STATE_TITLE_SCREEN_LOOP;

    uint32_t visible_buffer_address = hardware_playfields[0].buffer;
    memcpy((void *)visible_buffer_address, title_screen_graphics, 32000);

    uint8_t address_high_byte = (uint8_t)((visible_buffer_address >> 16) & 0xff);
    uint8_t address_mid_byte = (uint8_t)((visible_buffer_address >> 8) & 0xff);
    uint8_t address_low_byte = (uint8_t)(visible_buffer_address & 0xff);

    *((volatile uint8_t *)0xffff8201) = address_high_byte;
    *((volatile uint8_t *)0xffff8203) = address_mid_byte;
    *((volatile uint8_t *)0xffff820d) = address_low_byte;

    *((volatile uint8_t *)0xffff8205) = address_high_byte;
    *((volatile uint8_t *)0xffff8207) = address_mid_byte;
    *((volatile uint8_t *)0xffff8209) = address_low_byte;

    game_state = GAME_STATE_TITLE_SCREEN_LOOP;
    last_joy_fire = joy_fire;
}

static void title_screen_loop()
{
    last_joy_fire = joy_fire;
    joy_fire = joy_data >> 7 & 1;
    if (joy_fire == 1) {
        game_state = GAME_STATE_IN_GAME_INIT;
    } else {
        while (waiting_for_vbl) {}
    }
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
    race_ticks = 0;
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

    if ((player_car_invincible_countdown == 0 || player_car_invincible_countdown & 2) && player_car_state != PLAYER_CAR_STATE_RETURN_TO_TRACK) {
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
    } else {
        if (race_ticks > 30 && race_ticks < 150) {
            hardware_playfield_draw_sprite(
                &sprite_definitions[GET_READY_DEFINITION_OFFSET],
                160,
                127
            );
        } else if (race_ticks > 200 && race_ticks < 320) {
            hardware_playfield_draw_sprite(
                &sprite_definitions[GO_DEFINITION_OFFSET],
                160,
                119
            );
        }
    }

    if (frames_since_game_over > 180) {
        music_stop();
        game_state = GAME_STATE_TITLE_SCREEN_INIT;
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


