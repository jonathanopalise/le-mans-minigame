    public _draw_speedo_digit

_draw_speedo_digit:

    move.l sp,a0
    movem.l d2-d7/a2-a6,-(sp) 

    move.l 8(a0),a1  ; destination
    move.w 14(a0),d6 ; digit number
    move.l 4(a0),a0  ; source data

    move.w #10,$ffff8a20.w    ; set source x increment
    move.w #10,$ffff8a2e.w     ; set dest x increment CHANGED FROM 8 TO 10
    move.w #$0203,$ffff8a3a.w ; set hop/op
    move.w #$0201,$ffff8a3a.w
    lea $ffff8a24.w,a3        ; cache source address register
    lea $ffff8a32.w,a4        ; cache dest address register
    lea $ffff8a38.w,a5        ; cache ycount register
    lea $ffff8a3c.w,a6        ; cache control register
    moveq.l #-1,d7
    
    ;move.l a0,(a3) ; set lower and upper half of source address
    ;addq.l #2,a3
    ;move.l a1,(a4) ; set lower and upper half of dest address
    ;addq.l #2,a4

    cmp.w #2,d6
    beq _speedo_digit_2
    cmp.w #1,d6
    beq _speedo_digit_1

_speedo_digit_0:
    move.w #4,d0
    move.w #$c000,d1           ; blitter control
    or.w d0,d1                 ; apply skew
    move.w d1,d2               ; copy blitter control
    or.w #$80,d2               ; d2 has fxsr

    move.w d1,d3               ; d1 has nfsr only
    or.w #$40,d3
    move.w d2,d4               ; d4 has fxsr and nfsr
    or.w #$40,d4

    moveq.l #2,d2
    ; cache endmask instruction removed

    move.w #1,$ffff8a36.w ; x count (per length group)

    add.w d2,a0 ; source address into a0 REGISTER
    ; redundant a1 lea instruction removed

    move.w #4088,$ffff8a28.w ; set endmask1
    move.w #16,$ffff8a22.w ; source y increment (per fxsr eligibility) CHANGED FROM 10 TO 16
    move.w #32,$ffff8a30.w ; dest y increment (per length group) CHANGED FROM 24 TO 32

    ; 4 bitplane multiple line copy START
    moveq.l #12,d6

    moveq.l #2,d5
    .loop1:
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d1,(a6) ; set blitter control, fxsr = false, nfsr = false
    add.w d2,a0 ; source address into a0 REGISTER
    add.w d2,a1 ; destination address into a1 REGISTER
    dbra d5,.loop1
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d1,(a6) ; set blitter control, fxsr = false, nfsr = false
    ; redundant a0 lea instruction removed
    ; 4 bitplane multiple line copy END

    movem.l (sp)+,d2-d7/a2-a6
    rts

_speedo_digit_1:
    move.w #15,d0
    move.w #$c000,d1           ; blitter control
    or.w d0,d1                 ; apply skew
    move.w d1,d2               ; copy blitter control
    or.w #$80,d2               ; d2 has fxsr

    move.w d1,d3               ; d1 has nfsr only
    or.w #$40,d3
    move.w d2,d4               ; d4 has fxsr and nfsr
    or.w #$40,d4 

    moveq.l #2,d1
    ; cache endmask instruction removed

    move.w d1,$ffff8a36.w ; x count (per length group) REGISTER (3)

    add.w d1,a0 ; source address into a0 REGISTER
    ; redundant a1 lea instruction removed

    move.w #1,$ffff8a28.w ; set endmask1
    move.w #65280,$ffff8a2c.w ; set endmask3
    move.w #10,$ffff8a22.w ; source y increment (per fxsr eligibility)
    move.w #16,$ffff8a30.w ; dest y increment (per length group)

    ; 4 bitplane multiple line copy START
    moveq.l #12,d6

    moveq.l #2,d5
    .loop1:
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d3,(a6) ; set blitter control, fxsr = false, nfsr = true
    add.w d1,a0 ; source address into a0 REGISTER
    add.w d1,a1 ; destination address into a1 REGISTER
    dbra d5,.loop1
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d3,(a6) ; set blitter control, fxsr = false, nfsr = true
    ; redundant a0 lea instruction removed
    ; 4 bitplane multiple line copy END

    movem.l (sp)+,d2-d7/a2-a6
    rts

_speedo_digit_2:
    move.w #10,d0
    move.w #$c000,d1           ; blitter control
    or.w d0,d1                 ; apply skew
    move.w d1,d2               ; copy blitter control
    or.w #$80,d2               ; d2 has fxsr

    move.w d1,d3               ; d1 has nfsr only
    or.w #$40,d3
    move.w d2,d4               ; d4 has fxsr and nfsr
    or.w #$40,d4 

    moveq.l #2,d1
    ; cache endmask instruction removed

    move.w d1,$ffff8a36.w ; x count (per length group) REGISTER (3)

    add.w d1,a0 ; source address into a0 REGISTER
    ; redundant a1 lea instruction removed

    move.w #63,$ffff8a28.w ; set endmask1
    move.w #57344,$ffff8a2c.w ; set endmask3
    move.w #10,$ffff8a22.w ; source y increment (per fxsr eligibility)
    move.w #24,$ffff8a30.w ; dest y increment (per length group) CHANGED TO 24

    ; 4 bitplane multiple line copy START
    moveq.l #12,d6

    moveq.l #2,d5
    .loop1:
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d3,(a6) ; set blitter control, fxsr = false, nfsr = true
    add.w d1,a0 ; source address into a0 REGISTER
    add.w d1,a1 ; destination address into a1 REGISTER
    dbra d5,.loop1
    move.l a0,(a3) ; set source address
    move.l a1,(a4) ; set destination address
    move.w d6,(a5) ; set ycount
    move.w d3,(a6) ; set blitter control, fxsr = false, nfsr = true
    ; redundant a0 lea instruction removed
    ; 4 bitplane multiple line copy END

    movem.l (sp)+,d2-d7/a2-a6
    rts

