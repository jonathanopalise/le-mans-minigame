#include "road_movement.h"
#include "road_geometry.h"
#include "player_car.h"
#include "track_segments.h"
#include "trackside_items.h"
#include "trackside_items_process.h"
#include "movement_update_inner.h"

// TODO: this should be a generated value
#define PLAYER_CAR_SCANLINE 75

#define NEGATIVE_SHIFT_REQUIRED 1
#define POSITIVE_SHIFT_REQUIRED 2
#define NEGATIVE_TOTAL_CHANGE_TO_APPLY 4
#define POSITIVE_TOTAL_CHANGE_TO_APPLY 8

#define MAX_MOUNTAINS_SHIFT (319<<16)

int32_t current_road_curvature;
int32_t mountains_shift;

void road_corners_init()
{
    for (uint16_t index = 0; index < 100; index++) {
        road_scanlines[index].current_logical_xpos = 0;
    }

    current_road_curvature = 0;
    mountains_shift = 0;
}

void road_corners_update() {
    uint32_t segment_changes_to_apply;
    int32_t total_change_to_apply = 0;
    uint16_t current_segment_changes_passed;
    int32_t player_xpos_shift;

    if (camera_track_position > player_car_current_track_segment_end_position) {
        // apply all changes from old track segment before moving onto new one
        segment_changes_to_apply = player_car_current_track_segment->change_count - player_car_current_track_segment_changes_applied;
        if (player_car_current_track_segment->change_direction == DIRECTION_LEFT) {
            total_change_to_apply += segment_changes_to_apply;
        } else if (player_car_current_track_segment->change_direction == DIRECTION_RIGHT) {
            total_change_to_apply -= segment_changes_to_apply;
        }

        player_car_current_track_segment++;

        // if new track segment has no changes, wrap back to start of track
        if (player_car_current_track_segment->change_count == 0) {
            camera_track_position -= player_car_current_track_segment_end_position;
            player_car_current_track_segment = track_segments;
            player_car_current_track_segment_start_position = 0;
            current_nearest_trackside_item = trackside_items;
        } else {
            player_car_current_track_segment_start_position = player_car_current_track_segment_end_position;
        }
        player_car_current_track_segment_end_position = player_car_current_track_segment_start_position + (player_car_current_track_segment->change_frequency * player_car_current_track_segment->change_count);
        player_car_current_track_segment_changes_applied = 0;
    }

    current_segment_changes_passed = (camera_track_position - player_car_current_track_segment_start_position) / player_car_current_track_segment->change_frequency;
    segment_changes_to_apply = (current_segment_changes_passed - player_car_current_track_segment_changes_applied);
    player_car_current_track_segment_changes_applied = current_segment_changes_passed;

    if (player_car_current_track_segment->change_direction == DIRECTION_LEFT) {
        total_change_to_apply += segment_changes_to_apply;
    } else if (player_car_current_track_segment->change_direction == DIRECTION_RIGHT) {
        total_change_to_apply -= segment_changes_to_apply;
    }

    int32_t shift_required = (road_scanlines[PLAYER_CAR_SCANLINE].current_logical_xpos >> 16) - (player_car_logical_xpos >> 16);

    uint16_t scenario = 0;
    if (shift_required > 0) {
        scenario |= POSITIVE_SHIFT_REQUIRED;
    } else if (shift_required < 0) {
        scenario |= NEGATIVE_SHIFT_REQUIRED;
    }
    
    if (total_change_to_apply > 0) {
        scenario |= POSITIVE_TOTAL_CHANGE_TO_APPLY;
    } else if (total_change_to_apply < 0) {
        scenario |= NEGATIVE_TOTAL_CHANGE_TO_APPLY;
    }

    current_road_curvature -= total_change_to_apply;
    player_xpos_shift = current_road_curvature * ((player_car_speed * player_car_speed) / 375);
    player_car_logical_xpos += player_xpos_shift;

    if (player_xpos_shift < -500000 || player_xpos_shift > 500000) {
        play_sound(7);
    }

    mountains_shift += current_road_curvature * player_car_speed;
    if (mountains_shift < 0) {
        mountains_shift += MAX_MOUNTAINS_SHIFT;
    } else if (mountains_shift > (MAX_MOUNTAINS_SHIFT - 1)) {
        mountains_shift -= MAX_MOUNTAINS_SHIFT;
    }

    struct RoadScanline *current_road_scanline = road_scanlines;
    switch (scenario) {
        case NEGATIVE_SHIFT_REQUIRED:
            movement_update_inner_scenario_1(
                sizeof(struct RoadScanline),                                        // needs to go into a2
                &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
                &(current_road_scanline->logical_xpos_add_values[-shift_required])   // needs to go into a1
            );
            /*shift_required =- shift_required;
            for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline++;
            }*/
            break;
        case POSITIVE_SHIFT_REQUIRED:
            movement_update_inner_scenario_2(
                sizeof(struct RoadScanline),                                        // needs to go into a2
                &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
                &(current_road_scanline->logical_xpos_add_values[shift_required])   // needs to go into a1
            );
            /*for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline++;
            }*/
            break;
        case NEGATIVE_TOTAL_CHANGE_TO_APPLY:
            movement_update_inner_scenario_1(
                sizeof(struct RoadScanline),                                        // needs to go into a2
                &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
                &(current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply])   // needs to go into a1
            );
            /*total_change_to_apply = -total_change_to_apply;
            for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply];
                current_road_scanline++;
            }*/
            break;
        case POSITIVE_TOTAL_CHANGE_TO_APPLY:
            movement_update_inner_scenario_2(
                sizeof(struct RoadScanline),                                        // needs to go into a2
                &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
                &(current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply])   // needs to go into a1
            );
            /*for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply];
                current_road_scanline++;
            }*/
            break;
        case NEGATIVE_SHIFT_REQUIRED|NEGATIVE_TOTAL_CHANGE_TO_APPLY:
            /*for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_add_values[-shift_required];
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply];
                current_road_scanline++;
            }*/

            movement_update_inner_scenario_3(
                sizeof(struct RoadScanline),
                &(current_road_scanline->current_logical_xpos),
                &(current_road_scanline->logical_xpos_add_values[-shift_required]),
                &(current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply])
            );
            break;
        case NEGATIVE_SHIFT_REQUIRED|POSITIVE_TOTAL_CHANGE_TO_APPLY:
            /*for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_add_values[-shift_required];
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply];
                current_road_scanline++;
            }*/

            movement_update_inner_scenario_4(
                sizeof(struct RoadScanline),
                &(current_road_scanline->current_logical_xpos),
                &(current_road_scanline->logical_xpos_add_values[-shift_required]),
                &(current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply])
            );
            break;
        case POSITIVE_SHIFT_REQUIRED|NEGATIVE_TOTAL_CHANGE_TO_APPLY:
            for (uint16_t index = 0; index < 100; index++) {
                while (1==1) {}
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply];
                current_road_scanline++;
            }
            break;
        case POSITIVE_SHIFT_REQUIRED|POSITIVE_TOTAL_CHANGE_TO_APPLY:
            for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply];
                current_road_scanline++;
            }
            break;
    }
}
