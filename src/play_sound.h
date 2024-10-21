#ifndef __PLAY_SOUND_H
#define __PLAY_SOUND_H

#include<inttypes.h>

void play_sound(uint16_t index);

#define SOUND_ID_START_BEEP_LOW 1
#define SOUND_ID_START_BEEP_HIGH 2
#define SOUND_ID_CHECKPOINT_BEEP 3
#define SOUND_ID_CRASH 4
#define SOUND_ID_BOUNCE_LOUD 5
#define SOUND_ID_BOUNCE_QUIET 6
#define SOUND_ID_SKID 7
#define SOUND_ID_WHOOSH_LOUD 8
#define SOUND_ID_WHOOSH_MEDIUM 9
#define SOUND_ID_WHOOSH_QUIET 10

#endif
