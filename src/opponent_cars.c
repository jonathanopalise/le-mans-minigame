#include "opponent_cars.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "display_list.h"
#include "road_movement.h"
#include "random.h"
#include "natfeats.h"
#include "lookups.h"
#include <stdio.h>

struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

#define RED_CAR_BASE_INDEX 15
#define YELLOW_CAR_BASE_INDEX (RED_CAR_BASE_INDEX + 28)
#define BLUE_CAR_BASE_INDEX (YELLOW_CAR_BASE_INDEX + 24)

uint16_t lane_to_xpos_mappings[4] = {-2, -1, 1, 2};

void rewrite_compiled_sprite_pointers(struct SpriteDefinition *destination_definition)
{
    struct SpriteDefinition *source_definition = &sprite_definitions[RED_CAR_BASE_INDEX - 7];

    for (uint16_t index = 0; index < 24; index++) {
        destination_definition->compiled_sprite_0 = source_definition->compiled_sprite_0;
        destination_definition->compiled_sprite_1 = source_definition->compiled_sprite_1;
        destination_definition->compiled_sprite_2 = source_definition->compiled_sprite_2;
        destination_definition->compiled_sprite_3 = source_definition->compiled_sprite_3;
        destination_definition->compiled_sprite_4 = source_definition->compiled_sprite_4;
        destination_definition->compiled_sprite_5 = source_definition->compiled_sprite_5;
        destination_definition->compiled_sprite_6 = source_definition->compiled_sprite_6;
        destination_definition->compiled_sprite_7 = source_definition->compiled_sprite_7;
        destination_definition->compiled_sprite_8 = source_definition->compiled_sprite_8;
        destination_definition->compiled_sprite_9 = source_definition->compiled_sprite_9;
        destination_definition->compiled_sprite_10 = source_definition->compiled_sprite_10;
        destination_definition->compiled_sprite_11 = source_definition->compiled_sprite_11;
        destination_definition->compiled_sprite_12 = source_definition->compiled_sprite_12;
        destination_definition->compiled_sprite_13 = source_definition->compiled_sprite_13;
        destination_definition->compiled_sprite_14 = source_definition->compiled_sprite_14;
        destination_definition->compiled_sprite_15 = source_definition->compiled_sprite_15;

        source_definition++;
        destination_definition++;
    }
}

void opponent_cars_init()
{
    struct OpponentCar *current_opponent_car = opponent_cars;

    current_opponent_car->player_relative_track_position = 30000;
    current_opponent_car->max_player_relative_track_position = 65001;
    current_opponent_car->lane = 0;
    current_opponent_car->speed = 600;
    current_opponent_car->max_speed = 600;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = RED_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 25000;
    current_opponent_car->max_player_relative_track_position = 85001;
    current_opponent_car->lane = 1;
    current_opponent_car->speed = 650;
    current_opponent_car->max_speed = 650;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = YELLOW_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 20000;
    current_opponent_car->max_player_relative_track_position = 1055001;
    current_opponent_car->lane = 2;
    current_opponent_car->speed = 700;
    current_opponent_car->max_speed = 700;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = BLUE_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 15000;
    current_opponent_car->max_player_relative_track_position = 125001;
    current_opponent_car->lane = 3;
    current_opponent_car->speed = 750;
    current_opponent_car->max_speed = 750;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = BLUE_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    rewrite_compiled_sprite_pointers(&sprite_definitions[YELLOW_CAR_BASE_INDEX - 7]);
    rewrite_compiled_sprite_pointers(&sprite_definitions[BLUE_CAR_BASE_INDEX - 7]);
}

static void opponent_horizon_respawn(struct OpponentCar *current_opponent_car)
{
    uint32_t random_number;
    uint16_t car_selector;
    uint16_t base_sprite_index;

    current_opponent_car->player_relative_track_position += 50000;
    current_opponent_car->lane_change_countdown = 0;

    random_number = random();

    // car colour selection - 3 bits
    car_selector = random_number & 7;
    if (car_selector <= 2) {
        base_sprite_index = RED_CAR_BASE_INDEX;
    } else if (car_selector >= 5) {
        base_sprite_index = YELLOW_CAR_BASE_INDEX;
    } else {
        base_sprite_index = BLUE_CAR_BASE_INDEX;
    }

    current_opponent_car->base_sprite_index = base_sprite_index;

    // speed - 8 bits
    current_opponent_car->speed = current_opponent_car->max_speed = 650 + ((random_number >> 3) & 255);
    current_opponent_car->lane = (random_number >> 12) & 3;
}

static uint16_t get_target_lane(struct OpponentCar *opponent_car)
{
    if (opponent_car->lane_change_countdown != 0) {
        if (opponent_car->lane_change_countdown > 0) {
            return opponent_car->lane + 1;
        }

        return opponent_car->lane - 1;
    }

    return opponent_car->lane;
}

static uint16_t is_opponent_distance_blocking(struct OpponentCar *opponent_car, struct OpponentCar *other_opponent_car)
{
    int32_t opponent_distance;
    int32_t speed_difference;

    // if speed difference > 0, other_opponent_car is going faster than opponent_car
    opponent_distance = other_opponent_car->player_relative_track_position - opponent_car->player_relative_track_position;

    if (opponent_distance < 1500 && opponent_distance > -1500) {
        // cars are alongside each other
        return 1;
    }

    speed_difference = other_opponent_car->speed - opponent_car->speed;
        
    if (opponent_distance > 0) {
        // other opponent car is ahead
        // other opponent car is considered blocking if
        // - it's going slower than opponent car, AND
        // - the distance between the two cars is less than a certain threshold
        return speed_difference < 0 && (opponent_distance < ((-speed_difference) << 7));
    }

    return speed_difference > 0 && ((-opponent_distance) < (speed_difference << 7));
}

void opponent_cars_update()
{
    struct OpponentCar *current_opponent_car;
    struct OpponentCar *current_other_opponent_car;
    uint16_t left_lane_blocked;
    uint16_t right_lane_blocked;
    uint16_t index2;
    uint16_t left_lane_index;
    uint16_t right_lane_index;
    uint16_t take_evasive_action;
    int32_t distance_max_advance;
    uint16_t target_lane;
    uint16_t target_lane_2;

    struct OpponentCar *nearest_opponent_car;
    int32_t nearest_opponent_car_distance;
    int32_t opponent_car_distance;

    int32_t corner_sharpness = current_road_curvature > 0 ? current_road_curvature : -current_road_curvature;
    int32_t curvature_max_speed = 900 - ((corner_sharpness * corner_sharpness) >> 8);
    if (curvature_max_speed < 500) {
        curvature_max_speed = 500;
    }

    current_opponent_car = opponent_cars;
    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        if ((index + 1) > active_opponent_cars) {
            current_opponent_car->player_relative_track_position = current_opponent_car->max_player_relative_track_position;
            current_opponent_car++;
            continue;
        }

        if (current_opponent_car->player_relative_track_position < 10000) {
            distance_max_advance = current_opponent_car->max_speed;
        } else {
            // if player_relative_track_position = 10000, distance_max_advance needs to be 1000
            // if player_relative_track_position = 60000, distance_max_advance needs to be 400

            distance_max_advance = current_opponent_car->max_speed - ((current_opponent_car->player_relative_track_position - 10000) >> 5);
            if (distance_max_advance < 400) {
                distance_max_advance = 400;
            }
        }

        if (current_opponent_car->speed > curvature_max_speed) {
            current_opponent_car->target_speed = curvature_max_speed;
        } else {
            current_opponent_car->target_speed = current_opponent_car->max_speed;
            if (current_opponent_car->speed > current_opponent_car->max_speed) {
                current_opponent_car->speed = current_opponent_car->max_speed;
            }
        }

        if (current_opponent_car->speed > distance_max_advance) {
            current_opponent_car->player_relative_track_position += distance_max_advance;
        } else {
            current_opponent_car->player_relative_track_position += current_opponent_car->speed;
        }

        if (current_opponent_car->player_relative_track_position < 0) {
            current_opponent_car->player_relative_track_position -= 100;
        }

        current_opponent_car->player_relative_track_position -= player_car_speed;

        if (current_opponent_car->lane_change_countdown > 0) {
            current_opponent_car->lane_change_countdown--;
            if (current_opponent_car->lane_change_countdown == 0) {
                current_opponent_car->lane++;
            }
        } else if (current_opponent_car->lane_change_countdown < 0) {
            current_opponent_car->lane_change_countdown++;
            if (current_opponent_car->lane_change_countdown == 0) {
                current_opponent_car->lane--;
            }
        }

        if (current_opponent_car->player_relative_track_position > current_opponent_car->max_player_relative_track_position) {
            current_opponent_car->player_relative_track_position = current_opponent_car->max_player_relative_track_position;
        }

        if (current_opponent_car->player_relative_track_position < -PLAYER_CAR_DISTANCE) {
            opponent_horizon_respawn(current_opponent_car);
        }

        current_opponent_car++;
    }

    current_opponent_car = opponent_cars;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        if ((index + 1) > active_opponent_cars) {
            continue;
        }

        // if this car is already changing lane and its current or target lane is blocked ahead, brake
        if (current_opponent_car->lane_change_countdown != 0) {
            current_other_opponent_car = opponent_cars;
            target_lane = get_target_lane(current_opponent_car);
            for (uint16_t index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                target_lane_2 = get_target_lane(current_other_opponent_car);
                if (current_opponent_car != current_other_opponent_car) {
                    if ((current_other_opponent_car->lane == current_opponent_car->lane || current_other_opponent_car->lane == target_lane || target_lane_2 == current_opponent_car->lane || target_lane_2 == target_lane) && 
                        current_other_opponent_car->player_relative_track_position < (current_opponent_car->player_relative_track_position + 7000) &&
                        current_other_opponent_car->player_relative_track_position > (current_opponent_car->player_relative_track_position)
                    ) {
                        // take evasive action to avoid collision
                        current_opponent_car->target_speed = 400;
                        break;
                    }
                }
                current_other_opponent_car++;
            }

            break;
        }

        // check road ahead in same lane for other cars
        // if none, we can carry on our merry way
        take_evasive_action = 0;

        if ((random() & 2047) < opponent_lane_change_probability) {
            // attempt to change lane to annoy player
            take_evasive_action = 1;
        } else {
            current_other_opponent_car = opponent_cars;
            for (uint16_t index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                if (index != index2) {
                    // is there somebody ahead in the same lane, or heading into the same lane?
                    if ((current_other_opponent_car->lane == current_opponent_car->lane || get_target_lane(current_other_opponent_car) == current_opponent_car->lane) &&
                        is_opponent_distance_blocking(current_opponent_car, current_other_opponent_car)
                    ) {
                        // take evasive action to avoid collision
                        take_evasive_action = 1;
                        break;
                    }
                }
                current_other_opponent_car++;
            }
        }

        // lane ahead is blocked
        // if I can move left or right, do so
        // otherwise stay in lane and brake hard to match speed of car in front

        if (take_evasive_action) {
            left_lane_index = current_opponent_car->lane - 1;
            right_lane_index = current_opponent_car->lane + 1;
            left_lane_blocked = 0;
            right_lane_blocked = 0;

            if (current_opponent_car->lane == 0) {
                left_lane_blocked = 1;
            } else {
                // we're not in the leftmost lane, but the lane to our left may contain cars
                current_other_opponent_car = opponent_cars;
                for (index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                    if (index != index2 &&
                        (current_other_opponent_car->lane == left_lane_index || get_target_lane(current_other_opponent_car) == left_lane_index) &&
                        is_opponent_distance_blocking(current_opponent_car, current_other_opponent_car)
                    ) {
                        left_lane_blocked = 1;
                        break;
                    }
                    current_other_opponent_car++;
                }
            }

            if (current_opponent_car->lane == 3) {
                right_lane_blocked = 1;
            } else {
                // we're not in the rightmost lane, but the lane to our right may contain cars
                current_other_opponent_car = opponent_cars;
                for (index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                    if (index != index2 &&
                        (current_other_opponent_car->lane == right_lane_index || get_target_lane(current_other_opponent_car) == right_lane_index) &&
                        is_opponent_distance_blocking(current_opponent_car, current_other_opponent_car)
                    ) {
                        right_lane_blocked = 1;
                        break;
                    }
                    current_other_opponent_car++;
                }
            }

            if (left_lane_blocked && right_lane_blocked) {
                // if both left and right lanes blocked, match speed of nearest blocking car
                current_other_opponent_car = opponent_cars;
                nearest_opponent_car_distance = 100000;
                nearest_opponent_car = 0;
                for (uint16_t index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                    if (index != index2) {
                        opponent_car_distance = current_other_opponent_car->player_relative_track_position - current_opponent_car->player_relative_track_position;
                        if (opponent_car_distance > 0 &&
                            (current_other_opponent_car->lane == current_opponent_car->lane || get_target_lane(current_other_opponent_car) == current_opponent_car->lane) &&
                            opponent_car_distance < nearest_opponent_car_distance
                        ) {
                            nearest_opponent_car_distance = opponent_car_distance;
                            nearest_opponent_car = current_other_opponent_car;
                        }
                    }
                    current_other_opponent_car++;
                }
                if (nearest_opponent_car != 0) {
                    if (nearest_opponent_car_distance < 100) {
                        current_opponent_car->target_speed = nearest_opponent_car->speed - 20;
                    } else {
                        current_opponent_car->target_speed = nearest_opponent_car->speed;
                    }
                }
            } else if (!left_lane_blocked && !right_lane_blocked) {
                // if both left and right lanes available, take pick of lanes based on random or other factor
                if (random() & 1) {
                    current_opponent_car->lane_change_countdown = 37;
                } else {
                    current_opponent_car->lane_change_countdown = -37;
                }
            } else if (left_lane_blocked) {
                current_opponent_car->lane_change_countdown = 37;
                // left lane blocked, move right
            } else {
                current_opponent_car->lane_change_countdown = -37;
                // right lane blocked, move left
            }
        }

        if (current_opponent_car->target_speed > current_opponent_car->speed) {
            current_opponent_car->speed += 2;
        } else {
            current_opponent_car->speed -= 4;
        }

        current_opponent_car++;
    }
}

void opponent_cars_process()
{
    struct OpponentCar *current_opponent_car = opponent_cars;
    int16_t scanline_index;
    struct RoadScanline *road_scanline;
    int16_t sprite_index;
    int32_t logical_xpos;
    int16_t screen_xpos;
    int16_t sprite_aspect;
    int16_t opponent_car_xpos;
    int32_t camera_relative_track_position;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        // TODO: the 65535 check may eventually be over-defensive
        camera_relative_track_position = current_opponent_car->player_relative_track_position + PLAYER_CAR_DISTANCE;
        //camera_relative_track_position = PLAYER_CAR_DISTANCE;
        if (current_opponent_car->active && camera_relative_track_position > 0 && camera_relative_track_position < 45000) {
            scanline_index = distance_to_scanline_lookup[camera_relative_track_position];
            if (scanline_index != -1) {
                road_scanline = road_scanline_pointers[scanline_index];
                //sprite_index = current_opponent_car->base_sprite_index + road_scanline->sprite_index_adjust;

                /*sprite_index = current_opponent_car->base_sprite_index - (scanline_index / 6);
                if (sprite_index < (current_opponent_car->base_sprite_index - 7)) {
                    sprite_index = current_opponent_car->base_sprite_index - 7;
                }*/

                sprite_index = (RED_CAR_BASE_INDEX - 7) + road_scanline->sprite_index_adjust;
 
                opponent_car_xpos = lane_to_xpos_mappings[current_opponent_car->lane];
                if (opponent_car_xpos > 0) {
                    logical_xpos = (road_scanline->current_logical_xpos + road_scanline->object_xpos_add_values[opponent_car_xpos]);
                } else {
                    logical_xpos = (road_scanline->current_logical_xpos - road_scanline->object_xpos_add_values[-opponent_car_xpos]);
                }

                if (current_opponent_car->lane_change_countdown != 0) {
                    if (current_opponent_car->lane_change_countdown > 0) {
                        logical_xpos += road_scanline->logical_xpos_add_values[37 - current_opponent_car->lane_change_countdown] << 1;
                    } else {
                        logical_xpos -= road_scanline->logical_xpos_add_values[37 + current_opponent_car->lane_change_countdown] << 1;
                    }
                }

                screen_xpos = logical_xpos >> 16;

                sprite_aspect = (screen_xpos << 1) - current_road_curvature;

                if (sprite_aspect > 50) {
                    sprite_index += 8;
                } else if (sprite_aspect < -50) {
                    sprite_index += 16;
                }

                screen_xpos += 160;

                display_list_add_sprite(
                    sprite_definition_pointers[sprite_index],
                    //&sprite_definitions[sprite_index],
                    screen_xpos,
                    (119 + scanline_index)
                );
            }
        }
        current_opponent_car++;
    }
}

