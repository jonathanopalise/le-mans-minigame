#include "opponent_cars.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "display_list.h"
#include "road_movement.h"

struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

#define RED_CAR_BASE_INDEX 15
#define YELLOW_CAR_BASE_INDEX (RED_CAR_BASE_INDEX + 24)

void opponent_cars_init()
{
    struct OpponentCar *current_opponent_car = opponent_cars;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = -90;
    current_opponent_car->speed = 350;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = RED_CAR_BASE_INDEX;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = 0;
    current_opponent_car->speed = 400;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = YELLOW_CAR_BASE_INDEX;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = 90;
    current_opponent_car->speed = 450;
    current_opponent_car->active = 1;
    current_opponent_car->base_sprite_index = YELLOW_CAR_BASE_INDEX;
}

void opponent_cars_update()
{
    struct OpponentCar *current_opponent_car = opponent_cars;

    int32_t corner_sharpness = current_road_curvature > 0 ? current_road_curvature : -current_road_curvature;
    int32_t curvature_max_speed = 450 - (corner_sharpness >> 8);
    int32_t normalised_opponent_car_speed;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {

        normalised_opponent_car_speed = current_opponent_car->speed;
        if (normalised_opponent_car_speed > curvature_max_speed) {
            normalised_opponent_car_speed = curvature_max_speed;
        }

        current_opponent_car->player_relative_track_position += normalised_opponent_car_speed;
        current_opponent_car->player_relative_track_position -= player_car_speed;

        if (current_opponent_car->player_relative_track_position < 0) {
            current_opponent_car->player_relative_track_position += 65535;
        } else if (current_opponent_car->player_relative_track_position > 54999) {
            current_opponent_car->player_relative_track_position -= 65535;
        }

        /*if ((current_opponent_car->player_relative_track_position < -2000) || (current_opponent_car->player_relative_track_position> 55000)) {
            current_opponent_car->active = 0;
        }*/

        current_opponent_car++;
    }
}

void opponent_cars_process()
{
    struct OpponentCar *current_opponent_car = opponent_cars;
    int16_t scanline_index;
    struct RoadScanline *road_scanline;
    int16_t sprite_index;
    int16_t screen_xpos;
    int16_t sprite_aspect;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        // TODO: the 65535 check may eventually be over-defensive
        if (current_opponent_car->active && current_opponent_car->player_relative_track_position > 0 && current_opponent_car->player_relative_track_position < 65535) {
            scanline_index = distance_to_scanline_lookup[current_opponent_car->player_relative_track_position];
            if (scanline_index != -1) {
                road_scanline = &road_scanlines[scanline_index];
                sprite_index = current_opponent_car->base_sprite_index - (scanline_index / 6);
                if (sprite_index < (current_opponent_car->base_sprite_index - 7)) {
                    sprite_index = current_opponent_car->base_sprite_index - 7;
                }

                if (current_opponent_car->xpos > 0) {
                    screen_xpos = (((road_scanline->current_logical_xpos + road_scanline->logical_xpos_add_values[current_opponent_car->xpos]) >> 16));
                } else {
                    screen_xpos = (((road_scanline->current_logical_xpos - road_scanline->logical_xpos_add_values[-current_opponent_car->xpos]) >> 16));
                }

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

