#ifndef __PLAYER_CAR_H
#define __PLAYER_CAR_H

#include "track_segments.h"

extern struct TrackSegment *player_car_current_track_segment;
extern uint32_t player_car_current_track_segment_start_position;
extern uint32_t player_car_current_track_segment_end_position;
extern uint16_t player_car_current_track_segment_changes_applied;
extern int32_t camera_track_position;
extern int32_t player_car_logical_xpos;
extern int32_t player_car_speed;
extern int32_t player_car_steering;

void player_car_initialise();

void player_car_handle_inputs();

#endif
