#ifndef __HARDWARE_PLAYFIELD_FAST_H
#define __HARDWARE_PLAYFIELD_FAST_H

#include "bitplane_draw_record.h"
#include <inttypes.h>

void hardware_playfield_erase_sprites_fast(
    struct BitplaneDrawRecord *current_bitplane_draw_record,
    uint8_t *buffer,
    struct BitplaneDrawRecord *bitplane_draw_records
);

void hardware_playfield_copy_score_fast();

#endif
