#include <inttypes.h>
#include "player_car.h"
#include "initialise.h"
#include "hardware_playfield.h"
#include "natfeats.h"
#include "sprite_definitions.h"
#include "checkpoints.h"
#include "hud.h"
#include "play_sound.h"
#include "road_movement.h"
#include "game_loop.h"
#include <stdio.h>

#define PLAYER_CAR_STATE_SPIN_CRASH 1
#define PLAYER_CAR_STATE_FLIP_CRASH 2

struct TrackSegment *player_car_current_track_segment;
uint32_t player_car_current_track_segment_start_position;
uint32_t player_car_current_track_segment_end_position;
uint16_t player_car_current_track_segment_changes_applied;
int32_t camera_track_position;
int32_t total_distance_travelled;
int32_t old_player_car_track_position;
int32_t player_car_track_position;
int32_t player_car_logical_xpos;
int16_t player_car_speed;
int16_t engine_pitch;
int16_t player_car_steering;
uint16_t player_car_state;
uint16_t player_car_crash_countdown;
uint16_t player_car_invincible_countdown;
int32_t player_car_altitude;
int32_t player_car_altitude_change;
uint16_t player_car_flip_image_tracker;
uint16_t active_opponent_cars;
uint16_t opponent_lane_change_probability;
uint32_t race_ticks;
uint16_t time_extend_countdown;
uint16_t passed_start_line;

void player_car_initialise()
{
    player_car_altitude = 0;
    player_car_state = PLAYER_CAR_STATE_NORMAL;
    player_car_invincible_countdown = 0;
    player_car_current_track_segment = track_segments;
    player_car_current_track_segment_start_position = 0;
    player_car_current_track_segment_end_position = (player_car_current_track_segment->change_count << player_car_current_track_segment->change_frequency);
    player_car_current_track_segment_changes_applied = 0;
    camera_track_position = 0;
    total_distance_travelled = 0;
    player_car_logical_xpos = 0;
    player_car_speed = 400;
    engine_pitch = player_car_speed;
    player_car_steering = 0;
    player_car_track_position = 0;
    old_player_car_track_position = 0;
    active_opponent_cars = 1;
    opponent_lane_change_probability = 1;
    race_ticks = 0;
    time_extend_countdown = 0;
    passed_start_line = 0;
}

void player_car_handle_inputs()
{
    volatile uint8_t game_state_joy_data;

    if (player_car_state == PLAYER_CAR_STATE_SPIN_CRASH) {
        player_car_speed -= 4;
        if (player_car_flip_image_tracker < 23) {
            player_car_flip_image_tracker++;
        }
        if (player_car_speed < 0) {
            player_car_speed = 0;
            player_car_state = PLAYER_CAR_STATE_NORMAL;
            player_car_invincible_countdown = 180;
        }
    } else if (player_car_state == PLAYER_CAR_STATE_FLIP_CRASH) {
        player_car_altitude_change -= 60;
        player_car_altitude += player_car_altitude_change;
        if (player_car_altitude < 0) {
            if (player_car_altitude_change < -800) {
                play_sound(SOUND_ID_BOUNCE_LOUD);
            } else {
                play_sound(SOUND_ID_BOUNCE_QUIET);
            }
            player_car_altitude = 0;
            player_car_altitude_change =- (player_car_altitude_change >> 1);
        }

        if ((player_car_altitude > 0) || (player_car_flip_image_tracker != 0 && player_car_flip_image_tracker != 12)) {
            player_car_flip_image_tracker++;
            if (player_car_flip_image_tracker == 24) {
                player_car_flip_image_tracker = 0;
            }
        }

        if (player_car_altitude > 0) {
            player_car_speed -= 2;
        } else {
            player_car_speed -= 11;
        }

        if (player_car_speed < 0) {
            player_car_speed = 0;
            if (player_car_logical_xpos > 8000000 || player_car_logical_xpos < -8000000) {
                player_car_state = PLAYER_CAR_STATE_RETURN_TO_TRACK;
            } else {
                player_car_state = PLAYER_CAR_STATE_NORMAL;
                player_car_invincible_countdown = 180;
            }
        }
    } else if (player_car_state == PLAYER_CAR_STATE_RETURN_TO_TRACK) {
        if (player_car_logical_xpos > 8000000) {
            player_car_logical_xpos -= 150000;
        } else if (player_car_logical_xpos < -8000000) {
            player_car_logical_xpos += 150000;
        } else {
            player_car_state = PLAYER_CAR_STATE_NORMAL;
            player_car_invincible_countdown = 180;
        }
    } else {

        if (is_demo()) {
            game_state_joy_data = 1;
            if ((race_ticks > 260 && race_ticks < 276) || (race_ticks > 450 && race_ticks < 620)) {
                game_state_joy_data |= 8;
            } else if (race_ticks > 900 && race_ticks < 1040) {
                game_state_joy_data |= 4;
            }
        } else {
            game_state_joy_data = joy1;
        }

        /*uint16_t joy_up = game_state_joy_data & 1;
        uint16_t joy_down = game_state_joy_data & 2;
        uint16_t joy_left = game_state_joy_data & 4;
        uint16_t joy_right = game_state_joy_data & 8;
        uint16_t joy_fire = game_state_joy_data >> 7 & 1;*/

        //uint8_t game_state_joy_data = joy1;
        uint8_t joy_up = game_state_joy_data & 1;
        uint8_t joy_down = game_state_joy_data & 2;
        uint8_t joy_left = game_state_joy_data & 4;
        uint8_t joy_right = game_state_joy_data & 8;
        uint8_t joy_fire = game_state_joy_data >> 7 & 1;


        /*if (joy_left && joy_right) {
            while (1==1) {}
        }*/

        if (joy_fire) {
            /*snprintf(
                nf_strbuf,
                256,
                "track_position: %d\n",
                camera_track_position
            );*/
            //nf_print(nf_strbuf);
            /*snprintf(
                nf_strbuf,
                256,
                "logical_xpos: %d\n",
                player_car_logical_xpos
            );
            nf_print(nf_strbuf);*/
        }

        if (race_ticks > 200) {
            if ((joy_up || joy_fire) && !hud_is_time_up()) {
                player_car_speed += 3;
                if (player_car_speed > 1199) {
                    player_car_speed = 1199;
                }
            } else if (joy_down) {
                player_car_speed -= 14;
                if (player_car_speed < 0) {
                    player_car_speed = 0;
                }
            } else {
                player_car_speed -= 2;
                if (player_car_speed < 0) {
                    player_car_speed = 0;
                }
            }

            if (joy_left) {
                player_car_steering += 50;
                if (player_car_steering > 600) {
                    player_car_steering = 600;
                }
            }

            if (joy_right) {
                player_car_steering -= 50;
                if (player_car_steering < -600) {
                    player_car_steering = -600;
                }
            }

            if (!joy_left && !joy_right) {
                if (player_car_steering > 0) {
                    player_car_steering -= 20;
                    if (player_car_steering < 0) {
                        player_car_steering = 0;
                    }
                } else if (player_car_steering < 0) {
                    player_car_steering += 20;
                    if (player_car_steering > 0) {
                        player_car_steering = 0;
                    }
                }
            }
        }
    }

    if (player_car_altitude > 0) {
        engine_pitch += 6;
        if (engine_pitch > 1199) {
            engine_pitch = 1199;
        }
    } else {
        engine_pitch = player_car_speed;
    }
    camera_track_position += player_car_speed;
    total_distance_travelled += player_car_speed;

    if (!is_demo()) {
        hud_set_score(total_distance_travelled);
    }

    old_player_car_track_position = player_car_track_position;
    player_car_track_position = camera_track_position + PLAYER_CAR_DISTANCE;

    for (uint16_t index = 0; index < CHECKPOINTS_COUNT; index++) {
        if (old_player_car_track_position <= checkpoints[index] && player_car_track_position > checkpoints[index]) {
            //play_sound(3);
            hud_increase_time(28);
            if (passed_start_line) {
                time_extend_countdown = 128;
            }
            if (index == 0) {
                passed_start_line = 1;
                if (active_opponent_cars < 4) {
                    active_opponent_cars++;
                }
                if (opponent_lane_change_probability < 255) {
                    opponent_lane_change_probability+=1;
                }
            }
        }
    }

    player_car_logical_xpos += player_car_steering * player_car_speed;

    if (player_car_altitude == 0) {
        if ((player_car_logical_xpos > 11500000 || player_car_logical_xpos < -11500000)) {
            if (player_car_speed > 200) {
                player_car_speed -= 6;
            }
            if (player_car_speed < 0) {
                player_car_speed = 0;
            }
            hardware_playfield_shaking = (player_car_speed > 0);
        } else {
            hardware_playfield_shaking = 0;
        }
    }

    if (player_car_logical_xpos > 15000000) {
        player_car_logical_xpos = 15000000;
    } else if (player_car_logical_xpos < -15000000) {
        player_car_logical_xpos = -15000000;
    }

    if (player_car_invincible_countdown != 0) {
        player_car_invincible_countdown --;
    }

    race_ticks++;
}

void player_car_crash()
{
    /*if (player_car_state != PLAYER_CAR_STATE_NORMAL || player_car_invincible_countdown > 0) {
        return;
    }*/

    if (player_car_speed > 600) {
        player_car_flip_crash();
    } else if (player_car_speed > 100) {
        player_car_state = PLAYER_CAR_STATE_SPIN_CRASH;
        player_car_flip_image_tracker = 0;
    } else {
        player_car_speed = 0;
        player_car_invincible_countdown = 180;
    }
}

void player_car_flip_crash()
{
    player_car_state = PLAYER_CAR_STATE_FLIP_CRASH;
    player_car_altitude_change = (player_car_speed - 200) * 2;
    if (player_car_altitude_change > 1500) {
        player_car_altitude_change = 1500;
    }
    player_car_flip_image_tracker = 0;
}

uint16_t player_car_get_sprite_definition()
{
    if (player_car_state == PLAYER_CAR_STATE_FLIP_CRASH) {
        return 210 + player_car_flip_image_tracker / 4;
    } else if (player_car_state == PLAYER_CAR_STATE_SPIN_CRASH) {
        return 269 + player_car_flip_image_tracker / 8;
    }

    uint16_t player_car_sprite_definition_offset = 9;
    if (player_car_speed > 0) {
        if (player_car_steering <= -375 && current_road_curvature > 160) {
            player_car_sprite_definition_offset += 18;
        } else if (player_car_steering <= -250) {
            player_car_sprite_definition_offset += 30;
        } else if (player_car_steering <= -125) {
            player_car_sprite_definition_offset += 29;
        } else if (player_car_steering >= 375 && current_road_curvature < -160) {
            player_car_sprite_definition_offset += 9;
        } else if (player_car_steering >= 250) {
            player_car_sprite_definition_offset += 28;
        } else if (player_car_steering >= 125) {
            player_car_sprite_definition_offset += 27;
        }
    }

    return player_car_sprite_definition_offset;
}
