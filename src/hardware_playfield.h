#ifndef __HARDWARE_PLAYFIELD_H
#define __HARDWARE_PLAYFIELD_H

#include <inttypes.h>
#include "sprite_common.h"
#include "hardware.h"

#define HARDWARE_PLAYFIELD_WIDTH 320
#define HARDWARE_PLAYFIELD_HEIGHT 200
#define HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES (PIXELS_TO_BYTES(HARDWARE_PLAYFIELD_WIDTH * HARDWARE_PLAYFIELD_HEIGHT))

struct BitplaneDrawRecord {
    uint16_t *destination_address;
    int16_t destination_x_increment; // is this required?
    int16_t destination_y_increment;
    uint16_t x_count;
    uint16_t y_count;
};

struct HardwarePlayfield {
   uint8_t *buffer;
   struct BitplaneDrawRecord bitplane_draw_records[SPRITE_COUNT];
   uint16_t sprites_drawn;
};

extern struct HardwarePlayfield *hidden_hardware_playfield;
extern struct HardwarePlayfield *visible_hardware_playfield;

void hardware_playfield_handle_vbl();
void hardware_playfield_erase_sprites();
void hardware_playfield_init();

#endif
