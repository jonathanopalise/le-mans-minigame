#include <mint/osbind.h>
#include <mint/sysbind.h>
#include "credits_screen_data.h"

void main_supervisor() {

    //*((uint8_t *)0xffff820a) = *((uint8_t *)0xffff820a) | 2;
    *((uint8_t *)0xfffffc02) = 0x12;

    uint16_t *src = credits_screen_palette;
    uint16_t *dest = (uint16_t *)0xffff8240;
    for (uint16_t index = 0; index < 16; index++) {
        *dest = *src;
        src++;
        dest++;
    }

    src = credits_screen_bitmap;
    dest = Physbase();
    for (uint16_t index = 0; index < 16000; index++) {
        *dest = *src;
        src++;
        dest++;
    }
}

int main(int argc, char **argv)
{
   Supexec(&main_supervisor, 0,0,0,0,0);
   return 0;
}
