#include "hud.h"

static int16_t seconds_remaining;
static int16_t old_seconds_remaining;

static int32_t score;
static int32_t old_score;
static int32_t wip_score;

static int16_t frames_in_current_second_remaining;
static int16_t frames_until_score_update;

struct HudDigits hud_digits;

void hud_init()
{
    old_seconds_remaining = -1;
    seconds_remaining = 50;
    frames_in_current_second_remaining = 49;

    old_score = -1;
    score = 0;

    frames_until_score_update = 1;

    for (uint16_t index = 0; index < TIME_DIGIT_COUNT; index++) {
        hud_digits.time_digits[index] = 0;
    }

    for (uint16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        hud_digits.score_digits[index] = 0;
        hud_digits.high_score_digits[index] = 0;
    }
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

    frames_until_score_update--;
    if (frames_until_score_update == 0) {
        frames_until_score_update = 10;
        if (score != old_score) {
            wip_score = score;
            
            hud_digits.score_digits[7] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[6] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[5] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[4] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[3] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[2] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[1] = wip_score % 10;
            wip_score /= 10;
            hud_digits.score_digits[0] = wip_score % 10;
            old_score = score;
        }
    }
}

