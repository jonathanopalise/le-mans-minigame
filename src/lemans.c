#include <mint/osbind.h>
#include <mint/sysbind.h>
#include "game_loop.h"

void main_supervisor() {
    game_loop();
}

int main(int argc, char **argv)
{
   Supexec(&main_supervisor, 0,0,0,0,0);
   return 0;
}
