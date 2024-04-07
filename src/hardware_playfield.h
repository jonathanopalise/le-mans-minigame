#ifndef __HARDWARE_PLAYFIELD_H
#define __HARDWARE_PLAYFIELD_H

#include <inttypes.h>
#include "sprite_common.h"
#include "hardware.h"
#include "sprite_definitions.h"
#include "draw_sprite.h"
#include "bitplane_draw_record.h"

#define HARDWARE_PLAYFIELD_WIDTH 320
#define HARDWARE_PLAYFIELD_HEIGHT 200
#define HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES 32768

struct HardwarePlayfield {
    uint8_t *buffer;
    struct BitplaneDrawRecord bitplane_draw_records[SPRITE_COUNT];
    struct BitplaneDrawRecord *current_bitplane_draw_record;
    uint16_t sprites_drawn;
};

extern uint16_t hardware_playfield_shaking;

void hardware_playfield_handle_vbl();
void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos);
void hardware_playfield_erase_sprites();
void hardware_playfield_init();
void hardware_playfield_frame_complete();
struct HardwarePlayfield *hardware_playfield_get_drawing_playfield();

#endif
