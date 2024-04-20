#include "opponent_cars.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "display_list.h"
#include "road_movement.h"
#include "random.h"
#include "natfeats.h"
#include <stdio.h>

struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

#define RED_CAR_BASE_INDEX 15
#define YELLOW_CAR_BASE_INDEX (RED_CAR_BASE_INDEX + 24)
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
    current_opponent_car->lane = 0;
    current_opponent_car->speed = 600;
    current_opponent_car->max_speed = 600;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = RED_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 25000;
    current_opponent_car->lane = 1;
    current_opponent_car->speed = 650;
    current_opponent_car->max_speed = 650;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = YELLOW_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 20000;
    current_opponent_car->lane = 2;
    current_opponent_car->speed = 700;
    current_opponent_car->max_speed = 700;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = BLUE_CAR_BASE_INDEX;
    current_opponent_car->lane_change_countdown = 0;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 15000;
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
    uint16_t lane_status[4];
    int16_t xpos;
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


    /*snprintf(
        nf_strbuf,
        256,
        "spawned opponent speed: %d\n",
        current_opponent_car->speed
    );
    nf_print(nf_strbuf);*/



    /*for (uint16_t index = 0; index < 4; index++) {
        lane_status[index] = 0; // empty
    }
    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        lane_status[opponent_cars[index].lane] = 1;
    }*/

    //uint16_t new_lane;
    // lane selection - 1 bit
    /*if ((random_number >> 12) & 1) {
        if (lane_status[3] == 0) {
            new_lane = 3;
        } else if (lane_status[2] == 0) {
            new_lane = 2;
        } else if (lane_status[1] == 0) {
            new_lane = 1;
        } else {
            new_lane = 0;
        }
    } else {
        if (lane_status[0] == 0) {
            new_lane = 0;
        } else if (lane_status[1] == 0) {
            new_lane = 1;
        } else if (lane_status[2] == 0) {
            new_lane = 2;
        } else {
            new_lane = 3;
        }
    }*/

    //current_opponent_car->lane = new_lane;
    current_opponent_car->lane = (random_number >> 12) & 3;
}

void opponent_cars_update()
{
    struct OpponentCar *current_opponent_car = opponent_cars;
    struct OpponentCar *current_other_opponent_car;
    struct OpponentCar *current_other_opponent_car_2;
    uint16_t left_lane_blocked;
    uint16_t right_lane_blocked;
    uint16_t index3;
    uint16_t left_lane_index;
    uint16_t right_lane_index;
    uint16_t take_evasive_action;
    int32_t distance_max_advance;
    int32_t combined_max_speed;

    int32_t corner_sharpness = current_road_curvature > 0 ? current_road_curvature : -current_road_curvature;
    int32_t curvature_max_speed = 900 - ((corner_sharpness * corner_sharpness) >> 8);
    if (curvature_max_speed < 500) {
        curvature_max_speed = 500;
    }

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
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
            current_opponent_car->speed -= 6;
        } else {
            current_opponent_car->speed += 2;
            if (current_opponent_car->speed > current_opponent_car->max_speed) {
                current_opponent_car->speed = current_opponent_car->max_speed;
            }
        }

        if (current_opponent_car->speed > distance_max_advance) {
            current_opponent_car->player_relative_track_position += distance_max_advance;
        } else {
            current_opponent_car->player_relative_track_position += current_opponent_car->speed;
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

        if (current_opponent_car->player_relative_track_position > 65536) {
            current_opponent_car->player_relative_track_position = 65536;
        }

        if (current_opponent_car->player_relative_track_position < 0) {
            opponent_horizon_respawn(current_opponent_car);
        }

        current_opponent_car++;
    }

    current_opponent_car = opponent_cars;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        left_lane_index = current_opponent_car->lane - 1;
        right_lane_index = current_opponent_car->lane + 1;
        left_lane_blocked = 0;
        right_lane_blocked = 0;
        current_other_opponent_car = opponent_cars;

        // TODO
        // if this car is already changing lane, skip to next iteration

        if (current_opponent_car->lane_change_countdown != 0) {
            break;
        }

        // check road ahead in same lane for other cars
        // if none, we can carry on our merry way
        if (current_opponent_car->lane == 0) {
            left_lane_blocked = 1;
        }

        if (current_opponent_car->lane == 3) {
            right_lane_blocked = 1;
        }

        take_evasive_action = 0;

        if ((random() & 2047) < 4) {
            // change lane just because
            take_evasive_action = 1;
        }

        if (!take_evasive_action) {
            for (uint16_t index2 = 0; index2 < OPPONENT_CAR_COUNT; index2++) {
                if (current_opponent_car != current_other_opponent_car) {
                    if (current_other_opponent_car->lane == current_opponent_car->lane && 
                        current_other_opponent_car->player_relative_track_position < (current_opponent_car->player_relative_track_position + 8000) &&
                        current_other_opponent_car->player_relative_track_position > (current_opponent_car->player_relative_track_position)
                    ) {
                        // change lane because we have to
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
            if (!left_lane_blocked) {
                // we're not in the leftmost lane, but the lane to our left may contain cars
                current_other_opponent_car_2 = opponent_cars;
                for (index3 = 0; index3 < OPPONENT_CAR_COUNT; index3++) {
                    if (current_other_opponent_car_2 != current_other_opponent_car &&
                        current_other_opponent_car_2->lane == left_lane_index &&
                        current_other_opponent_car_2->player_relative_track_position < (current_other_opponent_car->player_relative_track_position + 8000) &&
                        current_other_opponent_car_2->player_relative_track_position > (current_other_opponent_car->player_relative_track_position - 4000)
                    ) {
                        left_lane_blocked = 1;
                        break;
                    }
                    current_other_opponent_car_2++;
                }
            }

            if (!right_lane_blocked) {
                // we're not in the rightmost lane, but the lane to our right may contain cars
                current_other_opponent_car_2 = opponent_cars;
                for (index3 = 0; index3 < OPPONENT_CAR_COUNT; index3++) {
                    if (current_other_opponent_car_2 != current_other_opponent_car &&
                        current_other_opponent_car_2->lane == right_lane_index &&
                        current_other_opponent_car_2->player_relative_track_position < (current_other_opponent_car->player_relative_track_position + 8000) &&
                        current_other_opponent_car_2->player_relative_track_position > (current_other_opponent_car->player_relative_track_position - 4000) 
                    ) {
                        right_lane_blocked = 1;
                        break;
                    }
                    current_other_opponent_car_2++;
                }
            }


            if (left_lane_blocked && right_lane_blocked) {
                // if both left and right lanes blocked, brake
                current_opponent_car->speed -= 6;
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

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        // TODO: the 65535 check may eventually be over-defensive
        if (current_opponent_car->active && current_opponent_car->player_relative_track_position > 0 && current_opponent_car->player_relative_track_position < 45000) {
            scanline_index = distance_to_scanline_lookup[current_opponent_car->player_relative_track_position];
            if (scanline_index != -1) {
                road_scanline = &road_scanlines[scanline_index];
                //sprite_index = current_opponent_car->base_sprite_index + road_scanline->sprite_index_adjust;

                /*sprite_index = current_opponent_car->base_sprite_index - (scanline_index / 6);
                if (sprite_index < (current_opponent_car->base_sprite_index - 7)) {
                    sprite_index = current_opponent_car->base_sprite_index - 7;
                }*/

                sprite_index = (current_opponent_car->base_sprite_index - 7) + road_scanline->sprite_index_adjust;
 
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

                sprite_aspect = (screen_xpos << 1) - (current_road_curvature >> 1);

                if (sprite_aspect > 50) {
                    sprite_index += 8;
                } else if (sprite_aspect < -50) {
                    sprite_index += 16;
                }

                screen_xpos += 160;

                display_list_add_sprite(
                    &sprite_definitions[sprite_index],
                    screen_xpos,
                    (119 + scanline_index)
                );
            }
        }
        current_opponent_car++;
    }
}

