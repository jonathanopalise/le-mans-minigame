#include <mint/osbind.h>
#include <mint/sysbind.h>
#include <inttypes.h>
#include <string.h>
#include "hardware_playfield.h"
#include "blitter.h"

struct HardwarePlayfield *hidden_hardware_playfield;
struct HardwarePlayfield *visible_hardware_playfield;

struct HardwarePlayfield hardware_playfield_1;
struct HardwarePlayfield hardware_playfield_2;

uint8_t back_buffer[HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES];

void hardware_playfield_handle_vbl()
{
    struct HardwarePlayfield *tmp_hardware_playfield;

    // swap screens
    tmp_hardware_playfield = visible_hardware_playfield;
    visible_hardware_playfield = hidden_hardware_playfield;
    hidden_hardware_playfield = tmp_hardware_playfield;

    Setscreen(
        visible_hardware_playfield->buffer,
        hidden_hardware_playfield->buffer,
        -1
    );
}

void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    draw_sprite(
        xpos,
        ypos,
        sprite_definition->words,
        sprite_definition->source_data_width,
        sprite_definition->source_data_height,
        hidden_hardware_playfield->buffer,
        hidden_hardware_playfield->current_bitplane_draw_record
    );

    hidden_hardware_playfield->current_bitplane_draw_record++;
    hidden_hardware_playfield->sprites_drawn++;
}

void hardware_playfield_erase_sprites()
{
    struct BitplaneDrawRecord *current_bitplane_draw_record = hidden_hardware_playfield->bitplane_draw_records;

    *((volatile uint16_t *)BLITTER_HOP_OP) = 0;
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8; // TODO: check value
    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;

    for (uint16_t index = 0; index < hidden_hardware_playfield->sprites_drawn; index++) {
        // road draws in bitplanes 0 and 1, so we only need to clear bitplanes 2 and 3
        // we will probably draw background in planes 0 and 1 too...
        *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = current_bitplane_draw_record->destination_y_increment;
        *((volatile int16_t *)BLITTER_X_COUNT) = current_bitplane_draw_record->x_count;

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address + 4;
        *((volatile int16_t *)BLITTER_Y_COUNT) = current_bitplane_draw_record->y_count;
        *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address + 6;
        *((volatile int16_t *)BLITTER_Y_COUNT) = current_bitplane_draw_record->y_count;
        *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

        current_bitplane_draw_record++;
    }

    hidden_hardware_playfield->current_bitplane_draw_record = hidden_hardware_playfield->bitplane_draw_records;
    hidden_hardware_playfield->sprites_drawn = 0;
}

static void hardware_playfield_init_playfield(struct HardwarePlayfield *hardware_playfield)
{
    memset(hardware_playfield->buffer, 0, HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES);
    hardware_playfield->current_bitplane_draw_record = hardware_playfield->bitplane_draw_records;
    hardware_playfield->sprites_drawn = 0;
}

void hardware_playfield_init()
{
    //memcpy((void *)0xffff8240, palette, 32);

    visible_hardware_playfield = &hardware_playfield_1;
    hidden_hardware_playfield = &hardware_playfield_2;

    visible_hardware_playfield->buffer = Physbase();
    hidden_hardware_playfield->buffer = back_buffer;

    hardware_playfield_init_playfield(visible_hardware_playfield);
    hardware_playfield_init_playfield(hidden_hardware_playfield);
}


