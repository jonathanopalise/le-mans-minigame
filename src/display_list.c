#include "hardware_playfield.h"
#include "display_list.h"
#include "display_list_insertion_sort_fast.h"

#define DISPLAY_LIST_SIZE 24

struct SpritePlacement *next_free_display_list_item;
uint16_t num_visible_objects = 0;

struct SpritePlacement display_list[DISPLAY_LIST_SIZE];

void display_list_init()
{
    next_free_display_list_item = display_list;
    num_visible_objects = 0;
}

void display_list_add_sprite(uint16_t sprite_index, int16_t xpos, int16_t ypos)
{
    next_free_display_list_item->sprite_index = sprite_index;
    next_free_display_list_item->xpos = xpos;
    next_free_display_list_item->ypos = ypos;

    next_free_display_list_item++;
    num_visible_objects++;
}

void display_list_execute() {
    struct SpritePlacement *current_display_list_item = next_free_display_list_item - 1;

    display_list_insertion_sort_fast(num_visible_objects);

    while (current_display_list_item >= display_list) {
        hardware_playfield_draw_sprite(current_display_list_item);
        current_display_list_item--;
    }

    display_list_init();
}



