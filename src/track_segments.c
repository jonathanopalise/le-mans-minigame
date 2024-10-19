
#include "track_segments.h"


struct TrackSegment track_segments[] = (struct TrackSegment[]) {

    // 0.26 - 0.34 hold straight
    // 1 second = 125 changes of 256 frequency

    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 1000
    },

    // 0.34 - 0.35: transition to 5/10 right:
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 300
    },

    // 0.35 - 0.37: hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 300
    },

    // 0.37 - 0.38: transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 300
    },

    // 0.38 - 0.39 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },

    // 0.39 - 0.40: transition to 7/10 left:
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_LEFT,
        .change_count = 420
    },

    // 0.40 - 0.41: hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },

    // 0.41 - 0.42 transition to 7/10 right
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 840
    },

    // 0.42 - 0.43 transition to straight
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_LEFT,
        .change_count = 420
    },

    // 0.44 - 0.45 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },

    // 0.45 - 0.47 transition to 8/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 480
    },

    // 0.47 - 0.48 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },

    // 0.48 - 0.49 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 480
    },

    // 0.49 - 0.50 transition to 6/10 right
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 180
    },

    // 0.50 - 0.51 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 60
    },

    // 0.51 - 0.52 transition to straight
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_LEFT,
        .change_count = 180
    },

    // 0.52 - 1.03 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 1400
    },

    // 1.03 - 1.04 transition to 4/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 240
    },

    // 1.04 - 1.06 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.06 - 1.07 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 240
    },

    // 1.07 - 1.09 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.09 - 1.10 transition to 8/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 240
    },

    // 1.10 - 1.12 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 20
    },

    // 1.12 - 1.13 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 240
    },

    // 1.13 - 1.18 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 800
    },

    // 1.18 - 1.19 transition to 3/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 180
    },

    // 1.19 - 1.20 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 180
    },

    // 1.20 - 1.23 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 375
    },

    // 1.23 - 1.24 transition to 2/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 120
    },

    // 1.24 - 1.25 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },

    // 1.25 - 1.27 transition to 5/10 left
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 420
    },

    // 1.27 - 1.28 transition to 5/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 600
    },

    // 1.28 - 1.30 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 120
    },

    // 1.30 - 1.31 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 300
    },

    // 1.31 - 1.33 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.33 - 1.34 transition to 5/10 left
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 375
    },

    // 1.34 - 1.37 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.37 - 1.38 transition to straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 375
    },

    // 1.38 - 1.39 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.40 - 1.41 transition to 6/10 left
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_LEFT,
        .change_count = 240
    },

    // 1.41 - 1.44 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 375
    },

    // 1.44 - 1.45 transition to straight
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 240
    },

    // 1.45 - 1.46 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 250
    },

    // 1.46 - 1.47 transition to 4/10 right
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 240
    },

    // 1.47 - 1.50 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 375
    },

    // 1.50 - 1.51 transition to 6/10 left
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_LEFT,
        .change_count = 600 // left is now 360
    },

    // 1.51 - 1.53 transition to 7/10 right
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_RIGHT,
        .change_count = 780 // right is now 420
    },

    // 1.53 - 1.54 hold corner
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },

    // 1.54 - 1.55 transition to straight
    {
        .change_frequency = 7,
        .change_direction = DIRECTION_LEFT,
        .change_count = 420
    },

    // 1.55 - 1.56 hold straight
    {
        .change_frequency = 8,
        .change_direction = DIRECTION_NONE,
        .change_count = 125
    },


    // end of track
    {
        .change_frequency = 0,
        .change_direction = 0,
        .change_count = 0
    }
};

