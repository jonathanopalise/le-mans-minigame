#include "hud.h"
#include "player_car.h"

static int16_t seconds_remaining;
static int16_t old_seconds_remaining;

static int32_t score;
static int32_t high_score;
static int32_t wip_score;

int8_t wip_score_digits[8];

static int16_t frames_in_current_second_remaining;
static int16_t current_wip_score_digit;
static uint16_t high_score_redraw_required;

uint16_t frames_since_game_over;

struct HudDigits hud_digits;

void hud_init()
{
    high_score = 0;
}

void hud_update_high_score_digits()
{
    int32_t wip_high_score = high_score;

    for (int16_t index = 7; index >=0; index--) {
        hud_digits.high_score_digits[index] = wip_high_score % 10;
        wip_high_score /= 10;
    }
}

void hud_game_init()
{
    old_seconds_remaining = -1;
    seconds_remaining = 50;
    frames_in_current_second_remaining = 49;
    frames_since_game_over = 0;

    score = 0;

    current_wip_score_digit = 7;

    for (uint16_t index = 0; index < TIME_DIGIT_COUNT; index++) {
        hud_digits.time_digits[index] = 0;
    }

    for (uint16_t index = 0; index < SCORE_DIGIT_COUNT; index++) {
        hud_digits.score_digits[index] = 0;
        //hud_digits.high_score_digits[index] = 0;
    }

    current_wip_score_digit = 7;

    hud_update_high_score_digits();
}

void hud_reduce_time()
{
    if (seconds_remaining == 0 && frames_in_current_second_remaining == 0) {
        if (player_car_speed == 0) {
            frames_since_game_over++;
        }
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
    if (score > high_score) {
        high_score = score;
        high_score_redraw_required = 1;
    } else {
        high_score_redraw_required = 0;
    }
}

uint16_t hud_is_time_up()
{
    return seconds_remaining == 0 && frames_in_current_second_remaining == 0;
}

uint16_t hud_update_score_digits()
{
    uint16_t hud_redraw_required = 0;

    if (seconds_remaining != old_seconds_remaining) {
        hud_digits.time_digits[0] = seconds_remaining / 10;
        hud_digits.time_digits[1] = seconds_remaining % 10;
        old_seconds_remaining = seconds_remaining;
        hud_redraw_required = 1;
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
        hud_redraw_required = 1;
    } else {
        wip_score /= 10;
    }

    return hud_redraw_required;
}


