#include "road_render.h"
#include "road_graphics.h"
#include "road_geometry.h"
#include "hardware_playfield.h"
#include "player_car.h"
#include "blitter.h"

#define LINE_COUNT 80

void road_render_init()
{
    struct RoadScanline *current_road_scanline = road_scanlines;
    uint32_t *current_byte_offset = byte_offsets;

    for (uint16_t index = 0; index < LINE_COUNT; index++) {
        current_road_scanline->line_start_source = ((uint8_t *)(&gfx_data[*current_byte_offset])) - 2; 
        current_road_scanline++;
        current_byte_offset++;
    }
}

void road_render()
{
    struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    uint8_t *line_start_dest = (playfield->buffer) + 160*119;
    int16_t current_skew;
    int16_t skew_adjust;

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

    for (uint16_t index = 0; index < LINE_COUNT; index++) {
        current_skew = current_road_scanline->current_logical_xpos >> 16;
        skew_adjust = (current_skew >> 2) & 0xfffc;
        blitter_control_word = 0xc080 | (current_skew & 15);

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest; // 8a32

        if ((current_road_scanline->distance_along_road + camera_track_position) & 2048) {
            // draw two textured bitplanes
            *((volatile int16_t *)BLITTER_Y_COUNT) = 2; // 8a38
            *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = (current_road_scanline->line_start_source - 2) - skew_adjust; // 8a24, -4 bytes
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c
        } else {
            // draw a solid bitplane then a textured bitplane
            *((volatile int16_t *)BLITTER_Y_COUNT) = 1; // 8a38
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0xf;
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word;
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;
            *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = current_road_scanline->line_start_source - skew_adjust; // -2 bytes
            *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
            *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word;
        }

        line_start_dest += 160;
        current_road_scanline++;
    }
}

