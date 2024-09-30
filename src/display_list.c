#ifndef __DISPLAY_LIST_H
#define __DISPLAY_LIST_H

#include "hardware_playfield.h"
#include "display_list_insertion_sort_fast.h"

#define DISPLAY_LIST_SIZE 24

struct DisplayListItem {
    struct SpriteDefinition *sprite_definition;
    int16_t xpos;
    int16_t ypos;
};

struct DisplayListItem *next_free_display_list_item;
uint16_t num_visible_objects = 0;

struct DisplayListItem display_list[DISPLAY_LIST_SIZE];

void display_list_init()
{
    next_free_display_list_item = display_list;
    num_visible_objects = 0;
}

void display_list_add_sprite(struct SpriteDefinition *sprite_definition, int16_t xpos, int16_t ypos)
{
    next_free_display_list_item->sprite_definition = sprite_definition;
    next_free_display_list_item->xpos = xpos;
    next_free_display_list_item->ypos = ypos;

    next_free_display_list_item++;
    num_visible_objects++;
}

void display_list_execute() {
    struct DisplayListItem *current_display_list_item = next_free_display_list_item - 1;

    display_list_insertion_sort_fast(num_visible_objects);

    while (current_display_list_item >= display_list) {
        hardware_playfield_draw_sprite(
            current_display_list_item->sprite_definition,
            current_display_list_item->xpos,
            current_display_list_item->ypos
        );

        current_display_list_item--;
    }

    display_list_init();
}


#endif

