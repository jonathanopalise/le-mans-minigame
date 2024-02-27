#ifndef __MOVEMENT_UPDATE_INNER_H
#define __MOVEMENT_UPDATE_INNER_H

#include <inttypes.h>

void movement_update_inner_scenario_1(
    uint16_t road_scanline_struct_size,
    uint16_t *logical_xpos_address,
    uint16_t *add_value_address
);

void movement_update_inner_scenario_2(
    uint16_t road_scanline_struct_size,
    uint16_t *logical_xpos_address,
    uint16_t *add_value_address
);

#endif

