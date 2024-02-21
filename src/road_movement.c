#include "road_movement.h"
#include "road_geometry.h"

void road_movement_update() {
    // TODO: need to turn scanline count = 80 into a constant somewhere

    /*uint16_t joy_up = joy_data & 1;
    uint16_t joy_down = joy_data & 2;
    uint16_t joy_left = joy_data & 4;
    uint16_t joy_right = joy_data & 8;*/

    struct RoadScanline *current_road_scanline = road_scanlines;
    for (uint16_t index = 0; index < 80; index++) {

        /*if (joy_left) {
            current_road_scanline->current_logical_xpos += current_road_scanline->logical_xpos_add_values[1];
        } else if (joy_right) {
            current_road_scanline->current_logical_xpos -= current_road_scanline->logical_xpos_add_values[1];
        }*/

        current_road_scanline++;
    }
}
