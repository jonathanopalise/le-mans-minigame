    public _road_render_fast

_road_render_fast:
    movem.l d0-d7/a0-a6,-(sp)

    jsr _hardware_playfield_get_drawing_playfield

    move.l d0,a0
    move.l (a0),d4
    add.l #(160*119),d4

    lea $ffff8a20.w,a6

    move.w #4,(a6)+        ; source x increment 8a20
    move.w #-78,(a6)+      ; source y increment 8z22
    move.l a6,a0           ; store 8a24 address in a0
    addq.l #4,a6
    moveq.l #-1,d0
    move.l d0,(a6)+        ; endmask 1 and endmask 2 - 8a28 and 8a2a
    move.w d0,(a6)+        ; endmask 3 8a2c
    move.w #8,(a6)+        ; dest x increment 8a2e
    move.w #-150,(a6)+     ; dest y increment 8a30
    move.l a6,a1           ; store 8a32 address in a1
    addq.l #4,a6
    move.w #20,(a6)+       ; x count 8a36
    move.l a6,a2           ; store y count address 8a38 in a2
    addq.l #2,a6
    move.l a6,a3           ; store hop/op 8a3a in a3
    addq.l #2,a6
    move.l a6,a4           ; store blitter control 8a3c

    move.w #$c080,d2       ; blitter control word

    lea _road_scanlines,a5

    ; a0 = source address
    ; a1 = dest address
    ; a2 = y count address
    ; a3 = hop/op address
    ; a4 = blitter control
    ; a5 = road_scanlines
    ; a6 = unused

    move.l d4,(a1) ; set both upper and lower word of destination

    move.w #79,d7

.line:
    ; current_skew = current_road_scanline->current_logical_xpos >> 16;
    move.l 4(a5),d0
    swap d0 ; d0 now contains current skew

    ; skew_adjust = (current_skew >> 2) & 0xfffc;
    move.w d0,d1
    ext.l d1
    asr.w #2,d1
    and.w #$fffc,d1 ; d1 = skew_adjust

    ; *((volatile uint32_t *)BLITTER_DESTINATION_ADDRESS) = line_start_dest;
    move.l d4,(a1)
    
    move.w #2,(a2)    ; *((volatile int16_t *)BLITTER_Y_COUNT) = 2; 
    move.l (a5),d3    ; get current_road_scanline->line_start_source
    subq.l #2,d3      ; line_start_source - 2
    sub.l d1,d3       ; line_start_source - skew_adjust
    move.l d3,(a0)    ; set source

    and.b #15,d0
    move.w #$c080,d2
    or.b d0,d2
    ;move.b d0,d2      ; update control word
    move.w d2,(a4)    ; set control word

    lea 548(a5),a5     ; next road scanline, NOTE: 24 depends on size of struct
    add.w #160,d4     ; move destination address to next line
    dbra d7,.line

    movem.l (sp)+,d0-d7/a0-a6
    rts
