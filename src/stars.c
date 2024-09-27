#include "stars.h"
#include "road_movement.h"
#include "hardware_playfield.h"
#include "star_lookups.h"
#include "lookups.h"

struct StarPosition {
    uint16_t original_xpos;
    uint16_t ypos;
    uint16_t background_colour_offset;
    uint16_t erase_offset;
};

uint16_t line_background_colours[100] = {
    2*64, 2*64, 2*64, 14*64, 2*64, // 15
    14*64, 14*64, 14*64, 13*64, 14*64, // 14
    13*64, 13*64, 13*64, 12*64, 13*64, // 13
    12*64, 12*64, 12*64, 11*64, 12*64, // 12
    11*64, 11*64, 11*64, 10*64, 11*64, // 11
    10*64, 10*64, 10*64, 9*64, 10*64, // 10
    9*64, 9*64, 9*64, 8*64, 9*64, // 9
    8*64, 8*64, 8*64, 7*64, 8*64, // 8
    7*64, 7*64, 7*64, 6*64, 7*64, // 7
    6*64, 6*64, 6*64, 5*64, 6*64, // 6
    5*64, 5*64, 5*64, 4*64, 5*64, // 5
    4*64, 4*64, 4*64, 3*64, 4*64, // 4
    3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, // 3
    3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64, 3*64
};

uint16_t line_background_colours_2[100] = {
    2, 2, 2, 14, 2, // 15
    14, 14, 14, 13, 14, // 14
    13, 13, 13, 12, 13, // 13
    12, 12, 12, 11, 12, // 12
    11, 11, 11, 10, 11, // 11
    10, 10, 10, 9, 10, // 10
    9, 9, 9, 8, 9, // 9
    8, 8, 8, 7, 8, // 8
    7, 7, 7, 6, 7, // 7
    6, 6, 6, 5, 6, // 6
    5, 5, 5, 4, 5, // 5
    4, 4, 4, 3, 4, // 4
    3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, // 3
    3, 3, 3, 3, 3, 3, 3, 3, 3, 3
};


struct StarPosition star_positions[STAR_COUNT] = {
    {154, 85},
    {257, 99},
    {310, 50},
    {153, 75},
    {300, 80},
    {111, 65},
    {228, 64},
    {160, 57},
    {44, 91},
    {191, 45},
    {27, 72},
    {59, 62},
    {212, 57},
    {292, 53},
    {208, 50},
    {276, 82},
    {186, 95},
    {291, 88},
    {275, 58},
    {35, 65},
    {164, 84},
    {175, 42},
    {28, 86},
    {99, 83},
    {202, 60},
    {242, 69},
    {177, 52},
    {251, 81},
    {205, 71},
    {153, 93},
    {72, 46},
    {114, 55},
    {212, 90},
    {73, 90},
    {12, 88},
    {181, 74},
    {318, 66},
    {105, 47},
    {130, 77},
    {137, 90},
    {45, 82},
    {314, 80},
    {254, 70},
    {290, 67},
    {162, 48},
    {220, 40},
    {316, 64},
    {301, 70},
    {214, 71},
    {107, 76}
};

void init_stars()
{
    struct StarPosition *current_star_position = star_positions;
    for (uint16_t index = 0; index < STAR_COUNT; index++) {
        current_star_position->original_xpos = current_star_position->original_xpos * 2;
        current_star_position->erase_offset = line_background_colours_2[current_star_position->ypos] << 2;
        current_star_position->background_colour_offset = 128 * line_background_colours_2[current_star_position->ypos];
        current_star_position->ypos *= 160; //= current_star_position->ypos * 160;
        current_star_position++;
    }
}

void draw_stars()
{
    int16_t shifted_star_xpos;
    uint32_t *plot_source;
    uint32_t *plot_dest;
    uint16_t background_colour;
    uint16_t normalised_mountains_shift = mountains_shift >> 16;
    uint8_t *drawing_playfield_buffer = drawing_playfield->buffer;
    uint16_t block_offset;
    uint16_t ypos;

    struct StarPosition *current_star_position = star_positions;
    uint16_t *current_star_block_offset = drawing_playfield->star_block_offsets;
    for (uint16_t index = 0; index < STAR_COUNT; index++) {
        shifted_star_xpos = current_star_position->original_xpos - normalised_mountains_shift;
        if (shifted_star_xpos < 0) {
            shifted_star_xpos += 320;
        }

        ypos = current_star_position->ypos;
        background_colour = line_background_colours[ypos];
        // TODO: i can preshift these background colour values to save some cycles
        plot_source = (uint32_t *)(&star_plot_values[(background_colour << 6) + ((shifted_star_xpos & 15) << 2)]);

        block_offset = multiply_160[ypos] + ((shifted_star_xpos >> 1) & 0xf8);
        *current_star_block_offset++ = block_offset;
        plot_dest = (uint32_t *)((uint32_t)drawing_playfield_buffer + block_offset);

        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;

        current_star_position++;
    }
}

// so we need:
// -

void erase_stars()
{
    uint32_t *plot_source;
    uint32_t *plot_dest;

    uint8_t *drawing_playfield_buffer = drawing_playfield->buffer;
    struct StarPosition *current_star_position = star_positions;
    uint16_t *current_star_block_offset = drawing_playfield->star_block_offsets;

    for (uint16_t index = 0; index < STAR_COUNT; index++) {
        plot_source = (uint32_t *)(&star_erase_values[current_star_position->erase_offset]);
        plot_dest = (uint32_t *)((uint32_t)drawing_playfield_buffer + *current_star_block_offset);

        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;

        current_star_position++;
        current_star_block_offset++;
    }
}
