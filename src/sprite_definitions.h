#ifndef __SPRITE_DEFINITIONS_H
#define __SPRITE_DEFINITIONS_H

#include <inttypes.h>

struct SpriteDefinition {
    uint16_t origin_x;             // offset 0
    uint16_t origin_y;             // offset 2
    uint16_t source_data_width;    // offset 4
    uint16_t source_data_height;   // offset 6
    uint16_t longest_right_end;    // offset 8
    uint16_t *words;               // offset 10
    uint8_t *compiled_sprite_0;    // offset 14
    uint8_t *compiled_sprite_1;
    uint8_t *compiled_sprite_2;
    uint8_t *compiled_sprite_3;
    uint8_t *compiled_sprite_4;
    uint8_t *compiled_sprite_5;
    uint8_t *compiled_sprite_6;
    uint8_t *compiled_sprite_7;
    uint8_t *compiled_sprite_8;
    uint8_t *compiled_sprite_9;
    uint8_t *compiled_sprite_10;
    uint8_t *compiled_sprite_11;
    uint8_t *compiled_sprite_12;
    uint8_t *compiled_sprite_13;
    uint8_t *compiled_sprite_14;
    uint8_t *compiled_sprite_15;
};

extern struct SpriteDefinition sprite_definitions[];

#endif
