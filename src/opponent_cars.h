#ifndef __OPPONENT_CARS_H
#define __OPPONENT_CARS_H

#include <inttypes.h>

#define OPPONENT_CAR_COUNT 4

struct OpponentCar {
    int32_t player_relative_track_position;
    int32_t max_player_relative_track_position;
    uint16_t lane;
    uint16_t speed;
    uint16_t max_speed;
    uint16_t target_speed;
    uint16_t last_advance;
    uint16_t base_sprite_index;
    int16_t lane_change_countdown;
};

extern struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

extern uint16_t lane_to_xpos_mappings[4];

void opponent_cars_init();
void opponent_cars_update();
void opponent_cars_process();

#endif
