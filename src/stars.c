#include "stars.h"
#include "star_lookups.h"

struct StarPosition {
    uint16_t original_xpos;
    uint16_t ypos;
    uint16_t background_colour_offset;
};

uint16_t star_erase_offsets[STAR_COUNT];

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
        star_erase_offsets[index] = line_background_colours_2[current_star_position->ypos] << 3;

        current_star_position->original_xpos = current_star_position->original_xpos * 2;
        current_star_position->background_colour_offset = 128 * line_background_colours_2[current_star_position->ypos];
        current_star_position->ypos *= 160; //= current_star_position->ypos * 160;

        current_star_position++;
    }
}


