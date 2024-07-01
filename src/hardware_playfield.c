#include <mint/osbind.h>
#include <mint/sysbind.h>
#include <inttypes.h>
#include <string.h>
#include "hardware_playfield.h"
#include "blitter.h"
#include "draw_sprite.h"
#include "draw_status.h"
#include "status_definitions.h"
#include "natfeats.h"
#include "random.h"
#include "initialise.h"
#include "hud.h"
#include "hud_digits.h"

#define HARDWARE_PLAYFIELD_COUNT 3

static int16_t visible_index;
volatile static int16_t ready_index;
static int16_t drawing_index;

uint16_t hardware_playfield_shaking = 0;

struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];

void hardware_playfield_handle_vbl()
{
	if (ready_index >= 0) {
		visible_index = ready_index;
		ready_index = -1;

        if (hardware_playfield_shaking) {
            vertical_shift = random() & 3;
        } else {
            vertical_shift = 0;
        }

        // no idea why we specify drawing_index here
        uint32_t visible_buffer_address = hardware_playfields[visible_index].buffer;
        if (vertical_shift != 0) {
            visible_buffer_address -= vertical_shift * 160;
        }
        uint8_t address_high_byte = (uint8_t)((visible_buffer_address >> 16) & 0xff);
        uint8_t address_mid_byte = (uint8_t)((visible_buffer_address >> 8) & 0xff);
        uint8_t address_low_byte = (uint8_t)(visible_buffer_address & 0xff);

        *((volatile uint8_t *)0xffff8201) = address_high_byte;
        *((volatile uint8_t *)0xffff8203) = address_mid_byte;
        *((volatile uint8_t *)0xffff820d) = address_low_byte;

        *((volatile uint8_t *)0xffff8205) = address_high_byte;
        *((volatile uint8_t *)0xffff8207) = address_mid_byte;
        *((volatile uint8_t *)0xffff8209) = address_low_byte;
		/*Setscreen(
            hardware_playfields[visible_index].buffer,
            hardware_playfields[drawing_index].buffer,
            -1
        );*/
    } else {
        //nf_print("Frame dropped :(");
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

    draw_status(
        status_definitions[STATUS_DEFS_TIME].words, // confirmed correct
        &hardware_playfield->buffer[160 * 8 + (8 * 8)],
        status_definitions[STATUS_DEFS_TIME].source_data_width_pixels,
        status_definitions[STATUS_DEFS_TIME].source_data_height_lines,
        13
    );

    draw_status(
        status_definitions[STATUS_DEFS_HIGH].words, // confirmed correct
        &hardware_playfield->buffer[160 * 8 + (1 * 8)],
        status_definitions[STATUS_DEFS_HIGH].source_data_width_pixels,
        status_definitions[STATUS_DEFS_HIGH].source_data_height_lines,
        0
    );

    draw_status(
        status_definitions[STATUS_DEFS_SCORE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 8 + (14 * 8)],
        status_definitions[STATUS_DEFS_SCORE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_SCORE].source_data_height_lines,
        0
    );

    draw_status(
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 19 + (8 * 8)],
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_height_lines,
        13 // 128 + 13 = 141
    );

    draw_status(
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 19 + (10 * 8)],
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_height_lines,
        2 // 160 + 2 = 162
    );

    for (uint16_t index = 0; index < TIME_DIGIT_COUNT; index++) {
        hardware_playfield->hud_digits.time_digits[index] = -1;
    }

    for (uint16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        hardware_playfield->hud_digits.score_digits[index] = -1;
        hardware_playfield->hud_digits.high_score_digits[index] = -1;
    }
}

void hardware_playfield_init()
{
    hardware_playfield_shaking = 0;

    visible_index = 0;
    ready_index = -1;
    drawing_index = 1;

    uint16_t *phys_base = Physbase();
    hardware_playfields[0].buffer = phys_base;
    hardware_playfields[1].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES / 2;
    hardware_playfields[2].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES;

    for (uint16_t index = 0; index < HARDWARE_PLAYFIELD_COUNT; index++) {
        hardware_playfield_init_playfield(&hardware_playfields[index]);
    }
}

void hardware_playfield_update_digits()
{
    struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    int8_t desired_digit;
    struct StatusDefinition *status_definition;

    for (uint16_t index = 0; index <= 1; index++) {
        desired_digit = hud_digits.time_digits[index];
        if (desired_digit != playfield->hud_digits.time_digits[index]) {
            playfield->hud_digits.time_digits[index] = desired_digit;
            status_definition = &status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE + desired_digit];
            if (index == 0) {
                draw_status(
                    status_definition->words, // confirmed correct
                    &playfield->buffer[160 * 19 + (8 * 8)],
                    status_definition->source_data_width_pixels,
                    status_definition->source_data_height_lines,
                    13 // 128 + 13 = 141
                );
            } else {
                draw_status(
                    status_definition->words, // confirmed correct
                    &playfield->buffer[160 * 19 + (10 * 8)],
                    status_definition->source_data_width_pixels,
                    status_definition->source_data_height_lines,
                    2 // 160 + 2 = 162
                );
            }
        }
    }

    uint16_t blocks_across;
    uint16_t skew;
    for (int16_t index = SCORE_DIGIT_COUNT - 1; index >=0; index--) {
        desired_digit = hud_digits.score_digits[index];
        if (desired_digit != playfield->hud_digits.score_digits[index]) {
            playfield->hud_digits.score_digits[index] = desired_digit;
            status_definition = &status_definitions[STATUS_DEFS_SMALL_DIGITS_BASE + desired_digit];
            // start at xpos = 9, increment by 9
            switch (index) {
                case 0:
                    blocks_across = 0;
                    skew = 9;
                    break;
                case 1:
                    // 18 = 16 + 2
                    blocks_across = 1;
                    skew = 2;
                    break;
                case 2:
                    // 27 = 16 + 11
                    blocks_across = 1;
                    skew = 11;
                    break;
                case 3:
                    // 36 = 32 + 4
                    blocks_across = 2;
                    skew = 4;
                    break;
                case 4:
                    // 45 = 32 + 13
                    blocks_across = 2;
                    skew = 13;
                    break;
                case 5:
                    // 54 = 48 + 6
                    blocks_across = 3;
                    skew = 6;
                    break;
                case 6:
                    // 63 = 48 + 15
                    blocks_across = 3;
                    skew = 15;
                    break;
                case 7:
                    // 72 = 64 + 6
                    blocks_across = 4;
                    skew = 8;
                    break;
            }
            draw_status(
                status_definition->words, // confirmed correct
                &playfield->buffer[160 * 19 + (blocks_across * 8)],
                status_definition->source_data_width_pixels,
                status_definition->source_data_height_lines,
                skew
            );
        }
    }
}

static void hardware_playfield_error()
{
    while(1) {};
}

void hardware_playfield_frame_complete()
{
    // is there already a frame ready?
    // if so, wait until the vbl has happened
    while (ready_index >= 0) {}

    ready_index = drawing_index;
    drawing_index++;
    if (drawing_index == HARDWARE_PLAYFIELD_COUNT) {
        drawing_index = 0;
    }
}

struct HardwarePlayfield *hardware_playfield_get_drawing_playfield()
{
    return &hardware_playfields[drawing_index];
}


