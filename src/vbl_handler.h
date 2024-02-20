#ifndef __VBL_HANDLER_H
#define __VBL_HANDLER_H

#include <inttypes.h>

extern volatile uint16_t waiting_for_vbl;

void vbl_handler();

#endif

