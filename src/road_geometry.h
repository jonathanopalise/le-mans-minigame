#ifndef __ROAD_GEOMETRY_H
#define __ROAD_GEOMETRY_H

#include <inttypes.h>

struct RoadScanline {
    int32_t current_logical_xpos;
    // how far into the distance this scanline is
    uint16_t distance_along_road;

    uint8_t *line_start_source;

    // value to add to sprite index for this scanline
    uint16_t sprite_index_adjust;

    // value to add to sprite index for this scanline (for lampposts only)
    uint16_t lamppost_sprite_index_adjust;

    // pre-multipled values for modifying the unnormalised_skew value
    // when the camera shifts to the left or right
    // e.g. to shift the camera 128 units to the right:
    // unnormalised_skew += current_scanline->unnormalised_skew_add_values[128]; 
    int32_t logical_xpos_add_values[64];
    int32_t object_xpos_add_values[6];

    int32_t logical_xpos_corner_add_values[64];
};

extern struct RoadScanline road_scanlines[];

extern int8_t distance_to_scanline_lookup[];

#endif
