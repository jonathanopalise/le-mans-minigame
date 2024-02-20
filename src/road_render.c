#include "road_render.h"
#include "road_graphics.h"
#include "hardware_playfield.h"

void road_render()
{
    uint32_t *current_byte_offset = byte_offsets;
    uint16_t *line_start_dest = (hidden_hardware_playfield->buffer) + 160*119;
    uint16_t *line_start_source;
    uint16_t x;
    uint16_t y;
    uint16_t *dest;
    uint16_t *source;

// iteration

    for (uint16_t index = 0; index < 80; index++) {
        line_start_source = &gfx_data[*current_byte_offset/2];

        source = line_start_source;
        dest = line_start_dest;
        for (x = 0; x < 20; x++) {
            *dest = *source;
            source += 2;
            dest += 4;
        }

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

