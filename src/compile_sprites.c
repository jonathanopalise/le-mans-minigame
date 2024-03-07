#define SKEW_COUNT 16
#define FRAMEBUFFER_BYTES_PER_LINE 160
#define BYTES_PER_16_PIXELS 8
#define SCENERY_ITEM_COUNT 33

#include<stddef.h>
#include<inttypes.h>
#include "sprite_definitions.h"

uint8_t *compiled_sprite_output;
uint8_t compiled_sprite_cache[524288];
uint8_t skew_buffer[32768];
uint8_t remap_buffer[16]; // currently unused - will need mem allocating if used

void write_word_to_compiled_sprite_output(uint16_t instruction)
{
    uint16_t *compiled_sprite_output_16 = (uint16_t *)compiled_sprite_output;
    *compiled_sprite_output_16 = instruction;
    compiled_sprite_output_16++;
    compiled_sprite_output = (uint8_t *)compiled_sprite_output_16;
}

void write_longword_to_compiled_sprite_output(uint32_t instruction)
{
    uint32_t *compiled_sprite_output_32 = (uint32_t *)compiled_sprite_output;
    *compiled_sprite_output_32 = instruction;
    compiled_sprite_output_32++;
    compiled_sprite_output = (uint8_t *)compiled_sprite_output_32;
}

void write_advance_a1_to_compiled_sprite_output(uint16_t bytes_to_advance)
{
    if (bytes_to_advance <= 8) {
        uint16_t instruction;
        switch (bytes_to_advance) {
            case 1:
                instruction = 0x5289;
            break;
            case 2:
                instruction = 0x5489;
            break;
            case 3:
                instruction = 0x5689;
            break;
            case 4:
                instruction = 0x5889;
            break;
            case 5:
                instruction = 0x5a89;
            break;
            case 6:
                instruction = 0x5c89;
            break;
            case 7:
                instruction = 0x5e89;
            break;
            case 8:
                instruction = 0x5089;
            break;
        }
        write_word_to_compiled_sprite_output(instruction);
    } else {
        write_word_to_compiled_sprite_output(0x43e9); // lea x(a1),a1 [8 cycles]
        write_word_to_compiled_sprite_output(bytes_to_advance); // value of x
    }
}

void generate_compiled_sprite(
    uint8_t *source,
    uint16_t width_in_16_pixel_blocks,
    uint16_t height_in_lines
) {
    uint16_t bytes_to_skip_after_each_line = FRAMEBUFFER_BYTES_PER_LINE - (width_in_16_pixel_blocks * BYTES_PER_16_PIXELS);
    uint16_t *current_source = (uint16_t *)source;

    uint16_t source_mask;
    uint16_t source_plane_0;
    uint16_t source_plane_1;
    uint16_t source_plane_2;
    uint16_t source_plane_3;

    uint16_t advance_before_next_write = 0;
    uint16_t source_planes_0123_mirror = 0;
    uint16_t last_d0_high = 0;
    uint16_t last_d0_low = 0;
    uint16_t tmp;

    write_word_to_compiled_sprite_output(0x7200); // moveq.l #0,d1
 
    for (uint16_t y = 0; y < height_in_lines; y++) {
        for (uint16_t x = 0; x < width_in_16_pixel_blocks; x++) {
            source_mask = *current_source;
            current_source++;
            source_plane_0 = *current_source;
            current_source++;
            source_plane_1 = *current_source;
            current_source++;
            source_plane_2 = *current_source;
            current_source++;
            source_plane_3 = *current_source;
            current_source++;

            if (source_mask == 0) {
                if (advance_before_next_write > 0) {
                    write_advance_a1_to_compiled_sprite_output(advance_before_next_write);
                    advance_before_next_write = 0;
                }

                source_planes_0123_mirror = 0;

                if (source_plane_0 == 0 && source_plane_1 == 0) {
                    write_word_to_compiled_sprite_output(0x22c1); // move.l d1,(a1)+ [12 cycles]
                } else {
                    if ((source_plane_0 == source_plane_3) && (source_plane_1 == source_plane_2)) {
                        source_planes_0123_mirror = 1;
                    }

                    if (source_plane_0 == 0 && source_plane_1 <= 0x7f) {
                        write_word_to_compiled_sprite_output(0x7000 | source_plane_1); // moveq.l immediate,d0 [4 cycles]
                        write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                        last_d0_high = 0;
                        last_d0_low = source_plane_1;
                    } else if ((source_plane_0 == last_d0_high) && (source_plane_1 == last_d0_low)) {
                        write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                    } else if (source_plane_0 == last_d0_high) {
                        write_word_to_compiled_sprite_output(0x303c); // move.w immediate,d0 [8 cycles]
                        write_word_to_compiled_sprite_output(source_plane_1); // second half of immediate
                        last_d0_low = source_plane_1;
                        write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                    } else {
                        write_word_to_compiled_sprite_output(0x203c); // move.l immediate,d0 [12 cycles]
                        write_word_to_compiled_sprite_output(source_plane_0); // first half of immediate
                        write_word_to_compiled_sprite_output(source_plane_1); // second half of immediate
                        last_d0_high = source_plane_0;
                        last_d0_low = source_plane_1;
                        write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                    }
                }

                if (source_plane_2 == 0 && source_plane_3 == 0) {
                    write_word_to_compiled_sprite_output(0x22c1); // move.l d1,(a1)+ [12 cycles]
                } else if (source_planes_0123_mirror) {
                    write_word_to_compiled_sprite_output(0x4840); // swap d0
                    tmp = last_d0_high;
                    last_d0_high = last_d0_low;
                    last_d0_low = tmp;
                    write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+
                } else if ((source_plane_2 == last_d0_high) && (source_plane_3 == last_d0_low)) {
                    write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+
                } else if (source_plane_2 == last_d0_high) {
                    write_word_to_compiled_sprite_output(0x303c); // move.w immediate,d0 [8 cycles]
                    write_word_to_compiled_sprite_output(source_plane_3); // second half of immediate
                    last_d0_low = source_plane_3;
                    write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                } else {
                    write_word_to_compiled_sprite_output(0x203c); // move.l immediate,d0 [12 cycles]
                    write_word_to_compiled_sprite_output(source_plane_2); // first half of immediate
                    write_word_to_compiled_sprite_output(source_plane_3); // second half of immediate
                    last_d0_high = source_plane_2;
                    last_d0_low = source_plane_3;
                    write_word_to_compiled_sprite_output(0x22c0); // move.l d0,(a1)+ [12 cycles]
                }
            } else if (source_mask == 0xffff) {
                // skip this destination
                advance_before_next_write += 8;
            } else {
                if (advance_before_next_write > 0) {
                    write_advance_a1_to_compiled_sprite_output(advance_before_next_write);
                    advance_before_next_write = 0;
                }

                // needs to be masked
                write_word_to_compiled_sprite_output(0x203c); // move.l immediate,d0 [12 cycles]
                write_word_to_compiled_sprite_output(source_mask); // first half of immediate
                write_word_to_compiled_sprite_output(source_mask); // second half of immediate
                last_d0_high = source_mask;
                last_d0_low = source_mask;

                if ((source_plane_0 != 0) || (source_plane_1 != 0)) {
                    write_word_to_compiled_sprite_output(0xc191); // and.l d0,(a1) [20 cycles]
                    // potential optimisation here: source_plane_0 is the same as source_plane_1
                    // if so, put value in a register
                    if (source_plane_1 == 0) {
                        write_word_to_compiled_sprite_output(0x0059); // or.w immediate,(a1)+ [16 cycles]
                        write_word_to_compiled_sprite_output(source_plane_0);
                        write_advance_a1_to_compiled_sprite_output(2); // [8 cycles]
                    } else if (source_plane_0 == 0) {
                        write_advance_a1_to_compiled_sprite_output(2); // [8 cycles]
                        write_word_to_compiled_sprite_output(0x0059); // or.w immediate,(a1)+ [16 cycles]
                        write_word_to_compiled_sprite_output(source_plane_1);
                    } else {
                        write_word_to_compiled_sprite_output(0x0099); // or.l immediate,(a1)+ [28 cycles]
                        write_word_to_compiled_sprite_output(source_plane_0); // first half of immediate
                        write_word_to_compiled_sprite_output(source_plane_1); // second half of immediate
                    }
                } else {
                    write_word_to_compiled_sprite_output(0xc199); // and.l d0,(a1)+ [20 cycles]
                }

                if ((source_plane_2 != 0) || (source_plane_3 != 0)) {
                    write_word_to_compiled_sprite_output(0xc191); // and.l d0,(a1) [20 cycles]
                    // potential optimisation here: source_plane_2 is the same as source_plane_3
                    // if so, put value in a register

                    if (source_plane_3 == 0) {
                        write_word_to_compiled_sprite_output(0x0059); // or.w immediate,(a1)+ [16 cycles]
                        write_word_to_compiled_sprite_output(source_plane_2);
                        write_advance_a1_to_compiled_sprite_output(2); // [8 cycles]
                    } else if (source_plane_2 == 0) {
                        write_advance_a1_to_compiled_sprite_output(2); // [8 cycles]
                        write_word_to_compiled_sprite_output(0x0059); // or.w immediate,(a1)+ [16 cycles]
                        write_word_to_compiled_sprite_output(source_plane_3);
                    } else {
                        write_word_to_compiled_sprite_output(0x0099); // or.l immediate,(a1)+ [28 cycles]
                        write_word_to_compiled_sprite_output(source_plane_2); // first half of immediate
                        write_word_to_compiled_sprite_output(source_plane_3); // second half of immediate
                    }
                } else {
                    write_word_to_compiled_sprite_output(0xc199); // and.l d0,(a1)+ [20 cycles]
                }
            }
        }

        advance_before_next_write += bytes_to_skip_after_each_line;
    }

    write_word_to_compiled_sprite_output(0x4e75); // rts
}

void skew_source_to_buffer(
    uint8_t *source,
    uint16_t width_in_16_pixel_blocks,
    uint16_t height_in_lines,
    uint16_t skew,
    uint8_t *destination
) {
    uint16_t source_mask;
    uint16_t source_plane_0;
    uint16_t source_plane_1;
    uint16_t source_plane_2;
    uint16_t source_plane_3;

    uint16_t prev_source_mask;
    uint16_t prev_source_plane_0;
    uint16_t prev_source_plane_1;
    uint16_t prev_source_plane_2;
    uint16_t prev_source_plane_3;

    uint16_t dest_mask;
    uint16_t dest_plane_0;
    uint16_t dest_plane_1;
    uint16_t dest_plane_2;
    uint16_t dest_plane_3;

    uint16_t *current_source = (uint16_t *)source;
    uint16_t *current_destination = (uint16_t *)destination;

    for (uint16_t y = 0; y < height_in_lines; y++) { 
        prev_source_mask = 0xffff;
        prev_source_plane_0 = 0;
        prev_source_plane_1 = 0;
        prev_source_plane_2 = 0;
        prev_source_plane_3 = 0;
        for (uint16_t x = 0; x < width_in_16_pixel_blocks; x++) {
            source_mask = *current_source;
            current_source++;
            source_plane_0 = *current_source;
            current_source++;
            source_plane_1 = *current_source;
            current_source++;
            source_plane_2 = *current_source;
            current_source++;
            source_plane_3 = *current_source;
            current_source++;

            dest_mask = (source_mask >> skew) | (prev_source_mask << (16-skew));
            *current_destination = dest_mask;
            current_destination++;
            dest_plane_0 = (source_plane_0 >> skew) | (prev_source_plane_0 << (16-skew));
            *current_destination = dest_plane_0;
            current_destination++;
            dest_plane_1 = (source_plane_1 >> skew) | (prev_source_plane_1 << (16-skew));
            *current_destination = dest_plane_1;
            current_destination++;
            dest_plane_2 = (source_plane_2 >> skew) | (prev_source_plane_2 << (16-skew));
            *current_destination = dest_plane_2;
            current_destination++;
            dest_plane_3 = (source_plane_3 >> skew) | (prev_source_plane_3 << (16-skew));
            *current_destination = dest_plane_3;
            current_destination++;

            prev_source_mask = source_mask;
            prev_source_plane_0 = source_plane_0;
            prev_source_plane_1 = source_plane_1;
            prev_source_plane_2 = source_plane_2;
            prev_source_plane_3 = source_plane_3;
        }

        dest_mask = (prev_source_mask << (16-skew)) | (0xffff >> skew);
        *current_destination = dest_mask;
        current_destination++;
        dest_plane_0 = (prev_source_plane_0 << (16-skew));
        *current_destination = dest_plane_0;
        current_destination++;
        dest_plane_1 = (prev_source_plane_1 << (16-skew));
        *current_destination = dest_plane_1;
        current_destination++;
        dest_plane_2 = (prev_source_plane_2 << (16-skew));
        *current_destination = dest_plane_2;
        current_destination++;
        dest_plane_3 = (prev_source_plane_3 << (16-skew));
        *current_destination = dest_plane_3;
        current_destination++;
    }
}

void remap_3bpp_to_4bpp(
    uint8_t *source,
    uint16_t width_in_16_pixel_blocks,
    uint16_t height_in_lines,
    uint8_t *destination
) {
    uint16_t source_mask;
    uint16_t source_plane_0;
    uint16_t source_plane_1;
    uint16_t source_plane_2;

    uint16_t *current_source = (uint16_t *)source;
    uint16_t *current_destination = (uint16_t *)destination;

    for (uint16_t y = 0; y < height_in_lines; y++) { 
        for (uint16_t x = 0; x < width_in_16_pixel_blocks; x++) {
            source_mask = *current_source;
            current_source++;
            source_plane_0 = *current_source;
            current_source++;
            source_plane_1 = *current_source;
            current_source++;
            source_plane_2 = *current_source;
            current_source++;

            *current_destination++ = source_mask;
            *current_destination++ = source_plane_0;
            *current_destination++ = source_plane_1;
            *current_destination++ = source_plane_2;
            *current_destination++ = 0;
        }
    }
}

void generate_compiled_sprite_all_skews(
    uint8_t *source,
    uint16_t width_in_16_pixel_blocks,
    uint16_t height_in_lines,
    uint16_t bitplanes,
    uint8_t **current_skew_compiled_sprite_ptr
) {
    uint8_t *normalised_source;
    //uint8_t **current_skew_compiled_sprite_ptr = compiled_sprite_output;
    //compiled_sprite_output += sizeof(current_skew_compiled_sprite_ptr) * SKEW_COUNT;

    for (uint16_t skew = 0; skew < SKEW_COUNT; skew++) {
        current_skew_compiled_sprite_ptr = &compiled_sprite_output;
        
        /*if (bitplanes == 3) {
            remap_3bpp_to_4bpp(source, width_in_16_pixel_blocks, height_in_lines, &remap_buffer);
            normalised_source = &remap_buffer;
        } else {
            normalised_source = source;
        }*/

        normalised_source = source;

        if (skew == 0) {
            generate_compiled_sprite(normalised_source, width_in_16_pixel_blocks, height_in_lines);
        } else {
            skew_source_to_buffer(normalised_source, width_in_16_pixel_blocks, height_in_lines, skew, skew_buffer);
            generate_compiled_sprite(skew_buffer, width_in_16_pixel_blocks + 1, height_in_lines);
        }
        current_skew_compiled_sprite_ptr++;
    }

    //current_skew_compiled_sprite_ptr++;
}

/*void generate_compiled_sprite_all_sizes(void *current_scenery_item_ptr)
{
    uint8_t **current_size_ptr_ptr = (void **)current_scenery_item_ptr;
    uint8_t *current_size_ptr;

    for (uint16_t index = 0; index < SPRITE_SIZE_COUNT; index++) {
        current_size_ptr = *current_size_ptr_ptr;

        uint16_t width_in_pixels = *((uint16_t *)current_size_ptr);
        uint16_t height_in_lines = *((uint16_t *)(current_size_ptr+2));
        uint16_t bitplanes = *((uint16_t *)(current_size_ptr+4));
        uint8_t *source = *((void **)(current_size_ptr+8));
        *((uint8_t **)(current_size_ptr+12)) = compiled_sprite_output;
        *((uint16_t *)(current_size_ptr+16)) = 0xf00d; // a compiled sprite is available

        generate_compiled_sprite_all_skews(source, (width_in_pixels+15)>>4, height_in_lines, bitplanes);
        current_size_ptr_ptr++;
    }
}*/

void generate_all_compiled_sprites()
{
    compiled_sprite_output = compiled_sprite_cache;
    struct SpriteDefinition *current_sprite_definition = sprite_definitions;

    for (uint16_t index = 0; index < SCENERY_ITEM_COUNT; index++) {
        generate_compiled_sprite_all_skews(
            (uint8_t *)current_sprite_definition->words, 
            ((current_sprite_definition->source_data_width)+15)>>4,
            current_sprite_definition->source_data_height,
            4,
            current_sprite_definition->compiled_sprites
        );
        current_sprite_definition++;
    }
}
