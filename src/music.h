#ifndef __MUSIC_H
#define __MUSIC_H

void music_init();
void music_stop();
void music_tick();

extern volatile void *music_data_address;

#endif

