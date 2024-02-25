#ifndef __TRACK_SEGMENTS_H
#define __TRACK_SEGMENTS_H

#include <inttypes.h>

#define DIRECTION_NONE 0
#define DIRECTION_LEFT -1
#define DIRECTION_RIGHT 1

struct TrackSegment {
    uint32_t change_frequency; // the interval in which the corner value is changed by 1
    int16_t change_direction; // whether the corner moves left or right on each interval
    uint16_t change_count; // how many intervals there are in this segment
};

extern struct TrackSegment track_segments[];

#endif
