#ifndef __DETECT_COLLISIONS_H
#define __DETECT_COLLISIONS_H

#include "detect_collisions.h"
#include "opponent_cars.h"
#include "road_geometry.h"
#include <inttypes.h>

int32_t get_opponent_car_logical_xpos(struct OpponentCar *current_opponent_car, struct RoadScanline *player_scanline);
void detect_collisions();

#endif

