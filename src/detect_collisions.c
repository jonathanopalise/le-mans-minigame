#include "detect_collisions.h"
#include "player_car.h"
#include "trackside_items_process.h"
#include "trackside_items.h"
#include "road_geometry.h"
#include "opponent_cars.h"
#include "natfeats.h"
#include "play_sound.h"
#include "lookups.h"

void detect_collisions()
{
    struct TracksideItem *current_trackside_item = current_nearest_trackside_item;

    int32_t current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;
    int32_t current_trackside_item_logical_xpos;

    struct RoadScanline *player_scanline = road_scanline_pointers[75];
    struct OpponentCar *current_opponent_car = opponent_cars;
    int32_t logical_xpos;
    int16_t opponent_car_xpos;

    if (player_car_state != PLAYER_CAR_STATE_NORMAL || player_car_invincible_countdown > 0) {
        return;
    }

    while (current_trackside_item_player_relative_position < 500) {
        if (current_trackside_item->xpos > 0) {
            current_trackside_item_logical_xpos = -player_scanline->object_xpos_add_values[current_trackside_item->xpos];
        } else {
            current_trackside_item_logical_xpos = player_scanline->object_xpos_add_values[-current_trackside_item->xpos];
        }

        if (current_trackside_item->track_position > (player_car_track_position - 400) &&
            current_trackside_item->track_position < (player_car_track_position + 400) &&
            current_trackside_item_logical_xpos > (player_car_logical_xpos - 4000000) && 
            current_trackside_item_logical_xpos < (player_car_logical_xpos + 4000000)
        ) {
            play_sound(SOUND_ID_CRASH); // crash sound
            player_car_crash();
        }

        current_trackside_item++;
        current_trackside_item_player_relative_position = current_trackside_item->track_position - player_car_track_position;
    }

    for (uint16_t index = 0; index < OPPONENT_CAR_COUNT; index++) {
        if (current_opponent_car->player_relative_track_position < 600 && current_opponent_car->player_relative_track_position > -600) {
            opponent_car_xpos = lane_to_xpos_mappings[current_opponent_car->lane];
            if (opponent_car_xpos > 0) {
                logical_xpos = player_scanline->object_xpos_add_values[opponent_car_xpos];
            } else {
                logical_xpos = -player_scanline->object_xpos_add_values[-opponent_car_xpos];
            }

            if (current_opponent_car->lane_change_countdown != 0) {
                if (current_opponent_car->lane_change_countdown > 0) {
                    logical_xpos += player_scanline->logical_xpos_add_values[37 - current_opponent_car->lane_change_countdown] << 1;
                } else {
                    logical_xpos -= player_scanline->logical_xpos_add_values[37 + current_opponent_car->lane_change_countdown] << 1;
                }
            }

            // not a clue why this is required :)
            logical_xpos =- logical_xpos;

            if (logical_xpos > (player_car_logical_xpos - 3000000) && logical_xpos < (player_car_logical_xpos + 3000000)) {
                play_sound(SOUND_ID_CRASH); // crash sound
                if ((player_car_speed - current_opponent_car->last_advance) > 400) {
                    player_car_flip_crash();
                } else {
                    player_car_speed = current_opponent_car->speed - 150;
                    if (player_car_speed < 0) {
                        player_car_speed = 0;
                    }
                } 
            }
        }

        current_opponent_car++;
    }
 }
