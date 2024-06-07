#include "road_render.h"
#include "road_graphics.h"
#include "road_geometry.h"
#include "hardware_playfield.h"
#include "player_car.h"
#include "blitter.h"
#include "road_render_fast.h"
#include "player_car.h"

#define LINE_COUNT 80
#define START_LINE_DISTANCE 86000

int32_t checkpoint_camera_distance;

void road_render_init()
{
    struct RoadScanline *current_road_scanline = road_scanlines;
    uint32_t *current_byte_offset = byte_offsets;

    for (uint16_t index = 0; index < LINE_COUNT; index++) {
        current_road_scanline->line_start_source = ((uint8_t *)(&gfx_data[*current_byte_offset])) - 2; 
        current_road_scanline++;
        current_byte_offset++;
    }
}

void road_render()
{
    checkpoint_camera_distance = START_LINE_DISTANCE - camera_track_position;

    if (checkpoint_camera_distance > 0 && checkpoint_camera_distance < 50000) {
        road_render_fast_checkpoint(checkpoint_camera_distance, checkpoint_camera_distance + 200);
    } else {
        road_render_fast();
    }
}
