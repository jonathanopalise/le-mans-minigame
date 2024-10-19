#ifndef __LOOKUPS_H
#define __LOOKUPS_H

#include <inttypes.h>
#include "sprite_definitions.h"
#include "road_geometry.h"
#include "generated/sprite_definitions_count.h"

#define PLAYER_SCANLINE_INDEX 75

extern struct SpriteDefinition *sprite_definition_pointers[SPRITE_DEFINITIONS_COUNT];
extern struct RoadScanline *road_scanline_pointers[100];
extern uint16_t multiply_160[];
extern uint16_t corner_shifts[1200];

void lookups_init();

#endif
