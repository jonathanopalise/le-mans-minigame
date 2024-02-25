#include "track_segments.h"

struct TrackSegment track_segments[] = (struct TrackSegment[]) {
    {
        .change_frequency = 1024,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },
    {
        .change_frequency = 256,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 480
    },
    {
        .change_frequency = 1024,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },
    {
        .change_frequency = 256,
        .change_direction = DIRECTION_LEFT,
        .change_count = 480
    },
    {
        .change_frequency = 1024,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },
    {
        .change_frequency = 256,
        .change_direction = DIRECTION_LEFT,
        .change_count = 480
    },
    {
        .change_frequency = 1024,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },
    {
        .change_frequency = 256,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 480
    },
    {
        .change_frequency = 0,
        .change_direction = 0,
        .change_count = 0
    }
};

