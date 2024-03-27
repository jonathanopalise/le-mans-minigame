#include <inttypes.h>
#include "player_car.h"
#include "initialise.h"
#include <stdio.h>

struct TrackSegment *player_car_current_track_segment;
uint32_t player_car_current_track_segment_start_position;
uint32_t player_car_current_track_segment_end_position;
uint16_t player_car_current_track_segment_changes_applied;
uint32_t player_car_track_position;
int32_t player_car_logical_xpos;
int32_t player_car_speed;
int32_t player_car_steering;

void player_car_initialise()
{
    player_car_current_track_segment = track_segments;
    player_car_current_track_segment_start_position = 0;
    player_car_current_track_segment_end_position = (player_car_current_track_segment->change_frequency * player_car_current_track_segment->change_count);
    player_car_current_track_segment_changes_applied = 0;
    player_car_track_position = 0;
    player_car_logical_xpos = 0;
    player_car_speed = 0;
    player_car_steering = 0;
}

void player_car_handle_inputs()
{
    uint16_t joy_up = joy_data & 1;
    uint16_t joy_down = joy_data & 2;
    uint16_t joy_left = joy_data & 4;
    uint16_t joy_right = joy_data & 8;
    uint16_t joy_fire = joy_data >> 7 & 1;

    char strbuf[256];

    if (joy_fire) {
        snprintf(
            strbuf,
            256,
            "track_position: %d\n",
            player_car_track_position
        );
        nf_print(strbuf);
    }

    if (joy_up) {
        player_car_speed += 2;
        if (player_car_speed > 1000) {
            player_car_speed = 1000;
        }
    } else if (joy_down) {
        player_car_speed -= 8;
        if (player_car_speed < 0) {
            player_car_speed = 0;
        }
    } else {
        player_car_speed -= 1;
        if (player_car_speed < 0) {
            player_car_speed = 0;
        }
    }

    if (joy_left) {
        player_car_steering += 125;
        if (player_car_steering > 500) {
            player_car_steering = 500;
        }
    } else if (joy_right) {
        player_car_steering -= 125;
        if (player_car_steering < 500) {
            player_car_steering = -500;
        }
    } else {
        if (player_car_steering > 0) {
            player_car_steering -= 20;
        } else if (player_car_steering < 0) {
            player_car_steering += 20;
        }
    }

    // TODO: slowdown when on grass

    player_car_track_position += player_car_speed;
    player_car_logical_xpos += player_car_steering * player_car_speed;

    if (player_car_logical_xpos > 13000000) {
        player_car_logical_xpos = 13000000;
    } else if (player_car_logical_xpos < -13000000) {
        player_car_logical_xpos = -13000000;
    }
}
