    public _hardware_playfield_erase_sprites_fast

_hardware_playfield_erase_sprites_fast:

    move.l sp,a0

    movem.l d2-d7/a2-a6,-(sp)

    ; pass in drawing_playfield->current_bitplane_draw_record and assign to d6 (?)
    ; pass in drawing_playfield->buffer and assign to a2
    ; pass in drawing_playfield->bitplane_draw_records and assign to a0

    move.l 4(a0),d6 ; drawing_playfield->current_bitplane_draw_record
    move.l 8(a0),$ffff8a32.w ; set top word of drawing_playfield->buffer into blitter destination
    move.l 12(a0),a0 ; drawing_playfield->bitplane_draw_records

    moveq.l #-1,d0
    move.l d0,$ffff8a28.w
    move.w d0,$ffff8a2c.w
    move.w #8,$ffff8a2e.w
    move.b #$c0,d7

    lea.l _multiply_160,a1 ; a1 is multiply_160

    lea $ffff8a34.w,a4 ; blitter destination (note: points to 2nd word of register)
    lea $ffff8a38.w,a5 ; blitter ycount
    lea $ffff8a3a.w,a2 ; hop/op
    lea $ffff8a3c.w,a6 ; blitter_control

    moveq.l #90,d0 ; four_bitplane_threshold
    tst.w _time_of_day_is_night
    beq.s _erase_sprite

    add.w #29,d0 ; adjust four_bitplane_threshold

_erase_sprite:

    cmp.l d6,a0 ; while (current_bitplane_draw_record < drawing_playfield->current_bitplane_draw_record)
    beq _all_done

    move.l (a0)+,a3  ; destination_address = current_bitplane_draw_record->destination_address
    move.w (a0)+,$ffff8a30.w ; blitter dest y increment = current_bitplane_draw_record->destination_y_increment
    move.w (a0)+,$ffff8a36.w  ; blitter x count = current_bitplane_draw_record->x_count
    move.w (a0)+,d2 ; d2 is current_bitplane_draw_record->y_count
    move.w (a0)+,d1 ; fetch current_bitplane_draw_record->ypos

    cmp.w d0,d1 ; compare current_bitplane_draw_record->ypos (d1) with four_bitplane_threshold (d0)

    blt.s _above_threshold ; jump to _above_threshold if ypos is less than threshold
    
    ; non-split erase here

    move.w #0,d3 ; four_bitplane_line_count (d3) = 0
    move.w d2,d4 ; two_bitplane_line_count (d4) = current_bitplane_draw_record->y_count

    bra.s _erase_calcs_complete

_above_threshold: ; start in 4bpl region

    move.w d2,d5 ; copy current_bitplane_draw_record->y_count into d5
    add.w d1,d5  ; end_ypos (d5) = current_bitplane_draw_record-ypos + current_bitplane_draw_record->y_count
    cmp.w d0,d5  ; compare end_ypos (d5) with four_bitplane_threshold (d0)
    bgt.s _split_erase

    ; non-split erase starts here

    move.w d2,d3 ; four_bitplane_line_count (d3) = current_bitplane_draw_record->y_count
    move.w #0,d4 ; two_bitplane_line_count (d4) = 0

    bra.s _erase_calcs_complete

_split_erase:

    ; d3 (four_bitplane_line_count) = four_bitplane_threshold (d0) - current_bitplane_draw_record->ypos (d1)
    move.w d0,d3
    sub.w d1,d3

    ; d4 (two_bitplane_line_count) = end_ypos (d5) - four_bitplane_threshold (d0)
    move.w d5,d4
    sub.w d0,d4

_erase_calcs_complete:

    tst.w d3 ; four_bitplane_line_count - anything to do?
    beq.s _four_bitplane_end

    ; four bitplane erase
    move.w #$f,(a2) ; hop/op

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    move.w #$0,(a2) ; hop/op

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    subq.l #6,a3

    add.w d3,d3       ; change ycount into multiply_160 lookup offset
    add.w (a1,d3),a3  ; destination += multiply_160[four_bitplane_line_count]

_four_bitplane_end:

    tst.w d4 ; two_bitplane_line_count - anything to do?
    beq.s _two_bitplane_end

    ; two bitplane erase

    move.w #0,(a2) ; hop/op

    addq.l #4,a3
    move.w a3,(a4)    ; blitter destination
    move.w d4,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control

    addq.l #2,a3
    move.w a3,(a4)    ; blitter destination
    move.w d4,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control

_two_bitplane_end:

    bra _erase_sprite

_all_done:

    movem.l (sp)+,d2-d7/a2-a6
    rts
