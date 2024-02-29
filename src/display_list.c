#ifndef __DISPLAY_LIST_H
#define __DISPLAY_LIST_H

#include "hardware_playfield.h"
#include "qsort.h"

#define DISPLAY_LIST_SIZE 16

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

int compare(const void *a, const void *b) {
	const struct DisplayListItem *visible_object_1;
	const struct DisplayListItem *visible_object_2;

	visible_object_1=a;
	visible_object_2=b;

	return visible_object_1->ypos-visible_object_2->ypos;
}

void display_list_execute() {
    struct DisplayListItem *current_display_list_item = display_list;

    qsort(display_list, num_visible_objects, sizeof(struct DisplayListItem), compare);

    while (current_display_list_item < next_free_display_list_item) {
        hardware_playfield_draw_sprite(
            current_display_list_item->sprite_definition,
            current_display_list_item->xpos,
            current_display_list_item->ypos
        );

        current_display_list_item++;
    }

    display_list_init();
}


#endif

