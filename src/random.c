#include "random.h"

static uint32_t rand_state;

uint32_t random(void)
{
    rand_state = (rand_state * 1103515245 + 12345);
    return rand_state;
}


