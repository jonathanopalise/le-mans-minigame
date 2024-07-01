#ifndef __STARS_H
#define __STARS_H

#include <inttypes.h>

#define STAR_COUNT 10

struct StarLine {
    uint16_t background_index;
};

struct StarBackground {
    struct StarBitplanes erase_bitplanes;
    struct StarBitplanes draw_bitplanes[16];
};

struct Star {
    uint16_t original_xpos;
    uint16_t line;
};

extern struct StarLine star_lines[100];
extern struct StarBackground star_backgrounds[10];
extern struct Star stars[STAR_COUNT];

#endif


