
#include "lookups.h"
#include "generated/sprite_definitions_count.h"

struct SpriteDefinition *sprite_definition_pointers[SPRITE_DEFINITIONS_COUNT];
struct RoadScanline *road_scanline_pointers[100];

void lookups_init()
{
    int index;

    for (index = 0; index < SPRITE_DEFINITIONS_COUNT; index++) {
        sprite_definition_pointers[index] = &sprite_definitions[index];
    }

    for (index = 0; index < 100; index++) {
        road_scanline_pointers[index] = &road_scanlines[index]; 
    } 
}

