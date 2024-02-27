#include "road_render.h"
#include "road_graphics.h"
#include "road_geometry.h"
#include "hardware_playfield.h"
#include "player_car.h"
#include "blitter.h"

void road_render()
{
    uint32_t *current_byte_offset = byte_offsets;
    uint8_t *line_start_dest = (hidden_hardware_playfield->buffer) + 160*119;
    uint8_t *line_start_source;
    int32_t current_skew;
    int32_t skew_adjust;

    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;
    *((volatile int16_t *)BLITTER_SOURCE_X_INCREMENT) = 4;
    *((volatile int16_t *)BLITTER_SOURCE_Y_INCREMENT) = -78; // originally 158
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = -150;
    *((volatile int16_t *)BLITTER_X_COUNT) = 20;
    *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;

    struct RoadScanline *current_road_scanline = road_scanlines;
    uint16_t blitter_control_word;

    for (uint16_t index = 0; index < 80; index++) {
        // TODO: rather than maintaining current_byte_offset and current_road_scanline,
        // could we integrate road graphics data into road_scanlines?
        line_start_source = ((uint8_t *)(&gfx_data[*current_byte_offset >> 1])) - 2;
        current_skew = current_road_scanline->current_logical_xpos >> 16;
        skew_adjust = (current_skew >> 2) & 0xfffffffc;
        blitter_control_word = 0xc080 | (current_skew & 15);

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest; // 8a32

        if ((current_road_scanline->distance_along_road + player_car_track_position) & 2048) {
            // draw two textured bitplanes
            *((volatile int16_t *)BLITTER_Y_COUNT) = 2; // 8a38
            *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = (line_start_source - 2) - skew_adjust; // 8a24, -4 bytes
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c
        } else {
            // draw a solid bitplane then a textured bitplane
            *((volatile int16_t *)BLITTER_Y_COUNT) = 1; // 8a38
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0xf;
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word;
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;
            *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source - skew_adjust; // -2 bytes
            *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word;
        }

        line_start_dest += 160;
        current_byte_offset++;
        current_road_scanline++;
    }
}

