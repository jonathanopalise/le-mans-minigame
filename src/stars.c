#include "stars.h"
#include "road_movement.h"
#include "hardware_playfield.h"
#include "star_lookups.h"

struct StarPosition {
    uint16_t original_xpos;
    uint16_t ypos;
};

uint16_t line_background_colours[100] = {
    15, 15, 15, 15, 15, // 15
    14, 14, 14, 14, 14, // 14
    13, 13, 13, 13, 13, // 13
    12, 12, 12, 12, 12, // 12
    11, 11, 11, 11, 11, // 11
    10, 10, 10, 10, 10, // 10
    9, 9, 9, 9, 9, // 9
    8, 8, 8, 8, 8, // 8
    7, 7, 7, 7, 7, // 7
    6, 6, 6, 6, 6, // 6
    5, 5, 5, 5, 5, // 5
    4, 4, 4, 4, 4, // 4
    3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 12 // 3
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

void draw_stars()
{
    int16_t shifted_star_xpos;
    uint16_t *plot_source;
    uint16_t *plot_dest;
    uint16_t background_colour;
    uint16_t normalised_mountains_shift = mountains_shift >> 16;
    uint8_t *drawing_playfield_buffer = drawing_playfield->buffer;
    uint16_t block_offset;

    struct StarPosition *current_star_position = star_positions;
    uint16_t *current_star_block_offset = drawing_playfield->star_block_offsets;
    for (uint16_t index = 0; index < STAR_COUNT; index++) {
        shifted_star_xpos = current_star_position->original_xpos - normalised_mountains_shift;
        if (shifted_star_xpos < 0) {
            shifted_star_xpos += 320;
        }

        background_colour = line_background_colours[current_star_position->ypos];
        plot_source = &star_plot_values[(background_colour << 6) + ((shifted_star_xpos & 15) << 2)];
        //plot_source = &star_erase_values[(background_colour << 2)];

        block_offset = ((shifted_star_xpos >> 1) & 0xf8);
        *current_star_block_offset = block_offset;
        plot_dest = (uint16_t *)((uint32_t)drawing_playfield_buffer + (current_star_position->ypos * 160) + block_offset);

        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;

        current_star_position++;
        current_star_block_offset++;
    }
}

void erase_stars()
{
    uint16_t *plot_source;
    uint16_t *plot_dest;
    uint16_t background_colour;

    uint8_t *drawing_playfield_buffer = drawing_playfield->buffer;
    struct StarPosition *current_star_position = star_positions;
    uint16_t *current_star_block_offset = drawing_playfield->star_block_offsets;

    for (uint16_t index = 0; index < STAR_COUNT; index++) {
        background_colour = line_background_colours[current_star_position->ypos];
        plot_source = &star_erase_values[(background_colour << 2)];
        plot_dest = (uint16_t *)((uint32_t)drawing_playfield_buffer + (current_star_position->ypos * 160) + *current_star_block_offset);

        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;
        *plot_dest++ = *plot_source++;

        current_star_position++;
        current_star_block_offset++;
    }
}
