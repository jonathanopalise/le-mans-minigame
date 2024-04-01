#ifndef __STATUS_DEFINITIONS_H
#define __STATUS_DEFINITIONS_H

#include <inttypes.h>

struct StatusDefinition {
    uint16_t source_data_width_pixels;
    uint16_t source_data_height_lines;
    uint16_t *words;
};

extern struct StatusDefinition status_definitions[];

#endif
