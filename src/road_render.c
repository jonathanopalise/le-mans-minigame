#include "road_render.h"
#include "road_graphics.h"
#include "hardware_playfield.h"
#include "blitter.h"

void road_render()
{
    uint32_t *current_byte_offset = byte_offsets;
    uint16_t *line_start_dest = (hidden_hardware_playfield->buffer) + 160*119;
    uint16_t *line_start_source;
    uint16_t x;
    uint16_t *dest;
    uint16_t *source;

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

    for (uint16_t index = 0; index < 80; index++) {
        line_start_source = &gfx_data[*current_byte_offset/2];

        /*source = line_start_source;
        dest = line_start_dest;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }*/

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest;
        *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source - 2; // -4 bytes
        *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
        *((volatile uint16_t *)BLITTER_CONTROL) = 0xc080;

        /*source = line_start_source + 1;
        dest = line_start_dest + 1;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }*/

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest + 1; // +2 bytes
        *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source - 1; // -2 bytes
        *((volatile int16_t *)BLITTER_Y_COUNT) = 1;
        *((volatile uint16_t *)BLITTER_CONTROL) = 0xc080;

        line_start_dest += 80;
        current_byte_offset++;
    }

}

