#ifndef __INITIALISE_H
#define __INITIALISE_H

#include <inttypes.h>

void initialise();

extern volatile uint8_t joy_data;
extern uint16_t vertical_shift;
extern uint16_t sky_gradient;
extern uint16_t scenery_colours;
extern uint16_t ground_colours;
extern volatile uint32_t vbl_title_screen_palette_source;

#endif
