#ifndef __DRAW_SPRITE_H
#define __DRAW_SPRITE_H

#include <inttypes.h>
#include "bitplane_draw_record.h"
#include "sprite_definitions.h"

uint16_t draw_sprite(
    int16_t xpos, // offset 6
    int16_t ypos, // offset 10
    struct SpriteDefinition *sprite_definition, // offset 12
    //void *source_data,
    //int16_t source_data_width,
    //int16_t source_data_height,
    void *screen_buffer, // offset 16
    struct BitplaneDrawRecord *bitplane_draw_record // offset 20
    //uint8_t *compiled_sprites 
);

void draw_compiled_sprite(
    void *source_data,
    void *destination,
    uint8_t *compiled_sprites,
    uint16_t skew
);

#endif

