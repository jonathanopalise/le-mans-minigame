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
        *((volatile uint16_t *)0xffff8240) = 0x440; // yellow
        road_corners_update();
        *((volatile uint16_t *)0xffff8240) = 0x044; // green/blue combination
        //road_movement_update();
        //*((volatile uint16_t *)0xffff8240) = 0x404; // purple
        road_render();
        *((volatile uint16_t *)0xffff8240) = 0x004; // blue
        hardware_playfield_erase_sprites();
        hardware_playfield_draw_sprite(&sprite_definitions[0], xpos, 120);
        hardware_playfield_draw_sprite(&sprite_definitions[0], 150, 50);
        hardware_playfield_draw_sprite(&sprite_definitions[0], 100, 50);
        *((volatile uint16_t *)0xffff8240) = 0x040; // green

        waiting_for_vbl = 1;
        while (waiting_for_vbl) {}

        xpos++;
        if (xpos > 320) {
            xpos = 0;
        }
        hardware_playfield_handle_vbl();

        *((volatile uint16_t *)0xffff8240) = 0x400; // red
    }
}
