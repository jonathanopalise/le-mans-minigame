#include "road_render.h"
#include "road_graphics.h"
#include "road_geometry.h"
#include "hardware_playfield.h"
#include "blitter.h"

void road_render()
{
    uint32_t *current_byte_offset = byte_offsets;
    uint8_t *line_start_dest = (hidden_hardware_playfield->buffer) + 160*119;
    uint8_t *line_start_source;
    uint16_t x;
    uint16_t *dest;
    uint16_t *source;
    int32_t current_skew;
    int32_t skew_adjust;

    /*

    from lotus ste source:

    ext.l d1                 ; d1 is the shift value for the current line
    move.l d1,d3             ; copy to d3
    and.b #15,d3             ; convert to skew value
    asr.w d5,d1              ; shift the source data pointer to the correct start point
    and.b #$f8,d1

    ...

    sub.l d1,d6              ; d1 now contains adjusted source

    */

// iteration
        *((int16_t *)BLITTER_ENDMASK_1) = -1;
        *((int16_t *)BLITTER_ENDMASK_2) = -1;
        *((int16_t *)BLITTER_ENDMASK_3) = -1;
        *((int16_t *)BLITTER_SOURCE_X_INCREMENT) = 4;
        *((int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
        *((int16_t *)BLITTER_X_COUNT) = 20;
        *((uint16_t *)BLITTER_HOP_OP) = 0x0203;

    struct RoadScanline *current_road_scanline = road_scanlines;

    for (uint16_t index = 0; index < 80; index++) {
        line_start_source = &gfx_data[*current_byte_offset/2];

        current_skew = current_road_scanline->current_logical_xpos >> 16;
        skew_adjust = (current_skew >> 2) & 0xfffffffc;

        /*source = line_start_source;
        dest = line_start_dest;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }*/

        if (current_road_scanline->distance_along_road & 2048) {
            *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = (line_start_source - 4) - skew_adjust; // -4 bytes
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;
        } else {
            *((volatile uint16_t *)BLITTER_HOP_OP) = 0xf;
        }
        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest;
        *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
        *((volatile uint16_t *)BLITTER_CONTROL) = 0xc080 | (current_skew & 15);

        /*source = line_start_source + 1;
        dest = line_start_dest + 1;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }*/
        *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest + 2; // +2 bytes
        *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = (line_start_source - 2) - skew_adjust; // -2 bytes
        *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
        *((volatile uint16_t *)BLITTER_CONTROL) = 0xc080 | (current_skew & 15);

        line_start_dest += 160;
        current_byte_offset++;
        current_road_scanline++;
    }

}

