#include "time_of_day.h"
#include "initialise.h"
#include "time_of_day_process.h"

uint32_t quarter_hour_countdown = 60*1;
uint32_t time_of_day_offset = 0;

void time_of_day_set_colours()
{
    uint16_t *src = &time_of_day[time_of_day_offset];
    uint16_t *dest = &sky_gradient;
    *dest++ = *src++; // remaining colours for sky
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++; // lamppost/star colours

    dest = &scenery_colours;
    *dest++ = *src++; // 2 colours for mountains
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;

    dest = &ground_colours;
    *dest++ = *src++;
    *dest++ = *src++;
    *dest++ = *src++;
}

void time_of_day_init()
{
    quarter_hour_countdown = 60;
    time_of_day_offset = 0;

    time_of_day_set_colours();
}

void time_of_day_update()
{
    quarter_hour_countdown--;
    if (quarter_hour_countdown == 0) {

        quarter_hour_countdown = 60*1;
        time_of_day_offset += 30;
        if (time_of_day_offset == 30*96) {
            time_of_day_offset = 0;
        }

        time_of_day_set_colours();
    }
}

uint16_t time_of_day_is_night()
{
    return time_of_day_offset >= 30*22 && time_of_day_offset <= 30*34;
}
