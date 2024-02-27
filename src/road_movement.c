#include "road_movement.h"
#include "road_geometry.h"
#include "player_car.h"
#include "track_segments.h"
#include "movement_update_inner.h"

// TODO: this should be a generated value
#define PLAYER_CAR_SCANLINE 75

void road_corners_update() {
    uint32_t segment_changes_to_apply;
    int32_t total_change_to_apply = 0;
    uint16_t current_segment_changes_passed;

    if (player_car_track_position > player_car_current_track_segment_end_position) {
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
            player_car_track_position -= player_car_current_track_segment_end_position;
            player_car_current_track_segment = track_segments;
            player_car_current_track_segment_start_position = 0;
        } else {
            player_car_current_track_segment_start_position = player_car_current_track_segment_end_position;
        }
        player_car_current_track_segment_end_position = player_car_current_track_segment_start_position + (player_car_current_track_segment->change_frequency * player_car_current_track_segment->change_count);
        player_car_current_track_segment_changes_applied = 0;
    }

    current_segment_changes_passed = (player_car_track_position - player_car_current_track_segment_start_position) / player_car_current_track_segment->change_frequency;
    segment_changes_to_apply = (current_segment_changes_passed - player_car_current_track_segment_changes_applied);
    player_car_current_track_segment_changes_applied = current_segment_changes_passed;

    if (player_car_current_track_segment->change_direction == DIRECTION_LEFT) {
        total_change_to_apply += segment_changes_to_apply;
    } else if (player_car_current_track_segment->change_direction == DIRECTION_RIGHT) {
        total_change_to_apply -= segment_changes_to_apply;
    }

    if (total_change_to_apply != 0) {
        // TODO: this needs to be all 100 scanlines
        struct RoadScanline *current_road_scanline = road_scanlines;
        for (uint16_t index = 0; index < 100; index++) {
            if (total_change_to_apply > 0) {
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_corner_add_values[total_change_to_apply];
            } else {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_corner_add_values[-total_change_to_apply];
            }
            current_road_scanline++;
        }
    }
}

void road_movement_update() {
    // TODO: need to turn scanline count = 80 into a constant somewhere

    int32_t shift_required = (road_scanlines[PLAYER_CAR_SCANLINE].current_logical_xpos >> 16) - (player_car_logical_xpos >> 16);

    if (shift_required != 0) {
        struct RoadScanline *current_road_scanline = road_scanlines;

        if (shift_required < 0) {
            shift_required =- shift_required;
            for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline++;
            }
        } else {
            for (uint16_t index = 0; index < 100; index++) {
                current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_add_values[shift_required];
                current_road_scanline++;
            }

            // disabled for now while I try to merge road_corners_update and road_movement_update
            /*movement_update_inner(
                sizeof(struct RoadScanline),                                        // needs to go into a2
                &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
                &(current_road_scanline->logical_xpos_add_values[shift_required])   // needs to go into a1
            );*/
        }
    }
}
