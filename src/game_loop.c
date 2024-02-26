#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
#include "vbl_handler.h"
#include "road_movement.h"
#include "road_render.h"
#include "player_car.h"
#include "sprite_definitions.h"

void game_loop()
{
    hardware_playfield_init();
    initialise();
    player_car_initialise();
    int16_t xpos = 0;

    while (1) {
        player_car_handle_inputs();
        road_corners_update();
        road_movement_update();
        road_render();
        hardware_playfield_erase_sprites();
        hardware_playfield_draw_sprite(&sprite_definitions[0], xpos, 120);
        hardware_playfield_draw_sprite(&sprite_definitions[0], 150, 50);
        *((volatile uint16_t *)0xffff8240) = 0x040; // green

        waiting_for_vbl = 1;
        while (waiting_for_vbl) {}

        xpos++;
        hardware_playfield_handle_vbl();

        *((volatile uint16_t *)0xffff8240) = 0x400; // red
    }
}
