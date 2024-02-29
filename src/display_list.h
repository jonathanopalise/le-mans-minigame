#ifndef __DISPLAY_LIST_H
#define __DISPLAY_LIST_H

#include "sprite_definitions.h"

void display_list_init();
void display_list_add_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos);
void display_list_execute();

#endif

