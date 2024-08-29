#include "hud.h"

static int16_t seconds_remaining;
static int16_t old_seconds_remaining;

static int32_t score;
static int32_t wip_score;

int8_t wip_score_digits[8];

static int16_t frames_in_current_second_remaining;
static int16_t current_wip_score_digit;

struct HudDigits hud_digits;

void hud_init()
{
    old_seconds_remaining = -1;
    seconds_remaining = 50;
    frames_in_current_second_remaining = 49;

    score = 0;

    current_wip_score_digit = 7;

    for (uint16_t index = 0; index < TIME_DIGIT_COUNT; index++) {
        hud_digits.time_digits[index] = 0;
    }

    for (uint16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        hud_digits.score_digits[index] = 0;
        hud_digits.high_score_digits[index] = 0;
    }

    current_wip_score_digit = 7;
}

void hud_reduce_time()
{
    if (seconds_remaining == 0 && frames_in_current_second_remaining == 0) {
        return;
    }

    frames_in_current_second_remaining--;
    if (frames_in_current_second_remaining == -1) {
        seconds_remaining--;
        frames_in_current_second_remaining = 49;
    }
}

void hud_increase_time(uint32_t seconds_to_add)
{
    seconds_remaining += seconds_to_add;
}

void hud_set_score(uint32_t new_score)
{
    score = new_score;
}

uint16_t hud_is_time_up()
{
    return seconds_remaining == 0 && frames_in_current_second_remaining == 0;
}

void hud_update_digits()
{
    if (seconds_remaining != old_seconds_remaining) {
        hud_digits.time_digits[0] = seconds_remaining / 10;
        hud_digits.time_digits[1] = seconds_remaining % 10;
        old_seconds_remaining = seconds_remaining;
    }

    wip_score_digits[current_wip_score_digit] = wip_score % 10;
    current_wip_score_digit--;

    if (current_wip_score_digit == -1) {
        // transfer wip_score_digits to hud_digits.score_digits
        for (int index = 0; index < 8; index++) {
            hud_digits.score_digits[index] = wip_score_digits[index];
        }

        current_wip_score_digit = 7;
        wip_score = score;
    } else {
        wip_score /= 10;
    }
}

