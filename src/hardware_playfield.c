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
#include "time_of_day_process.h"
#include "stars.h"
#include "hardware_playfield_fast.h"

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

void hardware_playfield_set_visible_address(uint32_t visible_buffer_address)
{
    uint8_t address_high_byte = (uint8_t)((visible_buffer_address >> 16) & 0xff);
    uint8_t address_mid_byte = (uint8_t)((visible_buffer_address >> 8) & 0xff);
    uint8_t address_low_byte = (uint8_t)(visible_buffer_address & 0xff);

    *((volatile uint8_t *)0xffff8201) = address_high_byte;
    *((volatile uint8_t *)0xffff8203) = address_mid_byte;
    *((volatile uint8_t *)0xffff820d) = address_low_byte;

    *((volatile uint8_t *)0xffff8205) = address_high_byte;
    *((volatile uint8_t *)0xffff8207) = address_mid_byte;
    *((volatile uint8_t *)0xffff8209) = address_low_byte;
}

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

        uint32_t visible_buffer_address = hardware_playfields[visible_index].buffer;
        if (vertical_shift != 0) {
            visible_buffer_address -= multiply_160[vertical_shift];
        }

        hardware_playfield_set_visible_address(visible_buffer_address);
    } else {
#ifdef __NATFEATS_DEBUG
        nf_print("Frame dropped :(");
#endif
    }
}

void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    int16_t normalised_xpos = xpos - sprite_definition->origin_x;
    int16_t normalised_ypos = ypos - sprite_definition->origin_y;
    struct BitplaneDrawRecord *current_bitplane_draw_record = drawing_playfield->current_bitplane_draw_record;
    uint16_t skew;
    
    // TODO: simplify this interface so we're not pushing so much on the stack!
    uint16_t sprite_drawn = draw_sprite(
        normalised_xpos,
        normalised_ypos,
        sprite_definition->words,
        sprite_definition->source_data_width,
        sprite_definition->source_data_height,
        drawing_playfield->buffer,
        current_bitplane_draw_record,
        &(sprite_definition->compiled_sprite_0)
    );

    if (sprite_drawn) {
        if (normalised_ypos < drawing_playfield->tallest_sprite_ypos) {
            drawing_playfield->tallest_sprite_ypos = normalised_ypos;
        }

        if (sprite_drawn == 2) {
            skew = normalised_xpos & 0xe;
            // TODO: this needs to be changed to something like highest_tolerable_skew
            // we can get rid of some of the arithmetic
            if (skew) {
                if ((sprite_definition->longest_right_end + skew) <= 16) {
                    current_bitplane_draw_record->x_count--;
                    current_bitplane_draw_record->destination_y_increment += 8;
                }
            }
        }

        drawing_playfield->current_bitplane_draw_record++;
    }
}

void hardware_playfield_erase_sprites()
{
    hardware_playfield_erase_sprites_fast(
        drawing_playfield->current_bitplane_draw_record,
        drawing_playfield->buffer,
        drawing_playfield->bitplane_draw_records
    );

    drawing_playfield->current_bitplane_draw_record = drawing_playfield->bitplane_draw_records;
    drawing_playfield->tallest_sprite_ypos = 200;
}

static uint16_t hardware_playfield_update_scoring_digits(struct ScoreDrawingPosition *current_score_drawing_position, uint8_t *desired_score_digits, uint8_t *current_score_digits, struct HardwarePlayfield *hardware_playfield)
{
    struct StatusDefinition *status_definition;
    uint8_t *desired_digit = desired_score_digits;
    uint8_t *current_digit = current_score_digits;
    uint16_t changed_digits = 0;

    for (int16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        if (*desired_digit != *current_digit) {
            changed_digits++;
            *current_digit = *desired_digit;
            status_definition = &status_definitions[STATUS_DEFS_SMALL_DIGITS_BASE + *desired_digit];
            draw_status(
                status_definition->words,
                &hardware_playfield->buffer[160 * 19 + (current_score_drawing_position->blocks_across * 8)],
                status_definition->source_data_width_pixels,
                status_definition->source_data_height_lines,
                current_score_drawing_position->skew
            );

        }
        current_score_drawing_position++;
        desired_digit++;
        current_digit++;
    }

    return changed_digits;
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

    hardware_playfield->current_bitplane_draw_record = hardware_playfield->bitplane_draw_records;
    hardware_playfield->stars_drawn = 0;
    hardware_playfield->mountains_scroll_pixels = -1;
    hardware_playfield->hud_redraw_required = 1;

    uint16_t word1,word2,word3,word4;
    uint16_t *current_dest = hardware_playfield->buffer;
    uint16_t line_colour;
    for (uint16_t line = 0; line < 90; line++) {
        line_colour = line_background_colours_2[line];

        word1 = (line_colour & 1) ? 0xffff : 0;
        word2 = (line_colour >> 1 & 1) ? 0xffff: 0;
        word3 = (line_colour >> 2 & 1) ? 0xffff: 0;
        word4 = (line_colour >> 3 & 1) ? 0xffff: 0;

        for (uint16_t iterations = 0; iterations < 20; iterations++) {
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
        high_score_drawing_positions,
        hud_digits.high_score_digits,
        hardware_playfield->hud_digits.high_score_digits,
        hardware_playfield
    );

    hardware_playfield_update_scoring_digits(
        score_drawing_positions,
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

void hardware_playfield_copy_score()
{
    *((volatile int16_t *)BLITTER_ENDMASK_1) = 0x3;
    *((volatile int16_t *)BLITTER_ENDMASK_2) = -1;
    *((volatile int16_t *)BLITTER_ENDMASK_3) = 0xfc00;
    *((volatile int16_t *)BLITTER_SOURCE_X_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_SOURCE_Y_INCREMENT) = (160 - 8 * 5) - 8; // originally 158
    *((volatile int16_t *)BLITTER_DESTINATION_X_INCREMENT) = 8;
    *((volatile int16_t *)BLITTER_DESTINATION_Y_INCREMENT) = (160 - 8*5);
    *((volatile int16_t *)BLITTER_X_COUNT) = 6;
    *((volatile uint16_t *)BLITTER_HOP_OP) = 0x0203;

    uint32_t drawing_playfield_buffer = (uint32_t)(drawing_playfield->buffer);
    uint32_t source = (drawing_playfield_buffer + 160*19) - 8;
    uint32_t destination = (drawing_playfield_buffer + 160*19) + (8 * 14);
    uint16_t blitter_control_word = 0xc085;

    for (uint16_t index = 0; index < 4; index++) {
        *((volatile uint32_t *)BLITTER_SOURCE_ADDRESS) = source; // 8a32
        *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = destination; // 8a32
        *((volatile int16_t *)BLITTER_Y_COUNT) = 9; // 8a38
        *((volatile uint16_t *)BLITTER_CONTROL) = blitter_control_word; // 8a3c
        source += 2;
        destination += 2;
    }
}

void hardware_playfield_update_digits()
{
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

    // TODO: we should be able to shortcut the logic in here
    // can we have a score value held against the playfield?
    hardware_playfield_update_scoring_digits(
        &score_drawing_positions,
        hud_digits.score_digits,
        drawing_playfield->hud_digits.score_digits,
        drawing_playfield
    );

    if (hud_score_is_high_score()) {
        hardware_playfield_copy_score();
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

    drawing_playfield = &hardware_playfields[drawing_index];
}

void hardware_playfield_hud_redraw_required()
{
    struct HardwarePlayfield *current_hardware_playfield = hardware_playfields;
    current_hardware_playfield->hud_redraw_required = 1;    
    current_hardware_playfield++;
    current_hardware_playfield->hud_redraw_required = 1;    
    current_hardware_playfield++;
    current_hardware_playfield->hud_redraw_required = 1;    
}
