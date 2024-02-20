#include "vbl_handler.h"

volatile uint16_t waiting_for_vbl;

void vbl_handler()
{
    waiting_for_vbl = 0;
}

