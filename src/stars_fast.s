    public _draw_stars_fast

_draw_stars_fast:
    ; a0 = current_star_position
    ; a1 = star_xpos_dest_offsets
    ; a2 = star_plot_values
    ; a3 = star_xpos_source_offsets
    ; a4 = current_star_block_offset
    ; usp = drawing_playfield->buffer

    move.l sp,a0

    movem.l d2-d7/a2-a6,-(sp)

    ; pass in address of drawing_playfield->star_block_offsets (a4)
    move.l 4(a0),a4

    ; pass in address of drawing_playfield->buffer (a5)
    move.l 8(a0),a5
    move.l a5,usp

    lea _star_xpos_dest_offsets,a1
    lea _star_plot_values,a2
    lea _star_xpos_source_offsets,a3
    lea _star_positions,a0

    move.l _mountains_shift,d4
    swap d4 ; normalised_mountains_shift
    add.l d4,d4

    moveq.l #49,d7
    moveq.l #0,d1

_one_star:

    move.w (a0)+,d0 ; current_star_position->original_xpos
    sub.w d4,d0    ; -normalised_mountains_shift
    bge.s _still_on_screen

    add.w #640,d0  ; move star back on screen

_still_on_screen:

    move.w (a0)+,d1      ; add offset for start of destination line (ypos * 160)
    add.w (a1,d0),d1     ; add offset from star_xpos_dest_offsets, d1 is now offset within framebuffer
    move.w d1,(a4)+      ; write block offset
    move.l usp,a5
    add.l d1,a5

    move.l a2,a6
    add.w (a0)+,a6      ; get offset for start of source data based on background colour
    add.w (a3,d0),a6       ; add offset from star_xpos_source_offsets

    lea 2(a0),a0 ; skip final property

    move.l (a6)+,(a5)+
    move.l (a6)+,(a5)+

    dbra d7,_one_star
    movem.l (sp)+,d2-d7/a2-a6

    rts



