#define RELOCATION_BUFFER_SIZE_WORDS 16384

#include "sprite_definitions.h"
#include "generated/sprite_definitions_count.h"
#include "natfeats.h"
#include <stdio.h>

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
    uint16_t index2; 

    for (uint16_t index = 0; index < SPRITE_DEFINITIONS_COUNT; index++) {
        data_size_bytes = (current_sprite_definition->source_data_width >> 4) * current_sprite_definition->source_data_height * 10;
        source_start_address = (uint32_t)(current_sprite_definition->words);
        source_end_address = source_start_address + (data_size_bytes - 1);

        if ((source_start_address >> 16) != (source_end_address >> 16)) {
#ifdef __NATFEATS_DEBUG
            // relocation required
            snprintf(
                nf_strbuf,
                256,
                "relocating: startaddr: %x, endaddr: %x, bytes: %d\n",
                source_start_address,
                source_end_address,
                data_size_bytes
            );

            nf_print(nf_strbuf);
#endif

            dest_start_address = (uint32_t)dest;
            dest_end_address = dest_start_address + (data_size_bytes - 1);

            if ((dest_start_address >> 16) != (dest_end_address >> 16)) {
                dest_start_address = (dest_start_address + 65536) & 0xffff0000;
                dest = (uint16_t *)dest_start_address;
            }

            data_size_words = data_size_bytes >> 1;
            source = current_sprite_definition->words;
            current_sprite_definition->words = dest;
            
            for (index2 = 0; index2 < data_size_words; index2++) {
                *dest++ = *source++;
            }
        }

        current_sprite_definition++;
    } 
}
