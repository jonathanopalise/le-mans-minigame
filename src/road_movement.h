#ifndef __ROAD_MOVEMENT_H
#define __ROAD_MOVEMENT_H

#include <inttypes.h>

extern int32_t mountains_shift;
extern uint32_t current_road_curvature;

void road_corners_init();

void road_corners_update();

#endif
