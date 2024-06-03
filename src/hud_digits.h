#ifndef __HUD_DIGITS_H
#define __HUD_DIGITS_H

#define TIME_DIGIT_COUNT 2
#define SCORE_DIGIT_COUNT 7

#include <inttypes.h>

struct HudDigits {
    int8_t time_digits[TIME_DIGIT_COUNT];
    int8_t score_digits[SCORE_DIGIT_COUNT];
    int8_t high_score_digits[SCORE_DIGIT_COUNT];
};

#endif

