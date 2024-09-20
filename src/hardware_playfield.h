#ifndef __HARDWARE_PLAYFIELD_H
#define __HARDWARE_PLAYFIELD_H

#include <inttypes.h>
#include "sprite_common.h"
#include "hardware.h"
#include "sprite_definitions.h"
#include "draw_sprite.h"
#include "bitplane_draw_record.h"
#include "hud_digits.h"
#include "stars.h"

#define HARDWARE_PLAYFIELD_WIDTH 320
#define HARDWARE_PLAYFIELD_HEIGHT 200
#define HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES 32768
#define HARDWARE_PLAYFIELD_COUNT 3

struct HardwarePlayfield {
    uint8_t *buffer;
    struct BitplaneDrawRecord bitplane_draw_records[SPRITE_COUNT];
    struct BitplaneDrawRecord *current_bitplane_draw_record;
    uint16_t star_block_offsets[STAR_COUNT];
    uint16_t stars_drawn;
    struct HudDigits hud_digits;
    uint16_t tallest_sprite_ypos;
    int16_t mountains_scroll_pixels;
    uint16_t hud_redraw_required;
};

extern struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];
extern uint16_t hardware_playfield_shaking;
extern struct HardwarePlayfield *drawing_playfield;

void hardware_playfield_handle_vbl();
void hardware_playfield_draw_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos);
void hardware_playfield_copy_and_erase_previous_bitplane_draw_record(struct BitplaneDrawRecord *destination_bitplane_draw_record);
void hardware_playfield_erase_sprites();
void hardware_playfield_global_init();
void hardware_playfield_init();
void hardware_playfield_update_digits();
void hardware_playfield_frame_complete();
void hardware_playfield_hud_redraw_required();

#endif
