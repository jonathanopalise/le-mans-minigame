#ifndef __OPPONENT_CARS_H
#define __OPPONENT_CARS_H

#include <inttypes.h>

#define OPPONENT_CAR_COUNT 3

struct OpponentCar {
    int32_t player_relative_track_position;
    int16_t xpos;
    uint32_t speed;
    uint16_t active;
    uint16_t base_sprite_index;
};

extern struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

void opponent_cars_init();
void opponent_cars_update();
void opponent_cars_process();

#endif
