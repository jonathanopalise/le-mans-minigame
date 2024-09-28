    public _mountains_render_fast

_mountains_render_fast:
    movem.l d0-d7/a0-a6,-(sp)

    lea _mountain_graphics,a0
    move.l a0,d1           ; d1 = line start source (mountain_graphics)

    move.l _drawing_playfield,a1 ; drawing playfield is in a1
    move.l (a1),d2 ; d2 = drawing_playfield->buffer
    add.w #(160*90),d2     ; d2 = line start dest

    move.w #29,d3          ; d3 = line_count

    move.w _mountains_shift,d4 ; d4 = scroll_pixels = mountains_shift >> 16

    cmp.w 6(a1),d4               ; compare drawing_playfield->mountains_scroll_pixels with scroll_pixels
    bne.s _render_required

    moveq.l #0,d5
    move.w 4(a1),d5 ; d5 is tallest sprite ypos
    
    cmp.w #90,d5 ; compare drawing_playfield->tallest_sprite_ypos with 90
    ble.s _render_required

    sub.w #90,d5 ; lines_to_skip = drawing_playfield->tallest_sprite_ypos - 90
    sub.w d5,d3  ; line_count -= lines_to_skip
    blt _all_done ; if line_count < 1, nothing to do

    add.w d5,d5
    lea _multiply_160,a2
    move.w (a2,d5),d5 ; d5 is now source_dest_advance

    add.w d5,d1 ; line_start_source += source_dest_advance
    add.w d5,d2 ; line_start_dest += source_dest_advance

_render_required:
    ; from here on i still need
    ; d1.l = line_start_source
    ; d2.l = line_start_dest
    ; d3.w = line_count
    ; d4.w = scroll_pixels

    lea $ffff8a20.w,a6

    move.w #4,(a6)+        ; source x increment 8a20
    move.w #80,(a6)+      ; source y increment 8z22
    move.l a6,a0           ; store 8a24 address in a0
    addq.l #4,a6
    moveq.l #-1,d0
    move.l d0,(a6)+        ; endmask 1 and endmask 2 - 8a28 and 8a2a
    move.w d0,(a6)+        ; endmask 3 8a2c
    move.w #8,(a6)+        ; dest x increment 8a2e
    move.w #8,(a6)+        ; dest y increment 8a30
    move.l a6,a1           ; store 8a32 address in a1
    ;move.l d4,(a1)        ; not sure why this is here
    addq.l #4,a6
    move.w #20,(a6)+       ; x count 8a36
    move.l a6,a2           ; store y count address 8a38 in a2
    addq.l #2,a6
    move.w #$0203,(a6)+    ; hop/op = $0203
    move.l a6,a4           ; store blitter control 8a3c in a4

    move.w d4,d5
    neg.w d5
    subq.w #1,d5           ; current_skew = ((-scroll_pixels)-1)
    
    move.w d5,d7
    and.w #15,d7
    or.w #$c080,d7         ; blitter_control_word = 0xc080 | (current_skew & 15)

    moveq.l #0,d5
    move.w d4,d5
    asr.w #2,d5
    and.w #$fffc,d5
    add.l d5,d1            ; line_start_source += (scroll_pixels >> 2) & 0xfffffffc;

    ; d5 is free for use

    move.w d3,d5
    lsr.w #1,d5       ; line_count >> 1 (will be used for line_count_pass_1)
    move.w d5,d6      ; copy to d6      (will be used for line_count_pass_2)

    and.w #1,d3       ; line_count & 1
    add.w d3,d5       ; line_count_pass_1 += (line_count & 1)

    ; from here on i still need
    ; d1.l = line_start_source
    ; d2.l = line_start_dest
    ; d3.w = line_count
    ; d4.w = scroll_pixels
    ; d5.w = line_count_pass_1
    ; d6.w = line_count_pass_2
    ; d7.w = blitter control word

    ; drawing playfield buffer + 160*90 = d4
    ; blitter source = a0
    ; blitter dest = a1
    ; blitter y count = a2
    ; blitter control = a4

    move.l d1,(a0)
    move.l d2,(a1)
    move.w d5,(a2)
    move.w d7,(a4)

    tst.w d6
    beq.s _skip_pass_2_plane_0

    move.w d6,(a2)
    move.w d7,(a4)

_skip_pass_2_plane_0:

    add.l #2,d1
    add.w #2,d2

    move.l d1,(a0)
    move.l d2,(a1)
    move.w d5,(a2)
    move.w d7,(a4)

    tst.w d6
    beq.s _all_done

    move.w d6,(a2)
    move.w d7,(a4)

_all_done:

    cmp.w 6(a1),d4 ; set scroll_pixels on drawing_playfield

    movem.l (sp)+,d0-d7/a0-a6
    rts
