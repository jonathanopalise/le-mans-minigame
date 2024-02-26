#ifndef __BITPLANE_DRAW_RECORD_H
#define __BITPLANE_DRAW_RECORD_H

struct BitplaneDrawRecord {
    uint8_t *destination_address;     // offset 0
    int16_t destination_y_increment;  // offset 4
    uint16_t x_count;                 // offset 6
    uint16_t y_count;                 // offset 8
};

#endif
