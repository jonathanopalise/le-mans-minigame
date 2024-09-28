#include "mountains_render.h"
#include "mountain_graphics.h"
#include "hardware_playfield.h"
#include "blitter.h"
#include "road_movement.h"
#include "lookups.h"
#include "natfeats.h"
#include <stdio.h>

void mountains_render()
{
    uint8_t *line_start_source = mountain_graphics;
    uint8_t *line_start_dest = (drawing_playfield->buffer) + 160*90;
    int16_t line_count = 29;

    int16_t scroll_pixels = mountains_shift >> 16;

    if (drawing_playfield->mountains_scroll_pixels == scroll_pixels && drawing_playfield->tallest_sprite_ypos > 90)  {
        // then we might be able to skip partial or full drawing of the mountains
        int16_t lines_to_skip = drawing_playfield->tallest_sprite_ypos - 90;
        line_count -= lines_to_skip;

        if (line_count < 1) {
            // nothing to do!
            return;
        }

        uint16_t source_dest_advance = multiply_160[lines_to_skip];

        line_start_source += source_dest_advance;
        line_start_dest += source_dest_advance;
    }

    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;
    *((volatile int16_t *)BLITTER_SOURCE_X_INCREMENT) = 4;
    *((volatile int16_t *)BLITTER_SOURCE_Y_INCREMENT) = 80; // originally 158
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_X_COUNT) = 20;
    *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;

    int16_t current_skew = ((-scroll_pixels)-1);
    uint16_t blitter_control_word = 0xc080 | (current_skew & 15);

    line_start_source += (scroll_pixels >> 2) & 0xfffffffc;

    uint16_t line_count_pass_1;
    uint16_t line_count_pass_2;

    line_count_pass_1 = line_count_pass_2 = line_count >> 1;
    line_count_pass_1 += line_count & 1;
    /*if (line_count & 1) {
        line_count_pass_1 += 1;
    }*/

    *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source; // 8a32
    *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest; // 8a32
    *((volatile int16_t *)BLITTER_Y_COUNT) = line_count_pass_1; // 8a38
    *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c

    if (line_count_pass_2) {
        *((volatile int16_t *)BLITTER_Y_COUNT) = line_count_pass_2; // 8a38
        *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c
    }

    line_start_source += 2;
    line_start_dest += 2;

    *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source; // 8a32
    *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest; // 8a32
    *((volatile int16_t *)BLITTER_Y_COUNT) = line_count_pass_1; // 8a38
    *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c

    if (line_count_pass_2) {
        *((volatile int16_t *)BLITTER_Y_COUNT) = line_count_pass_2; // 8a38
        *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c
    }

    drawing_playfield->mountains_scroll_pixels = scroll_pixels;
}

