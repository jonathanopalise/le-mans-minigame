#include "screen_transition.h"

uint16_t transition_offsets[260] = {
    5264, 10360, 28256, 30824, 25728, 15400, 12840, 20560, 15416, 23160, 7816, 7824, 5192, 7696, 7784, 20480, 2624, 30768, 2576, 23176, 
    15440, 17976, 5184, 15448, 10288, 28224, 28216, 30848, 30816, 25640, 20568, 2656, 120, 5256, 15424, 18064, 5200, 64, 20496, 2640, 
    12912, 23040, 5240, 28208, 28240, 20632, 30736, 16, 10296, 2600, 10344, 25744, 10248, 20520, 23056, 23144, 18072, 12864, 17984, 20600, 
    5216, 23112, 12808, 23192, 28288, 25680, 23128, 28168, 25712, 15496, 10368, 15488, 7712, 12952, 20536, 15472, 7760, 30776, 20584, 28184, 
    2616, 17968, 12848, 48, 2664, 7680, 12888, 20512, 30808, 23152, 2696, 72, 25600, 12904, 28232, 32, 30864, 10280, 12816, 5224, 
    12896, 5176, 7800, 15392, 15376, 112, 7832, 7728, 10304, 20528, 17952, 25736, 12872, 28304, 7768, 2712, 10376, 25648, 23048, 5120, 
    25624, 144, 25656, 2672, 20488, 5152, 23168, 17928, 25632, 5272, 56, 30872, 18040, 7688, 2632, 25688, 20592, 18000, 5136, 30792, 
    7744, 28160, 28264, 7752, 18056, 18008, 23096, 23080, 136, 25672, 17992, 23136, 30744, 28248, 12936, 18016, 7720, 2584, 30760, 28312, 
    30720, 18048, 25720, 23184, 20616, 2648, 128, 25752, 5160, 10384, 20576, 15360, 10392, 2568, 2688, 152, 15432, 30800, 28272, 30840, 
    10336, 88, 20504, 24, 2680, 20552, 28176, 28192, 25704, 2704, 2560, 12856, 2592, 10256, 10264, 23104, 5208, 10272, 10320, 30728, 
    30752, 20608, 25664, 0, 12944, 5232, 17936, 17960, 23120, 25696, 15384, 28200, 23088, 12824, 5168, 23064, 15368, 25608, 15464, 5248, 
    7808, 15512, 18024, 12832, 10240, 30856, 15480, 20544, 17944, 12880, 20624, 10352, 18032, 7704, 25616, 15456, 5128, 40, 10312, 28296, 
    7736, 12800, 12920, 10328, 96, 8, 5144, 7776, 7792, 15408, 12928, 30832, 104, 23072, 80, 15504, 17920, 30784, 28280, 2608, 
};

static uint16_t screen_transition_calculate_lines(uint16_t transition_offset)
{
    if (transition_offset >= (12 * 16 * 160)) {
        return 8;
    }

    return 16;
}

void screen_transition_erase_block(uint8_t *buffer, uint16_t offset)
{
    uint16_t transition_offset = transition_offsets[offset];
    uint32_t *dest = (uint32_t *)(&buffer[transition_offset]);
    uint16_t y;
    uint16_t lines = screen_transition_calculate_lines(transition_offset);

    for (y = 0; y < lines; y++) {
        *dest = 0;
        dest++;
        *dest = 0;
        dest += (40 - 1);
    }
}

void screen_transition_copy_block(uint8_t *source_buffer, uint8_t *dest_buffer, uint16_t offset)
{
    uint16_t transition_offset = transition_offsets[offset];
    uint32_t *source = (uint32_t *)(&source_buffer[transition_offset]);
    uint32_t *dest = (uint32_t *)(&dest_buffer[transition_offset]);
    uint16_t y;

    uint16_t lines = screen_transition_calculate_lines(transition_offset);

    for (y = 0; y < lines; y++) {
        *dest = *source;
        dest++;
        source++;
        *dest = *source;
        dest += (40 - 1);
        source += (40 - 1);
    }
}


