#include "speedometer.h"
#include "player_car.h"
#include "hardware_playfield.h"
#include "sprite_definitions.h"
#include "lookups.h"
#include "draw_sprite.h"
#include <string.h>

#define SPEEDO_DEFINITION_OFFSET 237
#define SPEEDO_DIGITS_OFFSET_BASE 238

uint8_t digit_lookup[] = {
    0,0,0,0,
    0,0,1,0,
    0,0,2,0,
    0,0,3,0,
    0,0,4,0,
    0,0,5,0,
    0,0,6,0,
    0,0,7,0,
    0,0,8,0,
    0,0,9,0,
    0,1,0,0,
    0,1,1,0,
    0,1,2,0,
    0,1,3,0,
    0,1,4,0,
    0,1,5,0,
    0,1,6,0,
    0,1,7,0,
    0,1,8,0,
    0,1,9,0,
    0,2,0,0,
    0,2,1,0,
    0,2,2,0,
    0,2,3,0,
    0,2,4,0,
    0,2,5,0,
    0,2,6,0,
    0,2,7,0,
    0,2,8,0,
    0,2,9,0,
    0,3,0,0,
    0,3,1,0,
    0,3,2,0,
    0,3,3,0,
    0,3,4,0,
    0,3,5,0,
    0,3,6,0,
    0,3,7,0,
    0,3,8,0,
    0,3,9,0,
    0,4,0,0,
    0,4,1,0,
    0,4,2,0,
    0,4,3,0,
    0,4,4,0,
    0,4,5,0,
    0,4,6,0,
    0,4,7,0,
    0,4,8,0,
    0,4,9,0,
    0,5,0,0,
    0,5,1,0,
    0,5,2,0,
    0,5,3,0,
    0,5,4,0,
    0,5,5,0,
    0,5,6,0,
    0,5,7,0,
    0,5,8,0,
    0,5,9,0,
    0,6,0,0,
    0,6,1,0,
    0,6,2,0,
    0,6,3,0,
    0,6,4,0,
    0,6,5,0,
    0,6,6,0,
    0,6,7,0,
    0,6,8,0,
    0,6,9,0,
    0,7,0,0,
    0,7,1,0,
    0,7,2,0,
    0,7,3,0,
    0,7,4,0,
    0,7,5,0,
    0,7,6,0,
    0,7,7,0,
    0,7,8,0,
    0,7,9,0,
    0,8,0,0,
    0,8,1,0,
    0,8,2,0,
    0,8,3,0,
    0,8,4,0,
    0,8,5,0,
    0,8,6,0,
    0,8,7,0,
    0,8,8,0,
    0,8,9,0,
    0,9,0,0,
    0,9,1,0,
    0,9,2,0,
    0,9,3,0,
    0,9,4,0,
    0,9,5,0,
    0,9,6,0,
    0,9,7,0,
    0,9,8,0,
    0,9,9,0,
    1,0,0,0,
    1,0,1,0,
    1,0,2,0,
    1,0,3,0,
    1,0,4,0,
    1,0,5,0,
    1,0,6,0,
    1,0,7,0,
    1,0,8,0,
    1,0,9,0,
    1,1,0,0,
    1,1,1,0,
    1,1,2,0,
    1,1,3,0,
    1,1,4,0,
    1,1,5,0,
    1,1,6,0,
    1,1,7,0,
    1,1,8,0,
    1,1,9,0,
    1,2,0,0,
    1,2,1,0,
    1,2,2,0,
    1,2,3,0,
    1,2,4,0,
    1,2,5,0,
    1,2,6,0,
    1,2,7,0,
    1,2,8,0,
    1,2,9,0,
    1,3,0,0,
    1,3,1,0,
    1,3,2,0,
    1,3,3,0,
    1,3,4,0,
    1,3,5,0,
    1,3,6,0,
    1,3,7,0,
    1,3,8,0,
    1,3,9,0,
    1,4,0,0,
    1,4,1,0,
    1,4,2,0,
    1,4,3,0,
    1,4,4,0,
    1,4,5,0,
    1,4,6,0,
    1,4,7,0,
    1,4,8,0,
    1,4,9,0,
    1,5,0,0,
    1,5,1,0,
    1,5,2,0,
    1,5,3,0,
    1,5,4,0,
    1,5,5,0,
    1,5,6,0,
    1,5,7,0,
    1,5,8,0,
    1,5,9,0,
    1,6,0,0,
    1,6,1,0,
    1,6,2,0,
    1,6,3,0,
    1,6,4,0,
    1,6,5,0,
    1,6,6,0,
    1,6,7,0,
    1,6,8,0,
    1,6,9,0,
    1,7,0,0,
    1,7,1,0,
    1,7,2,0,
    1,7,3,0,
    1,7,4,0,
    1,7,5,0,
    1,7,6,0,
    1,7,7,0,
    1,7,8,0,
    1,7,9,0,
    1,8,0,0,
    1,8,1,0,
    1,8,2,0,
    1,8,3,0,
    1,8,4,0,
    1,8,5,0,
    1,8,6,0,
    1,8,7,0,
    1,8,8,0,
    1,8,9,0,
    1,9,0,0,
    1,9,1,0,
    1,9,2,0,
    1,9,3,0,
    1,9,4,0,
    1,9,5,0,
    1,9,6,0,
    1,9,7,0,
    1,9,8,0,
    1,9,9,0,
    2,0,0,0,
    2,0,1,0,
    2,0,2,0,
    2,0,3,0,
    2,0,4,0,
    2,0,5,0,
    2,0,6,0,
    2,0,7,0,
    2,0,8,0,
    2,0,9,0,
    2,1,0,0,
    2,1,1,0,
    2,1,2,0,
    2,1,3,0,
    2,1,4,0,
    2,1,5,0,
    2,1,6,0,
    2,1,7,0,
    2,1,8,0,
    2,1,9,0,
    2,2,0,0,
    2,2,1,0,
    2,2,2,0,
    2,2,3,0,
    2,2,4,0,
    2,2,5,0,
    2,2,6,0,
    2,2,7,0,
    2,2,8,0,
    2,2,9,0,
    2,3,0,0,
    2,3,1,0,
    2,3,2,0,
    2,3,3,0,
    2,3,4,0,
    2,3,5,0,
    2,3,6,0,
    2,3,7,0,
    2,3,8,0,
    2,3,9,0,
    2,4,0,0,
    2,4,1,0,
    2,4,2,0,
    2,4,3,0,
    2,4,4,0,
    2,4,5,0,
    2,4,6,0,
    2,4,7,0,
    2,4,8,0,
    2,4,9,0,
    2,5,0,0,
    2,5,1,0,
    2,5,2,0,
    2,5,3,0,
    2,5,4,0,
    2,5,5,0,
    2,5,6,0,
    2,5,7,0,
    2,5,8,0,
    2,5,9,0,
    2,6,0,0,
    2,6,1,0,
    2,6,2,0,
    2,6,3,0,
    2,6,4,0,
    2,6,5,0,
    2,6,6,0,
    2,6,7,0,
    2,6,8,0,
    2,6,9,0,
    2,7,0,0,
    2,7,1,0,
    2,7,2,0,
    2,7,3,0,
    2,7,4,0,
    2,7,5,0,
    2,7,6,0,
    2,7,7,0,
    2,7,8,0,
    2,7,9,0,
    2,8,0,0,
    2,8,1,0,
    2,8,2,0,
    2,8,3,0,
    2,8,4,0,
    2,8,5,0,
    2,8,6,0,
    2,8,7,0,
    2,8,8,0,
    2,8,9,0,
    2,9,0,0,
    2,9,1,0,
    2,9,2,0,
    2,9,3,0,
    2,9,4,0,
    2,9,5,0,
    2,9,6,0,
    2,9,7,0,
    2,9,8,0,
    2,9,9,0,
    3,0,0,0
};

struct Speedometer {
    uint16_t *words;
    int8_t digits[3];
};

static uint16_t speedometer_2_words[(8*3*26)/2]; // 10 bytes per 10 pixels
static struct Speedometer speedometer_1;
static struct Speedometer speedometer_2;
static struct Speedometer *visible_speedometer;
static struct Speedometer *hidden_speedometer;
static uint16_t wip_speed;
static uint16_t wip_digit_index;

static void speedometer_init_speedometer(struct Speedometer *speedometer, uint8_t *words)
{
    speedometer->words = words;
    speedometer->digits[0] = -1; 
    speedometer->digits[1] = -1; 
    speedometer->digits[2] = -1; 
}

void speedometer_init()
{
    speedometer_init_speedometer(&speedometer_1, sprite_definitions[SPEEDO_DEFINITION_OFFSET].words);
    speedometer_init_speedometer(&speedometer_2, speedometer_2_words);
    memcpy(speedometer_2.words, speedometer_1.words, 8*3*26);
    visible_speedometer = &speedometer_1;
    hidden_speedometer = &speedometer_2;
    wip_digit_index = 0;
    wip_speed = 0;
}

void speedometer_update()
{
    uint8_t desired_digit = digit_lookup[(wip_speed << 2) + wip_digit_index];
    uint8_t existing_digit = hidden_speedometer->digits[wip_digit_index];
    struct SpriteDefinition *sprite_definition;

    if (desired_digit != existing_digit) {
        sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + desired_digit];

        switch (wip_digit_index) {
            case 0:
                draw_compiled_sprite(
                    sprite_definition->words,
                    hidden_speedometer->words + (12*10),
                    &(sprite_definition->compiled_sprite_0),
                    4
                );
                break;
            case 1:
                draw_compiled_sprite(
                    sprite_definition->words,
                    hidden_speedometer->words + (12*10),
                    &(sprite_definition->compiled_sprite_0),
                    15
                );
                break;
            case 2:
                draw_compiled_sprite(
                    sprite_definition->words,
                    visible_speedometer->words + (12*10) + 4,
                    &(sprite_definition->compiled_sprite_0),
                    10
                );
                break;
        }

        hidden_speedometer->digits[wip_digit_index] = desired_digit;
    }

    wip_digit_index++;
    if (wip_digit_index == 3) {
        struct Speedometer *temp_speedometer;
        temp_speedometer = visible_speedometer;
        visible_speedometer = hidden_speedometer;
        hidden_speedometer = temp_speedometer;
        wip_speed = player_car_speed >> 2;
        wip_digit_index = 0;
    }
}

void speedometer_draw()
{
    struct SpriteDefinition *sprite_definition;
    //uint32_t player_car_display_speed = player_car_speed >> 2;

    // xpos = 273
    sprite_definition = sprite_definition_pointers[SPEEDO_DEFINITION_OFFSET];
    draw_compiled_sprite(
        visible_speedometer->words,
        &drawing_playfield->buffer[160 * 164 + (8 * 17)],
        &(sprite_definition->compiled_sprite_0),
        1
    );

    /*sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + 1];
    draw_speedo_digit(sprite_definition->words, visible_speedometer->words, 0);
    sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + 2];
    draw_speedo_digit(sprite_definition->words, visible_speedometer->words, 1);*/

    //draw_speedo_digit(sprite_definition->words, visible_speedometer->words, 0);

    //uint8_t *current_digit = &digit_lookup[player_car_display_speed << 2];

    // xpos = 277
    /*sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + *current_digit];
    draw_compiled_sprite(
        sprite_definition->words,
        &drawing_playfield->buffer[160 * 174 + (8 * 17)],
        &(sprite_definition->compiled_sprite_0),
        5
    );*/

    //current_digit++;

    // xpos = 288
    /*sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + *current_digit];
    draw_compiled_sprite(
        sprite_definition->words,
        &drawing_playfield->buffer[160 * 174 + (8 * 18)],
        &(sprite_definition->compiled_sprite_0),
        0
    );*/

    //current_digit++;

    // xpos = 299
    /*sprite_definition = sprite_definition_pointers[SPEEDO_DIGITS_OFFSET_BASE + *current_digit];
    draw_compiled_sprite(
        sprite_definition->words,
        &drawing_playfield->buffer[160 * 174 + (8 * 18)],
        &(sprite_definition->compiled_sprite_0),
        11
    );*/
}

