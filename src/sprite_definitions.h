#ifndef __SPRITE_DEFINITIONS_H
#define __SPRITE_DEFINITIONS_H

#include <inttypes.h>

struct SpriteDefinition {
    uint16_t origin_x;
    uint16_t origin_y;
    uint16_t source_data_width;
    uint16_t source_data_height;
    uint16_t longest_right_end;
    uint16_t *words;
    uint8_t *compiled_sprite_0;
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
