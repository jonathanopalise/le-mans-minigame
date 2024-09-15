    public _draw_stars_fast

_draw_stars_fast:
    ; a0 = current_star_position
    ; a1 = line_background_colours
    ; a2 = star_plot_values
    ; a3 = multiply_160
    ; a4 = current_star_block_offset
    ; usp = drawing_playfield->buffer

    move.l sp,a0

    movem.l d2-d7/a2-a6,-(sp)

    ; pass in address of drawing_playfield->star_block_offsets (a4)
    move.l 4(a0),a4

    ; pass in address of drawing_playfield->buffer (a5)
    move.l 8(a0),a5
    move.l a5,usp

        lea _line_background_colours,a1
        lea _star_plot_values,a2
        lea _multiply_160,a3
        lea _star_positions,a0

        move.l _mountains_shift,d4
        swap d4 ; normalised_mountains_shift

        moveq.l #49,d7

    _one_star:

        move.w (a0),d0 ; current_star_position->original_xpos
        sub.w d4,d0    ; -normalised_mountains_shift
        bge.s _still_on_screen

        add.w #320,d0  ; move star back on screen

    _still_on_screen:

        move.l d0,d3       ; save shifted star xpos for later

        move.w 2(a0),d1    ; current_star_position->ypos

        moveq.l #0,d2      ; clear top byte of word in d2 (might be able to tidy up later)
        move.b (a1,d1),d2  ; line_background_colours[ypos]
        lsl.w #6,d2        ; (background_colour << 6)
        and.w #15,d0       ; (shifted_star_xpos & 15)
        lsl.w #2,d0        ; (shifted_star_xpos & 15) << 2
        add.w d2,d0        ; (background_colour << 6) + ((shifted_star_xpos & 15) << 2)
        add.w d0,d0        ; uint16_t offset into star_plot_values

        move.l a2,a6       ; star plot values
        add.w d0,a6        ; star_plot_values with offset

        ; ypos is in d1, xpos is in d3

        add.w d1,d1        ; adjust d1 to be index into multiply_160 table

        move.w (a3,d1),d1  ; multiply d1 by 160
        lsr.w #1,d3        ; shifted_star_xpos >> 1
        and.w #$f8,d3      ; shifted_star_xpos & 0xf8
        add.w d1,d3        ; multiply_160[ypos] + ((shifted_star_xpos >> 1) & 0xf8) 
        move.w d3,(a4)     ; *current_star_block_offset = block_offset

        move.l usp,a5      ; get drawing buffer address
        add.w d3,a5        ; add block offset

        move.l (a6)+,(a5)+
        move.l (a6)+,(a5)+

        lea 4(a0),a0       ; current_star_position++;
        lea 2(a4),a4       ; current_star_block_offset++

        dbra d7,_one_star
        movem.l (sp)+,d2-d7/a2-a6

        rts



