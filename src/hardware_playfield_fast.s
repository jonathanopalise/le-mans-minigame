    public _hardware_playfield_erase_sprites_fast
    public _hardware_playfield_copy_score_fast
    public _hardware_playfield_transfer_score_fast

_hardware_playfield_erase_sprites_fast:

    move.l sp,a0

    movem.l d2-d7/a2-a6,-(sp)

    ; pass in drawing_playfield->current_bitplane_draw_record and assign to d6 (?)
    ; pass in drawing_playfield->buffer and assign to a2
    ; pass in drawing_playfield->bitplane_draw_records and assign to a0

    addq.l #4,a0
    move.l (a0)+,d6 ; drawing_playfield->current_bitplane_draw_record
    move.l (a0)+,$ffff8a32.w ; set top word of drawing_playfield->buffer into blitter destination
    move.l (a0)+,a0 ; drawing_playfield->bitplane_draw_records

    moveq.l #-1,d0
    move.l d0,$ffff8a28.w
    move.w d0,$ffff8a2c.w
    move.w #8,$ffff8a2e.w
    moveq.l #-64,d7 ; control word - aka $c0
    moveq.l #$f,d4

    lea.l _multiply_160,a1 ; a1 is multiply_160

    lea $ffff8a34.w,a4 ; blitter destination (note: points to 2nd word of register)
    lea $ffff8a38.w,a5 ; blitter ycount
    lea $ffff8a3a.w,a2 ; hop/op
    lea $ffff8a3c.w,a6 ; blitter_control

    moveq.l #90,d0 ; four_bitplane_threshold
    move.w #0,(a2) ; hop/op ; initial state of hop/op - means that we don't need to set for 2bpl passes
    tst.w _time_of_day_is_night
    beq.s _erase_sprite

    add.w #29,d0 ; adjust four_bitplane_threshold

_erase_sprite:

    cmp.l d6,a0 ; while (current_bitplane_draw_record < drawing_playfield->current_bitplane_draw_record)
    beq _all_done

    ; TODO: does the destination need to be a long?
    move.l (a0)+,a3  ; destination_address = current_bitplane_draw_record->destination_address
    move.w (a0)+,$ffff8a30.w ; blitter dest y increment = current_bitplane_draw_record->destination_y_increment
    move.w (a0)+,$ffff8a36.w  ; blitter x count = current_bitplane_draw_record->x_count
    move.w (a0)+,d2 ; d2 is current_bitplane_draw_record->y_count
    move.w (a0)+,d1 ; fetch current_bitplane_draw_record->ypos

    cmp.w d0,d1 ; compare current_bitplane_draw_record->ypos (d1) with four_bitplane_threshold (d0)

    blt.s _above_threshold ; jump to _above_threshold if ypos is less than threshold
    
    ; non-split erase here

    bra.s _two_bitplane_start

_above_threshold: ; start in 4bpl region

    move.w d2,d5 ; copy current_bitplane_draw_record->y_count into d5
    add.w d1,d5  ; end_ypos (d5) = current_bitplane_draw_record-ypos + current_bitplane_draw_record->y_count
    cmp.w d0,d5  ; compare end_ypos (d5) with four_bitplane_threshold (d0)
    bgt.s _split_erase

    ; non-split erase starts here

    move.w d2,d3 ; four_bitplane_line_count (d3) = current_bitplane_draw_record->y_count
    moveq.l #0,d2 ; two_bitplane_line_count (d2) = 0

    bra.s _erase_calcs_complete

_split_erase:

    ; d3 (four_bitplane_line_count) = four_bitplane_threshold (d0) - current_bitplane_draw_record->ypos (d1)
    move.w d0,d3
    sub.w d1,d3

    ; d2 (two_bitplane_line_count) = end_ypos (d5) - four_bitplane_threshold (d0)
    move.w d5,d2
    sub.w d0,d2

_erase_calcs_complete:

    tst.w d3 ; four_bitplane_line_count - anything to do?
    beq.s _four_bitplane_end

    ; four bitplane erase
    move.w d4,(a2) ; hop/op

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    clr.w (a2) ; hop/op

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control
    addq.l #2,a3

    move.w a3,(a4)    ; blitter destination
    move.w d3,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control

_four_bitplane_end:

    tst.w d2 ; two_bitplane_line_count - anything to do?
    beq.s _erase_sprite

    subq.l #6,a3

    add.w d3,d3       ; change ycount into multiply_160 lookup offset
    add.w (a1,d3),a3  ; destination += multiply_160[four_bitplane_line_count]

_two_bitplane_start:

    ; two bitplane erase

    addq.l #4,a3
    move.w a3,(a4)    ; blitter destination
    move.w d2,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control

    addq.l #2,a3
    move.w a3,(a4)    ; blitter destination
    move.w d2,(a5)    ; ycount
    move.b d7,(a6)  ; blitter control

    bra _erase_sprite

_all_done:

    movem.l (sp)+,d2-d7/a2-a6
    rts

_hardware_playfield_copy_score_fast:

    movem.l a2-a5,-(sp)

    move.l _drawing_playfield,a1
    move.l (a1),a1 ; drawing_playfield->buffer

    lea $ffff8a20.w,a0
    move.l #$80070,(a0)+   ; source x increment = 8, source y increment = 112
    move.l a1,(a0)+      ; source address 8a24
    move.l #$3ffff,(a0)+ ; endmask1 + endmask2
    move.l #$fc000008,(a0)+ ; endmask3 + destination x increment
    move.w #120,(a0)+    ; destination y increment
    move.l a1,(a0)+      ; destination address 8a32
    move.w #6,(a0)+      ; x count 8a36
    move.w #$0203,$ffff8a3a.w ; hop/op

    lea 3032(a1),a4
    lea 3152(a1),a5

    lea $ffff8a26.w,a0   ; source address + 2 (for word writes)
    lea $ffff8a34.w,a1   ; destination address +2 (for word writes)
    lea $ffff8a38.w,a2   ; y count
    lea $ffff8a3c.w,a3   ; blitter control

    moveq.l #9,d0        ; y count
    move.w #$c085,d1     ; blitter control word

    move.w a4,(a0)
    move.w a5,(a1)
    move.w d0,(a2)
    move.w d1,(a3)

    addq.l #2,a4
    addq.l #2,a5

    move.w a4,(a0)
    move.w a5,(a1)
    move.w d0,(a2)
    move.w d1,(a3)

    addq.l #2,a4
    addq.l #2,a5

    move.w a4,(a0)
    move.w a5,(a1)
    move.w d0,(a2)
    move.w d1,(a3)

    addq.l #2,a4
    addq.l #2,a5

    move.w a4,(a0)
    move.w a5,(a1)
    move.w d0,(a2)
    move.w d1,(a3)

    movem.l (sp)+,a2-a5
    rts

_hardware_playfield_transfer_score_fast:
    move.l sp,a0

    move.l a2,-(sp)

    move.l 4(a0),a2                  ; dest buffer
    lea 160*19(a2),a2

    move.l _score_source_playfield,a0     ; drawing playfield struct
    move.l (a0),a0                   ; get buffer
    lea 160*19(a0),a0

    moveq.l #-1,d0

    lea.l $ffff8a20.w,a1
    move.w #2,(a1)+                  ; source x increment
    move.w #(160-48)+2,(a1)+           ; source y increment
    move.l a0,(a1)+                  ; source address
    move.l d0,(a1)+                  ; endmask1 and endmask2
    move.w d0,(a1)+                  ; endmask3
    move.w #2,(a1)+                  ; destination x increment
    move.w #(160-48)+2,(a1)+           ; destination y increment

    move.l a2,(a1)+                  ; destination address

    move.w #24,(a1)+                 ; x count
    move.w #9,(a1)+
    move.w #$0203,(a1)+              ; hop/op

    move.w #$c000,(a1)

    move.l (sp)+,a2

    rts
