#include "opponent_cars.h"
#include "player_car.h"
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "display_list.h"

struct OpponentCar opponent_cars[OPPONENT_CAR_COUNT];

void opponent_cars_init()
{
    struct OpponentCar *current_opponent_car = opponent_cars;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = -100;
    current_opponent_car->speed = 300;
    current_opponent_car->active = 1;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = 0;
    current_opponent_car->speed = 400;
    current_opponent_car->active = 1;

    current_opponent_car++;

    current_opponent_car->player_relative_track_position = 10000;
    current_opponent_car->xpos = 100;
    current_opponent_car->speed = 500;
    current_opponent_car->active = 1;
}

void opponent_cars_update()
{
    struct OpponentCar *current_opponent_car = opponent_cars;

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        current_opponent_car->player_relative_track_position += current_opponent_car->speed;
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

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        // TODO: the 65535 check may eventually be over-defensive
        if (current_opponent_car->active && current_opponent_car->player_relative_track_position > 0 && current_opponent_car->player_relative_track_position < 65535) {
            scanline_index = distance_to_scanline_lookup[current_opponent_car->player_relative_track_position];
            if (scanline_index != -1) {
                road_scanline = &road_scanlines[scanline_index];
                sprite_index = 15 - (scanline_index / 6);
                if (sprite_index < (15-7)) {
                    sprite_index = 15-7;
                }

                if (current_opponent_car->xpos > 0) {
                    screen_xpos = (((road_scanline->current_logical_xpos + road_scanline->logical_xpos_add_values[current_opponent_car->xpos]) >> 16));
                } else {
                    screen_xpos = (((road_scanline->current_logical_xpos - road_scanline->logical_xpos_add_values[-current_opponent_car->xpos]) >> 16));
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

