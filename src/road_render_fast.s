    lea $ffff8a20.w,a6

    move.w #4,(a6)+        ; source x increment 8a20
    move.w #-78,(a6)+      ; source y increment 8z22
    ;move.l a6,a0           ; store 8a24 address in a0
    addq.l #4,a6
    moveq.l #-1,d0
    move.l d0,(a6)+        ; endmask 1 and endmask 2 - 8a28 and 8a2a
    move.w d0,(a6)+        ; endmask 3 8a2c
    move.w #8,(a6)+        ; dest x increment 8a2e
    move.w #-150,(a6)+     ; dest y increment 8a30
    ;move.l a6,a1           ; store 8a32 address in a1
    addq.l #4,a6
    move.w #20,(a6)+       ; x count 8a36
    move.l a6,a2           ; store y count address 8a38 in a2
    addq.l #2,a6
    move.l a6,a3           ; store hop/op 8a3a in a3
    addq.l #2,a6
    move.l a6,a4           ; store blitter control 8a3c

    lea _byte_offsets,a5
    lea _road_scanlines,a6
    lea _gfx_data,
    ; need another address register for gfx_data
    ; need another address register for current_byte_offset


    ; a0 = source address
    ; a1 = dest address
    ; a2 = y count address
    ; a3 = hop/op address
    ; a4 = blitter control
    ; a5 = byte_offsets
    ; a6 = road_scanlines

    move.w #79,d7

.line:

    dbra d7,.line


; note: gfx_data is an array of words


