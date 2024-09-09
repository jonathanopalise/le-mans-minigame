#ifndef __STATUS_DEFINITIONS_H
#define __STATUS_DEFINITIONS_H

#include <inttypes.h>

#define STATUS_DEFS_LARGE_DIGITS_BASE 3
#define STATUS_DEFS_SMALL_DIGITS_BASE 13
#define STATUS_DEFS_SPEEDO_DIGITS_BASE 23
#define STATUS_DEFS_TIME 0
#define STATUS_DEFS_SCORE 1
#define STATUS_DEFS_HIGH 2


struct StatusDefinition {
    uint16_t source_data_width_pixels;
    uint16_t source_data_height_lines;
    uint16_t *words;
};

extern struct StatusDefinition status_definitions[];

#endif
