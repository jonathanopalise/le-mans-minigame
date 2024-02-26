#ifndef __DRAW_SPRITE_H
#define __DRAW_SPRITE_H

#include "bitplane_draw_record.h"

void draw_sprite(
    int16_t xpos,
    int16_t ypos,
    void *source_data,
    int16_t source_data_width,
    int16_t source_data_height,
    void *screen_buffer,
    struct BitplaneDrawRecord *bitplane_draw_record
);

#endif

