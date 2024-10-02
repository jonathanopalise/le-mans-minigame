#ifndef __HARDWARE_PLAYFIELD_H
#define __HARDWARE_PLAYFIELD_H

#include <inttypes.h>
#include "sprite_definitions.h"
#include "draw_sprite.h"
#include "bitplane_draw_record.h"
#include "hud_digits.h"
#include "stars.h"

#define HARDWARE_PLAYFIELD_WIDTH 320
#define HARDWARE_PLAYFIELD_HEIGHT 200
#define HARDWARE_PLAYFIELD_BUFFER_SIZE_BYTES 32768
#define HARDWARE_PLAYFIELD_COUNT 3
#define SPRITE_COUNT 32

struct HardwarePlayfield {
    uint8_t *buffer;                  // offset 0
    uint16_t tallest_sprite_ypos;     // offset 4
    int16_t mountains_scroll_pixels;  // offset 6
    uint16_t hud_redraw_required;
    uint16_t stars_drawn;
    struct BitplaneDrawRecord bitplane_draw_records[SPRITE_COUNT];
    struct BitplaneDrawRecord *current_bitplane_draw_record;
    uint16_t star_block_offsets[STAR_COUNT];
    struct HudDigits hud_digits;
};

struct SpritePlacement {
    uint16_t sprite_index;
    int16_t xpos;
    int16_t ypos;
};

extern struct HardwarePlayfield hardware_playfields[HARDWARE_PLAYFIELD_COUNT];
extern uint16_t hardware_playfield_shaking;
extern struct HardwarePlayfield *drawing_playfield;
extern struct HardwarePlayfield *score_source_playfield;

void hardware_playfield_set_visible_address(uint32_t visible_buffer_address);
void hardware_playfield_handle_vbl();
void hardware_playfield_draw_sprite(struct SpritePlacement *sprite_placement);
void hardware_playfield_erase_sprites();
void hardware_playfield_global_init();
void hardware_playfield_init();
void hardware_playfield_update_digits();
void hardware_playfield_frame_complete();
void hardware_playfield_hud_redraw_required();

#endif
