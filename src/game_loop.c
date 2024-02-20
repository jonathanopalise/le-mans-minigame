#include "game_loop.h"
#include "hardware_playfield.h"
#include "initialise.h"
#include "vbl_handler.h"
#include "road_render.h"

void game_loop()
{
    hardware_playfield_init();
    initialise();

    while (1) {
        road_render();
        *((volatile uint16_t *)0xffff8240) = 0x040; // green

        waiting_for_vbl = 1;
        while (waiting_for_vbl) {}

        hardware_playfield_handle_vbl();
        //hardware_playfield_erase_sprites();

        *((volatile uint16_t *)0xffff8240) = 0x400; // red
    }
}
