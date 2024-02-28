#include "mountains_render.h"
#include "mountain_graphics.h"
#include "hardware_playfield.h"
#include "blitter.h"

void mountains_render()
{
    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;
    *((volatile int16_t *)BLITTER_SOURCE_X_INCREMENT) = 4;
    *((volatile int16_t *)BLITTER_SOURCE_Y_INCREMENT) = -78; // originally 158
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = -150;
    *((volatile int16_t *)BLITTER_X_COUNT) = 20;
    *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;

    uint16_t blitter_control_word;
    int32_t current_skew;
    int32_t skew_adjust;

    uint8_t *line_start_source = mountain_graphics;
    uint8_t *line_start_dest = (hidden_hardware_playfield->buffer) + 160*90;

    for (uint16_t index = 0; index < 29; index++) {
        //current_skew = current_road_scanline->current_logical_xpos >> 16;
        current_skew = 0;
        //skew_adjust = (current_skew >> 2) & 0xfffffffc;
        blitter_control_word = 0xc080 | (current_skew & 15);

        *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source; // 8a32
        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest; // 8a32
        *((volatile int16_t *)BLITTER_Y_COUNT) = 2; // 8a38
        *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c

        line_start_source += 160;
        line_start_dest += 160;
    }
}
