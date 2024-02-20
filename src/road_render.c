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

// iteration

    for (uint16_t index = 0; index < 80; index++) {
        line_start_source = &gfx_data[*current_byte_offset/2];

        /*source = line_start_source;
        dest = line_start_dest;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }*/

        *((int16_t *)BLITTER_ENDMASK_1) = -1;
        *((int16_t *)BLITTER_ENDMASK_2) = -1;
        *((int16_t *)BLITTER_ENDMASK_3) = -1;

        *((int16_t *)BLITTER_SOURCE_X_INCREMENT) = 4;
        *((int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
        *((uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest;
        *((uint32_t *)BLITTER_SOURCE_ADDRESS) = line_start_source - 2;
        *((int16_t *)BLITTER_X_COUNT) = 20;
        *((uint16_t *)BLITTER_HOP_OP) = 0x0203;
        *((int16_t *)BLITTER_Y_COUNT) = 1;
        *((uint16_t *)BLITTER_CONTROL) = 0xc080;

        source = line_start_source + 1;
        dest = line_start_dest + 1;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }

        line_start_dest += 80;
        current_byte_offset++;
    }

}

