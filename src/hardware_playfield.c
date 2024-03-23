#include <mint/osbind.h>
#include <mint/sysbind.h>
#include <inttypes.h>
#include <string.h>
#include "hardware_playfield.h"
#include "blitter.h"
#include "draw_sprite.h"

#define HARDWARE_PLAYFIELD_COUNT 3

static int16_t visible_index;
static int16_t ready_index;
static int16_t drawing_index;

struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];

void hardware_playfield_handle_vbl()
{
	if (ready_index >= 0) {
		visible_index = ready_index;
		ready_index = -1;
		Setscreen(
            hardware_playfields[visible_index].buffer,
            hardware_playfields[drawing_index].buffer,
            -1
        );
    }
}

void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    draw_sprite(
        xpos - sprite_definition->origin_x,
        ypos - sprite_definition->origin_y,
        sprite_definition->words,
        sprite_definition->source_data_width,
        sprite_definition->source_data_height,
        playfield->buffer,
        playfield->current_bitplane_draw_record,
        &(sprite_definition->compiled_sprite_0)
    );

    playfield->current_bitplane_draw_record++;
    playfield->sprites_drawn++;
}

void hardware_playfield_erase_sprites()
{
    struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    struct BitplaneDrawRecord *current_bitplane_draw_record = playfield->bitplane_draw_records;

    int16_t lines_to_draw;
    uint8_t *destination_address;

    *((volatile uint16_t *)BLITTER_HOP_OP) = 0;
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8; // TODO: check value
    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;

    for (uint16_t index = 0; index < playfield->sprites_drawn; index++) {
        // road draws in bitplanes 0 and 1, so we only need to clear bitplanes 2 and 3
        // we will probably draw background in planes 0 and 1 too...
        if (current_bitplane_draw_record->destination_address != 0) {
            if (current_bitplane_draw_record->ypos < 90) {
                lines_to_draw = 90 - current_bitplane_draw_record->ypos;

                *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = current_bitplane_draw_record->destination_y_increment;
                *((volatile int16_t *)BLITTER_X_COUNT) = current_bitplane_draw_record->x_count;

                *((volatile uint16_t *)BLITTER_HOP_OP) = 0xf;

                *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address;
                *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
                *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

                *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address + 2;
                *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
                *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

                *((volatile uint16_t *)BLITTER_HOP_OP) = 0;

                *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address + 4;
                *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
                *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

                *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = current_bitplane_draw_record->destination_address + 6;
                *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
                *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;


                destination_address = current_bitplane_draw_record->destination_address + (160 * lines_to_draw);
                lines_to_draw = current_bitplane_draw_record->y_count - lines_to_draw;
            }

            lines_to_draw = current_bitplane_draw_record->y_count;
            destination_address = current_bitplane_draw_record->destination_address;            

            *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = current_bitplane_draw_record->destination_y_increment;
            *((volatile int16_t *)BLITTER_X_COUNT) = current_bitplane_draw_record->x_count;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address + 4;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address + 6;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;
        }

        current_bitplane_draw_record++;
    }

    playfield->current_bitplane_draw_record = playfield->bitplane_draw_records;
    playfield->sprites_drawn = 0;
}

static void hardware_playfield_init_playfield(struct HardwarePlayfield *hardware_playfield)
{
    memset(hardware_playfield->buffer, 0, HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES);
    hardware_playfield->current_bitplane_draw_record = hardware_playfield->bitplane_draw_records;
    hardware_playfield->sprites_drawn = 0;

    uint16_t word1,word2,word3,word4;
    uint16_t current_stripe_iterations;

    uint16_t *current_dest = hardware_playfield->buffer;
    for (uint16_t stripe_index = 15; stripe_index >= 3; stripe_index--) {
        // 80 words per line/20 iterations per line
        // 5 lines per stripe
        // so 80 iterations per stripe
        word1 = (stripe_index & 1) ? 0xffff : 0;
        word2 = (stripe_index >> 1 & 1) ? 0xffff: 0;
        word3 = (stripe_index >> 2 & 1) ? 0xffff: 0;
        word4 = (stripe_index >> 3 & 1) ? 0xffff: 0;

        current_stripe_iterations = 5*20;
        if (stripe_index == 3) {
            current_stripe_iterations = 30*20;
        }

        for (uint16_t iterations = 0; iterations < current_stripe_iterations; iterations++) {
            *current_dest = word1;
            current_dest++;
            *current_dest = word2;
            current_dest++;
            *current_dest = word3;
            current_dest++;
            *current_dest = word4;
            current_dest++;
        }
    }
}

void hardware_playfield_init()
{
    visible_index = 0;
    ready_index = -1;
    drawing_index = 1;

    uint16_t *phys_base = Physbase();
    hardware_playfields[0].buffer = phys_base;
    hardware_playfields[1].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES;
    hardware_playfields[2].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES * 2;

    for (uint16_t index = 0; index < HARDWARE_PLAYFIELD_COUNT; index++) {
        hardware_playfield_init_playfield(&hardware_playfields[index]);
    }
}

static void hardware_playfield_error()
{
    while(0) {};
}

void hardware_playfield_frame_complete()
{
    if (ready_index >= 0) {
        hardware_playfield_error();
    } else {
        ready_index = drawing_index;
        drawing_index++;
        if (drawing_index == HARDWARE_PLAYFIELD_COUNT) {
            drawing_index = 0;
        }
    }
}

struct HardwarePlayfield *hardware_playfield_get_drawing_playfield()
{
    return &hardware_playfields[drawing_index];
}


