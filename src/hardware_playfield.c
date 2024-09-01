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
struct HardwarePlayfield *drawing_playfield;

uint16_t hardware_playfield_shaking = 0;

struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];

uint16_t multiply_160[] = {
    0,
    160,
    320,
    480,
    640,
    800,
    960,
    1120,
    1280,
    1440,
    1600,
    1760,
    1920,
    2080,
    2240,
    2400,
    2560,
    2720,
    2880,
    3040,
    3200,
    3360,
    3520,
    3680,
    3840,
    4000,
    4160,
    4320,
    4480,
    4640,
    4800,
    4960,
    5120,
    5280,
    5440,
    5600,
    5760,
    5920,
    6080,
    6240,
    6400,
    6560,
    6720,
    6880,
    7040,
    7200,
    7360,
    7520,
    7680,
    7840,
    8000,
    8160,
    8320,
    8480,
    8640,
    8800,
    8960,
    9120,
    9280,
    9440,
    9600,
    9760,
    9920,
    10080,
    10240,
    10400,
    10560,
    10720,
    10880,
    11040,
    11200,
    11360,
    11520,
    11680,
    11840,
    12000,
    12160,
    12320,
    12480,
    12640,
    12800,
    12960,
    13120,
    13280,
    13440,
    13600,
    13760,
    13920,
    14080,
    14240,
    14400,
    14560,
    14720,
    14880,
    15040,
    15200,
    15360,
    15520,
    15680,
    15840,
    16000,
    16160,
    16320,
    16480,
    16640,
    16800,
    16960,
    17120,
    17280,
    17440,
    17600,
    17760,
    17920,
    18080,
    18240,
    18400,
    18560,
    18720,
    18880,
    19040,
    19200,
    19360,
    19520,
    19680,
    19840,
    20000,
    20160,
    20320,
    20480,
    20640,
    20800,
    20960,
    21120,
    21280,
    21440,
    21600,
    21760,
    21920,
    22080,
    22240,
    22400,
    22560,
    22720,
    22880,
    23040,
    23200,
    23360,
    23520,
    23680,
    23840,
    24000,
    24160,
    24320,
    24480,
    24640,
    24800,
    24960,
    25120,
    25280,
    25440,
    25600,
    25760,
    25920,
    26080,
    26240,
    26400,
    26560,
    26720,
    26880,
    27040,
    27200,
    27360,
    27520,
    27680,
    27840,
    28000,
    28160,
    28320,
    28480,
    28640,
    28800,
    28960,
    29120,
    29280,
    29440,
    29600,
    29760,
    29920,
    30080,
    30240,
    30400,
    30560,
    30720,
    30880,
    31040,
    31200,
    31360,
    31520,
    31680,
    31840,
    32000,
    32160,
    32320,
    32480,
    32640,
    32800,
    32960,
    33120,
    33280,
    33440,
    33600,
    33760,
    33920,
    34080,
    34240,
    34400,
    34560,
    34720,
    34880,
    35040,
    35200,
    35360,
    35520,
    35680,
    35840,
    36000,
    36160,
    36320,
    36480,
    36640,
    36800,
    36960,
    37120,
    37280,
    37440,
    37600,
    37760,
    37920,
    38080,
    38240,
    38400,
    38560,
    38720,
    38880,
    39040,
    39200,
    39360,
    39520,
    39680,
    39840,
    40000,
    40160,
    40320,
    40480,
    40640,
    40800
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
        nf_print("Frame dropped :(");
    }
}

void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    //struct HardwarePlayfield *playfield = hardware_playfield_get_drawing_playfield();

    draw_sprite(
        xpos - sprite_definition->origin_x,
        ypos - sprite_definition->origin_y,
        sprite_definition->words,
        sprite_definition->source_data_width,
        sprite_definition->source_data_height,
        drawing_playfield->buffer,
        drawing_playfield->current_bitplane_draw_record,
        &(sprite_definition->compiled_sprite_0)
    );

    drawing_playfield->current_bitplane_draw_record++;
    drawing_playfield->sprites_drawn++;
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
        if (destination_address != 0) {
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
        }

        current_bitplane_draw_record++;
    }

    drawing_playfield->current_bitplane_draw_record = drawing_playfield->bitplane_draw_records;
    drawing_playfield->sprites_drawn = 0;
}

static void hardware_playfield_init_playfield(struct HardwarePlayfield *hardware_playfield)
{
    memset(hardware_playfield->buffer, 0, HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES);
    hardware_playfield->current_bitplane_draw_record = hardware_playfield->bitplane_draw_records;
    hardware_playfield->sprites_drawn = 0;
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
    drawing_playfield = &hardware_playfields[drawing_index];

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
                    13 // 128 + 13 = 141
                );
            } else {
                draw_status(
                    status_definition->words, // confirmed correct
                    &drawing_playfield->buffer[160 * 19 + (10 * 8)],
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
        if (desired_digit != drawing_playfield->hud_digits.score_digits[index]) {
            drawing_playfield->hud_digits.score_digits[index] = desired_digit;
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
                &drawing_playfield->buffer[160 * 19 + (blocks_across * 8)],
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

    drawing_playfield = &hardware_playfields[drawing_index];
}

/*struct HardwarePlayfield *hardware_playfield_get_drawing_playfield()
{
    return &hardware_playfields[drawing_index];
}*/


