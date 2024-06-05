#define SPRITE_DEFINITION_COUNT 48 
#define RELOCATION_BUFFER_SIZE_WORDS = 32768

#include "sprite_definitions.h"

uint16_t relocation_buffer[RELOCATION_BUFFER_SIZE_WORDS];

void relocate_sprites()
{ 
    struct SpriteDefinition *current_sprite_definition = sprite_definitions;
    uint16_t data_size_bytes;
    uint16_t data_size_words;
    uint32_t source_start_address;
    uint32_t source_end_address;
    uint32_t dest_start_address;
    uint32_t dest_end_address;
    uint16_t *dest = relocation_buffer;
    uint16_t *source; 

    for (uint16_t index = 0; index < SPRITE_DEFINITION_COUNT; index++) {
        data_size_bytes = current_sprite_definition->source_data_width * current_sprite_definition->source_data_height * 10;
        source_start_address = (uint32_t)current_sprite_definition->words;
        source_end_address = source_start_address + (data_size_bytes - 1);

        if ((source_start_address >> 16) != (source_end_address >> 16)) {
            // relocation required

            dest_start_address = (uint32_t)dest;
            dest_end_address = dest_start_address + (data_size_bytes - 1);

            if ((dest_start_address >> 16) != (dest_end_address >> 16)) {
                dest_start_address = (dest_start_address + 65536) & 0xffff0000;
                dest = (uint16_t *)dest_start_address;
            }

            data_size_words = data_size_bytes >> 1;
            source = current_sprite_definition->words;
            current_sprite_definition->words = dest;
            
            for (index = 0; index < data_size_words; index++) {
                *dest++ = *source++;
            }
        }

        current_sprite_definition++;
    } 
}
