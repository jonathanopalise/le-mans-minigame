#ifndef __LOOKUPS_H
#define __LOOKUPS_H

#include "sprite_definitions.h"
#include "road_geometry.h"
#include "generated/sprite_definitions_count.h"

extern struct SpriteDefinition *sprite_definition_pointers[SPRITE_DEFINITIONS_COUNT];
extern struct RoadScanline *road_scanline_pointers[100];

#endif
