#ifndef __INITIALISE_H
#define __INITIALISE_H

#include <inttypes.h>

void initialise();
void update_joy();

extern volatile uint8_t joy_data;
extern volatile uint8_t joy0;
extern volatile uint8_t joy1;
extern uint16_t vertical_shift;
extern uint16_t sky_gradient;
extern uint16_t scenery_colours;
extern uint16_t ground_colours;
extern volatile uint32_t vbl_title_screen_palette_source;

#endif
