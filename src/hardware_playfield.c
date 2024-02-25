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
        hidden_hardware_playfield->buffer,
        visible_hardware_playfield->buffer,
        -1
    );
}

void hardware_playfield_erase_sprites()
{
    struct BitplaneDrawRecord *current_bitplane_draw_record = hidden_hardware_playfield->bitplane_draw_records;

    *((uint16_t *)BLITTER_HOP_OP) = 0;    

    // TODO: run two passes
    // and check the bitplanes it writes to - should only write to bitplanes that road doesn't write to
    for (int index = 0; index < hidden_hardware_playfield->sprites_drawn; index++) { 
        *((uint16_t **)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address;
        *((uint16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8; // TODO: check value
        *((uint16_t *)BLITTER_DESTINATION_Y_INCREMENT) = current_bitplane_draw_record->destination_y_increment;
        *((int16_t *)BLITTER_X_COUNT) = current_bitplane_draw_record->x_count;
        *((int16_t *)BLITTER_Y_COUNT) = current_bitplane_draw_record->y_count;
        *((uint8_t *)BLITTER_CONTROL) = BLITTER_HOG_START;
         
        current_bitplane_draw_record++;
    }

    hidden_hardware_playfield->sprites_drawn = 0;
}

static void hardware_playfield_init_playfield(struct HardwarePlayfield *hardware_playfield)
{
    memset(hardware_playfield->buffer, 0, HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES);
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


