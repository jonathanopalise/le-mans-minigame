#ifndef __ROAD_GEOMETRY_H
#define __ROAD_GEOMETRY_H

#include <inttypes.h>

struct RoadScanline {
    // how far into the distance this scanline is
    uint16_t distance_along_road;

    // pre-multipled values for modifying the unnormalised_skew value
    // when the camera shifts to the left or right
    // e.g. to shift the camera 128 units to the right:
    // unnormalised_skew += current_scanline->unnormalised_skew_add_values[128]; 
    uint32_t logical_xpos_add_values[256];

    // runtime values
    int32_t current_logical_xpos;
    uint16_t render_method;
};

extern struct RoadScanline road_scanlines[];

extern uint16_t distance_to_scanline_lookup[];

#endif
