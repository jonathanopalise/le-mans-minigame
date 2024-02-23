#include <inttypes.h>
#include "player_car.h"
#include "initialise.h"

uint32_t player_car_track_position;
int32_t player_car_logical_xpos;
int32_t player_car_speed;
int32_t player_car_steering;

void player_car_handle_inputs()
{
    uint16_t joy_up = joy_data & 1;
    uint16_t joy_down = joy_data & 2;
    uint16_t joy_left = joy_data & 4;
    uint16_t joy_right = joy_data & 8;

    if (joy_up) {
        player_car_speed += 2;
        if (player_car_speed > 700) {
            player_car_speed = 700;
        }
    } else if (joy_down) {
        player_car_speed -= 6;
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
        player_car_steering += 100;
        if (player_car_steering < -2000) {
            player_car_steering = -2000;
        }
    } else if (joy_right) {
        player_car_steering -= 100;
        if (player_car_steering > 2000) {
            player_car_steering = 2000;
        }
    } else {
        if (player_car_steering > 0) {
            player_car_steering -= 25;
        } else if (player_car_steering < 0) {
            player_car_steering += 25;
        }
    }

    // TODO: slowdown when on grass

    player_car_track_position += player_car_speed;
    player_car_logical_xpos += player_car_steering * player_car_speed;

    if (player_car_logical_xpos > 18000000) {
        player_car_logical_xpos = 18000000;
    } else if (player_car_logical_xpos < -18000000) {
        player_car_logical_xpos = -18000000;
    }
}
