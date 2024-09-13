#include <mint/osbind.h>
#include <mint/sysbind.h>
#include <inttypes.h>
#include <string.h>
#include <stdio.h>
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
#include "lookups.h"
#include "player_car.h"

static int16_t visible_index;
volatile static int16_t ready_index;
static int16_t drawing_index;
struct HardwarePlayfield *drawing_playfield;

uint16_t hardware_playfield_shaking = 0;

struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];

struct ScoreDrawingPosition {
    uint16_t blocks_across;
    uint16_t skew;
};

struct ScoreDrawingPosition score_drawing_positions[] = {
    {0, 9}, // 9
    {1, 2},
    {1, 11},
    {2, 4},
    {2, 13},
    {3, 6},
    {3, 15},
    {4, 8}
};

struct ScoreDrawingPosition high_score_drawing_positions[] = {
    {14, 14}, // 238
    {15, 7}, // 247
    {16, 0}, // 256
    {16, 9}, // 265
    {17, 2}, // 274
    {17, 11}, // 283
    {18, 4}, // 292
    {18, 13} // 301
};

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
            // TODO: table lookup for multiply by 160
            visible_buffer_address -= multiply_160[vertical_shift];
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
#ifdef __NATFEATS_DEBUG
        nf_print("Frame dropped :(");
#endif
    }
}

void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    //struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    // need a return value from draw_sprite if nothing gets draw
    // so that we don't advance current_bitplane_draw_record
    uint16_t sprite_drawn = draw_sprite(
        xpos - sprite_definition->origin_x,
        ypos - sprite_definition->origin_y,
        sprite_definition->words,
        sprite_definition->source_data_width,
        sprite_definition->source_data_height,
        drawing_playfield->buffer,
        drawing_playfield->current_bitplane_draw_record,
        &(sprite_definition->compiled_sprite_0)
    );

    if (sprite_drawn) {
        drawing_playfield->current_bitplane_draw_record++;
    }
    //drawing_playfield->sprites_drawn++;
}

void hardware_playfield_copy_and_erase_previous_bitplane_draw_record(struct BitplaneDrawRecord *destination_bitplane_draw_record)
{
    drawing_playfield->current_bitplane_draw_record--;
    *destination_bitplane_draw_record = *drawing_playfield->current_bitplane_draw_record;
}

void hardware_playfield_erase_sprites()
{
    //struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    struct BitplaneDrawRecord *current_bitplane_draw_record = drawing_playfield->bitplane_draw_records;

    int16_t lines_to_draw;
    uint8_t *destination_address;

    *((volatile uint16_t *)BLITTER_HOP_OP) = 0;
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8; // TODO: check value
    *((volatile int16_t *)BLITTER_ENDMASK_1) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = -1;

    //for (uint16_t index = drawing_playfield->sprites_drawn; index > 0; index--) {
    while (current_bitplane_draw_record < drawing_playfield->current_bitplane_draw_record) {
        // road draws in bitplanes 0 and 1, so we only need to clear bitplanes 2 and 3
        // we will probably draw background in planes 0 and 1 too...
        destination_address = current_bitplane_draw_record->destination_address;

        *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = current_bitplane_draw_record->destination_y_increment;
        *((volatile int16_t *)BLITTER_X_COUNT) = current_bitplane_draw_record->x_count;

        if (current_bitplane_draw_record->ypos < 90) {
            lines_to_draw = 90 - current_bitplane_draw_record->ypos;

            *((volatile uint16_t *)BLITTER_HOP_OP) = 0xf;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;
            destination_address += 2;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;
            destination_address += 2;

            *((volatile uint16_t *)BLITTER_HOP_OP) = 0;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;
            destination_address += 2;

            *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
            *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
            *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

            destination_address -= 2;
            destination_address += multiply_160[lines_to_draw];
            lines_to_draw = current_bitplane_draw_record->y_count - lines_to_draw;
        } else {
            lines_to_draw = current_bitplane_draw_record->y_count;
            destination_address += 4;
        }

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
        *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
        *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

        destination_address += 2;

        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination_address;
        *((volatile int16_t *)BLITTER_Y_COUNT) = lines_to_draw;
        *((volatile uint8_t *)BLITTER_CONTROL) = 0xc0;

        current_bitplane_draw_record++;
    }

    drawing_playfield->current_bitplane_draw_record = drawing_playfield->bitplane_draw_records;
    drawing_playfield->sprites_drawn = 0;
}

static void hardware_playfield_update_scoring_digits(struct ScoreDrawingPosition *current_score_drawing_position, uint8_t *desired_score_digits, uint8_t *current_score_digits, struct HardwarePlayfield *hardware_playfield)
{
    int8_t desired_digit;
    struct StatusDefinition *status_definition;

    for (int16_t index = SCORE_DIGIT_COUNT - 1; index >=0; index--) {
        desired_digit = desired_score_digits[index];
        if (desired_digit != current_score_digits[index]) {
            current_score_digits[index] = desired_digit;
            status_definition = &status_definitions[STATUS_DEFS_SMALL_DIGITS_BASE + desired_digit];
            draw_status(
                status_definition->words, // confirmed correct
                &hardware_playfield->buffer[160 * 19 + (current_score_drawing_position->blocks_across * 8)],
                status_definition->source_data_width_pixels,
                status_definition->source_data_height_lines,
                current_score_drawing_position->skew
            );

        }
        current_score_drawing_position--;
    }
}

static void hardware_playfield_init_playfield(struct HardwarePlayfield *hardware_playfield)
{
#ifdef __NATFEATS_DEBUG
            // relocation required
            snprintf(
                nf_strbuf,
                256,
                "clearing playfield at address: %x\n",
                hardware_playfield->buffer
            );

            nf_print(nf_strbuf);
#endif

    memset(hardware_playfield->buffer, 0, HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES);

    /*uint8_t *bufptr = hardware_playfield->buffer;
    for (int index = 0; index < HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES; index++) {
        *bufptr = index & 255;
        bufptr++;
    }*/

    hardware_playfield->current_bitplane_draw_record = hardware_playfield->bitplane_draw_records;
    //hardware_playfield->sprites_drawn = 0;
    hardware_playfield->stars_drawn = 0;

    uint16_t word1,word2,word3,word4;
    uint16_t current_stripe_iterations;
    uint16_t mapped_stripe_index;

    uint16_t *current_dest = hardware_playfield->buffer;
    for (uint16_t stripe_index = 15; stripe_index >= 3; stripe_index--) {
        // 80 words per line/20 iterations per line
        // 5 lines per stripe
        // so 80 iterations per stripe
        mapped_stripe_index = stripe_index;
        if (mapped_stripe_index == 15) {
            mapped_stripe_index = 2;
        }

        word1 = (mapped_stripe_index & 1) ? 0xffff : 0;
        word2 = (mapped_stripe_index >> 1 & 1) ? 0xffff: 0;
        word3 = (mapped_stripe_index >> 2 & 1) ? 0xffff: 0;
        word4 = (mapped_stripe_index >> 3 & 1) ? 0xffff: 0;

        current_stripe_iterations = 5*20;
        if (mapped_stripe_index == 3) {
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
        status_definitions[STATUS_DEFS_SCORE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 8],
        status_definitions[STATUS_DEFS_SCORE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_SCORE].source_data_height_lines,
        9
    );

    draw_status(
        status_definitions[STATUS_DEFS_HIGH].words, // confirmed correct
        &hardware_playfield->buffer[160 * 8 + (17 * 8)],
        status_definitions[STATUS_DEFS_HIGH].source_data_width_pixels,
        status_definitions[STATUS_DEFS_HIGH].source_data_height_lines,
        2
    );

    draw_status(
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 19 + (8 * 8)],
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_height_lines,
        15 // 128 + 13 = 141
    );

    draw_status(
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].words, // confirmed correct
        &hardware_playfield->buffer[160 * 19 + (10 * 8)],
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_width_pixels,
        status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE].source_data_height_lines,
        0 // 160 + 2 = 162
    );

    for (uint16_t index = 0; index < TIME_DIGIT_COUNT; index++) {
        hardware_playfield->hud_digits.time_digits[index] = -1;
    }

    for (uint16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        hardware_playfield->hud_digits.score_digits[index] = -1;
        hardware_playfield->hud_digits.high_score_digits[index] = -1;
    }

    // TODO: i think we need to get rid of hardware_playfield->hud_digits.high_score_digits if possible
    hardware_playfield_update_scoring_digits(
        &high_score_drawing_positions[7],
        hud_digits.high_score_digits,
        hardware_playfield->hud_digits.high_score_digits,
        hardware_playfield
    );

    hardware_playfield_update_scoring_digits(
        &score_drawing_positions[7],
        hud_digits.score_digits,
        hardware_playfield->hud_digits.score_digits,
        hardware_playfield
    );
}

void hardware_playfield_global_init()
{
    uint16_t *phys_base = Physbase();
    hardware_playfields[0].buffer = phys_base;
    hardware_playfields[1].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES / 2;
    hardware_playfields[2].buffer = phys_base - HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES;
}

void hardware_playfield_init()
{
    hardware_playfield_shaking = 0;

    visible_index = 0;
    ready_index = -1;
    drawing_index = 1;
    drawing_playfield = &hardware_playfields[drawing_index];

    for (uint16_t index = 0; index < HARDWARE_PLAYFIELD_COUNT; index++) {
        hardware_playfield_init_playfield(&hardware_playfields[index]);
    }
}

void hardware_playfield_update_digits()
{
    //struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    int8_t desired_digit;
    struct StatusDefinition *status_definition;

    for (uint16_t index = 0; index <= 1; index++) {
        desired_digit = hud_digits.time_digits[index];
        if (desired_digit != drawing_playfield->hud_digits.time_digits[index]) {
            drawing_playfield->hud_digits.time_digits[index] = desired_digit;
            status_definition = &status_definitions[STATUS_DEFS_LARGE_DIGITS_BASE + desired_digit];
            if (index == 0) {
                draw_status(
                    status_definition->words, // confirmed correct
                    &drawing_playfield->buffer[160 * 19 + (8 * 8)],
                    status_definition->source_data_width_pixels,
                    status_definition->source_data_height_lines,
                    15 // 128 + 13 = 143
                );
            } else {
                draw_status(
                    status_definition->words, // confirmed correct
                    &drawing_playfield->buffer[160 * 19 + (10 * 8)],
                    status_definition->source_data_width_pixels,
                    status_definition->source_data_height_lines,
                    0 // 160 + 2 = 160
                );
            }
        }
    }

    hardware_playfield_update_scoring_digits(
        &score_drawing_positions[7],
        hud_digits.score_digits,
        drawing_playfield->hud_digits.score_digits,
        drawing_playfield
    );
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

    drawing_playfield = &hardware_playfields[drawing_index];
}


