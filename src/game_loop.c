#include <string.h>
#include <stdio.h>
#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
#include "road_movement.h"
#include "mountains_render.h"
#include "mountains_render_fast.h"
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
#include "stars_fast.h"
#include "natfeats.h"
#include "random.h"
#include "speedometer.h"
#include "screen_transition.h"
#include "play_sound.h"
#include "title_sound.h"
#include "new_title_screen_graphics.h"
#include <mint/osbind.h>
#include <mint/sysbind.h>

#define GAME_OVER_DEFINITION_OFFSET 234
#define GET_READY_DEFINITION_OFFSET 235
#define GO_DEFINITION_OFFSET 236
#define TIME_EXTEND_DEFINITION_OFFSET 248
#define SHADOW_DEFINITION_OFFSET 267
#define DEMO_DEFINITION_OFFSET 268

#define GAME_STATE_GLOBAL_INIT 0
#define GAME_STATE_TITLE_SCREEN_INIT 1
#define GAME_STATE_TITLE_SCREEN_LOOP 2
#define GAME_STATE_IN_GAME_INIT 3
#define GAME_STATE_IN_GAME_LOOP 4
#define GAME_STATE_TITLE_SCREEN_EXIT_TO_GAME_TRANSITION 5
#define GAME_STATE_GAME_OVER_EXIT_TRANSITION 6
#define GAME_STATE_TITLE_SCREEN_ENTRY_TRANSITION 7
#define GAME_STATE_IN_DEMO_LOOP 8
#define GAME_STATE_IN_DEMO_INIT 9
#define GAME_STATE_TITLE_SCREEN_EXIT_TO_DEMO_TRANSITION 10

uint16_t game_state;
uint16_t joy_fire;
volatile uint16_t waiting_for_vbl;
uint16_t transition_offset;
uint16_t player_car_sprite_definition_offset;
uint16_t player_car_visible;

static void global_init()
{
    /*FILE *f;
    music_data_address = 1048576;
    f = fopen("a:\\jracer.snd", "rb");
    fread(music_data_address, 262144, 1, f);
    fclose(f);*/

#ifdef __NATFEATS_DEBUG
    if (!nf_init()) {
        while (1==1) {}
    }

    nf_print("hello from lemans!");

    uint32_t bob = malloc(-1);
            snprintf(
                nf_strbuf,
                256,
                "free memory starts at: %d, physbase is: %d\n",
                bob,
                Physbase()
            );
    nf_print(nf_strbuf);
#endif


    init_stars();
    lookups_init();
    relocate_sprites();
    mixer_init();
    initialise();
    hud_init();
    hardware_playfield_global_init();
    memset(hardware_playfields[0].buffer, 0, 32000);

    game_state = GAME_STATE_TITLE_SCREEN_INIT;

}

static void title_screen_init()
{
    FILE *f;
    uint32_t bytes_read;

    title_sound_address = hardware_playfields[2].buffer;

    f = fopen("\\titlefx.raw", "rb");
    bytes_read = fread(title_sound_address, 65536, 1, f);
    fclose(f);

    title_sound_length = 57573;

    vbl_title_screen_palette_source = new_title_screen_graphics+16000;
    //memcpy((void *)0xffff8240, title_screen_palette, 32);

    waiting_for_vbl = 1;
    game_state = GAME_STATE_TITLE_SCREEN_LOOP;

    uint32_t visible_buffer_address = hardware_playfields[0].buffer;
    //memcpy((void *)visible_buffer_address, title_screen_graphics, 32000);

    hardware_playfield_set_visible_address(visible_buffer_address);

    transition_offset = 0;
    game_state = GAME_STATE_TITLE_SCREEN_ENTRY_TRANSITION;
    race_ticks = 0;
}

static void title_screen_loop()
{
    //update_joy();

    joy_fire = joy1 >> 7 & 1;
    if (joy_fire == 1) {
        *((uint8_t *)0xffff8901) = 0;
        transition_offset = 0;
        game_state = GAME_STATE_TITLE_SCREEN_EXIT_TO_GAME_TRANSITION;
    }

    waiting_for_vbl = 1;
    while (waiting_for_vbl) {}

    race_ticks++;
    if (race_ticks > 50*6) {
        transition_offset = 0;
        game_state = GAME_STATE_TITLE_SCREEN_EXIT_TO_DEMO_TRANSITION;
    }
}

static void entry_transition_loop(uint16_t next_game_state, uint8_t *source_buffer)
{
    uint16_t count = 8;
    if (transition_offset == 256) {
        count = 4;
    }

    for (uint16_t index = 0; index < count; index++) {
        screen_transition_copy_block(source_buffer, hardware_playfields[0].buffer, transition_offset);
        transition_offset++;
    }

    waiting_for_vbl = 1;
    while (waiting_for_vbl) {}

    if (transition_offset == 260) {
        game_state = next_game_state;
    }
}

static void exit_transition_loop(uint16_t next_game_state)
{
    uint16_t count = 8;
    if (transition_offset == 256) {
        count = 4;
    }

    for (uint16_t index = 0; index < count; index++) {
        screen_transition_erase_block(hardware_playfields[0].buffer, transition_offset);
        transition_offset++;
    }

    waiting_for_vbl = 1;
    while (waiting_for_vbl) {}

    if (transition_offset == 260) {
        game_state = next_game_state;
    }
}

static void title_screen_entry_transition_loop()
{
    entry_transition_loop(GAME_STATE_TITLE_SCREEN_LOOP, new_title_screen_graphics);

    if (transition_offset == 260) {
        title_sound_play();
    }
}

static void title_screen_exit_to_game_transition_loop()
{
    exit_transition_loop(GAME_STATE_IN_GAME_INIT);
}

static void title_screen_exit_to_demo_transition_loop()
{
    exit_transition_loop(GAME_STATE_IN_DEMO_INIT);
}

static void game_over_exit_transition_loop()
{
    exit_transition_loop(GAME_STATE_TITLE_SCREEN_INIT);
}

static void in_game_init()
{
    waiting_for_vbl = 1;
    while (waiting_for_vbl) {}
    memset(0xffff8240, 0, 32);

    speedometer_init();
    time_of_day_init();
    time_of_day_update();
    music_init();
    hud_game_init();

    hardware_playfield_init();

    road_corners_init();
    player_car_initialise();
    opponent_cars_init();
    display_list_init();
    trackside_items_process_init();
    road_render_init();
    init_random();

    race_ticks = 0;
    time_extend_countdown = 0;
    passed_start_line = 0;

    game_state = GAME_STATE_IN_GAME_LOOP;
}

static void in_demo_init()
{
    waiting_for_vbl = 1;
    while (waiting_for_vbl) {}
    memset(0xffff8240, 0, 32);

    time_of_day_init();
    time_of_day_update();
    music_init();
    hud_game_init();

    hardware_playfield_init();

    road_corners_init();
    player_car_initialise();
    opponent_cars_init();
    display_list_init();
    trackside_items_process_init();
    road_render_init();
    init_random();

    race_ticks = 0;
    time_extend_countdown = 0;
    passed_start_line = 0;

    game_state = GAME_STATE_IN_DEMO_LOOP;
}

static void in_game_loop_core()
{

    time_of_day_update();
    player_car_handle_inputs();
    opponent_cars_update();
    trackside_items_update_nearest();
    detect_collisions();
    road_corners_update();
    road_render();

    if (time_of_day_is_night) {
        drawing_playfield->mountains_scroll_pixels = -1;
    } else {
        //mountains_render();
        mountains_render_fast();
    }

    if (drawing_playfield->stars_drawn) {
        erase_stars_fast(drawing_playfield->star_block_offsets, drawing_playfield->buffer);
        drawing_playfield->stars_drawn = 0;
    }

    hardware_playfield_erase_sprites();
    trackside_items_process();
    opponent_cars_process();

    player_car_sprite_definition_offset = player_car_get_sprite_definition();

    if (time_of_day_is_night) {
        draw_stars_fast(drawing_playfield->star_block_offsets, drawing_playfield->buffer);
        drawing_playfield->stars_drawn = 1;
    }
    drawing_playfield->stars_drawn = time_of_day_is_night;

    player_car_visible = (player_car_invincible_countdown == 0 || player_car_invincible_countdown & 2) && player_car_state != PLAYER_CAR_STATE_RETURN_TO_TRACK;
    if (player_car_visible) {
        if (player_car_altitude > 0) {
            display_list_add_sprite(
                SHADOW_DEFINITION_OFFSET,
                160,
                194
            );
        } else {
            display_list_add_sprite(
                player_car_sprite_definition_offset,
                160,
                194 - ((player_car_altitude >> 8) + (player_car_speed > 1190 ? race_ticks & 1 : 0))
            );
        }
    }

    display_list_execute();

    if (player_car_visible && player_car_altitude > 0) {
        struct SpritePlacement player_car_sprite_placement = {
            player_car_sprite_definition_offset,
            160,
            194 - ((player_car_altitude >> 8) + (player_car_speed == 1200 ? race_ticks & 1 : 0))
        };

        hardware_playfield_draw_sprite(&player_car_sprite_placement);
    }
}

static void in_demo_loop()
{
    in_game_loop_core();

    uint16_t joy_fire = joy1 >> 7 & 1;
    if (race_ticks > 23*50 || joy_fire) {
        uint32_t visible_buffer_address = hardware_playfields[0].buffer;
        hardware_playfield_set_visible_address(visible_buffer_address);

        music_stop();
        transition_offset = 0;
        game_state = GAME_STATE_GAME_OVER_EXIT_TRANSITION;
    } else {
        struct SpritePlacement demo_sprite_placement = {DEMO_DEFINITION_OFFSET, 160, 106};
        hardware_playfield_draw_sprite(&demo_sprite_placement);

        hardware_playfield_frame_complete();
    }
}

static void in_game_loop()
{
    //update_joy();

    speedometer_update();

    hud_reduce_time();
    if (hud_update_score_digits()) {
        hardware_playfield_hud_redraw_required();
    }

    in_game_loop_core();

    if (frames_since_game_over) {
        {
            struct SpritePlacement game_over_sprite_placement = {GAME_OVER_DEFINITION_OFFSET, 160, 127};
            hardware_playfield_draw_sprite(&game_over_sprite_placement);
        }
    } else {
        if (race_ticks <= 200) {
            switch (race_ticks) {
                case 200:
                    play_sound(SOUND_ID_START_BEEP_HIGH);
                    break;
                case 150:
                case 100:
                    play_sound(SOUND_ID_START_BEEP_LOW);
            }
        }

        if (race_ticks > 30 && race_ticks < 150) {
            struct SpritePlacement get_ready_sprite_placement = {GET_READY_DEFINITION_OFFSET, 160, 127};
            hardware_playfield_draw_sprite(&get_ready_sprite_placement);
        } else if (race_ticks > 200 && race_ticks < 320) {
            struct SpritePlacement go_sprite_placement = {GO_DEFINITION_OFFSET, 160, 119};
            hardware_playfield_draw_sprite(&go_sprite_placement);
        } else if (time_extend_countdown > 0) {
            if (time_extend_countdown & 8) {
                if (((time_extend_countdown + 1) & 8) == 0) {
                    play_sound(SOUND_ID_CHECKPOINT_BEEP);
                }
                struct SpritePlacement time_extend_sprite_placement = {TIME_EXTEND_DEFINITION_OFFSET, 160, 109};
                hardware_playfield_draw_sprite(&time_extend_sprite_placement);
            }
            time_extend_countdown--;
        }
    }

    if (drawing_playfield->hud_redraw_required) {
        hardware_playfield_update_digits();
        drawing_playfield->hud_redraw_required = 0;
    }
    speedometer_draw();

    if (frames_since_game_over > 180) {
        music_stop();

        uint32_t visible_buffer_address = hardware_playfields[0].buffer;
        hardware_playfield_set_visible_address(visible_buffer_address);

        transition_offset = 0;
        game_state = GAME_STATE_GAME_OVER_EXIT_TRANSITION;
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
            case GAME_STATE_TITLE_SCREEN_ENTRY_TRANSITION:
                title_screen_entry_transition_loop();
                break;
            case GAME_STATE_TITLE_SCREEN_LOOP:
                title_screen_loop();
                break;
            case GAME_STATE_TITLE_SCREEN_EXIT_TO_GAME_TRANSITION:
                title_screen_exit_to_game_transition_loop();
                break;
            case GAME_STATE_TITLE_SCREEN_EXIT_TO_DEMO_TRANSITION:
                title_screen_exit_to_demo_transition_loop();
                break;
            case GAME_STATE_IN_GAME_INIT:
                in_game_init();
                break;
            case GAME_STATE_IN_DEMO_INIT:
                in_demo_init();
                break;
            case GAME_STATE_IN_GAME_LOOP:
                in_game_loop();
                break;
            case GAME_STATE_IN_DEMO_LOOP:
                in_demo_loop();
                break;
            case GAME_STATE_GAME_OVER_EXIT_TRANSITION:
                game_over_exit_transition_loop();
                break;
        }
    }
}

uint16_t is_demo()
{
    return game_state == GAME_STATE_IN_DEMO_LOOP;
}
