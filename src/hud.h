#ifndef __HUD_H
#define __HUD_H

#include <inttypes.h>
#include "hud_digits.h"

extern struct HudDigits hud_digits;
extern uint16_t frames_since_game_over;

void hud_init();
void hud_reduce_time();
void hud_increase_time(uint32_t seconds_to_add);
void hud_set_score(uint32_t new_score);
uint16_t hud_is_time_up();
void hud_update_digits();

#endif
