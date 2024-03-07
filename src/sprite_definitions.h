#ifndef __SPRITE_DEFINITIONS_H
#define __SPRITE_DEFINITIONS_H

#include <inttypes.h>

struct SpriteDefinition {
    uint16_t origin_x;
    uint16_t origin_y;
    uint16_t source_data_width;
    uint16_t source_data_height;
    uint16_t *words;
    uint8_t *compiled_sprites[16];
};

extern struct SpriteDefinition sprite_definitions[];

#endif
