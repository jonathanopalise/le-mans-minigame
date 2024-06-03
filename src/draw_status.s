    public _draw_status

_draw_status:

    move.l sp,a0
    movem.l d2-d7/a2-a6,-(sp)    

    move.l 4(a0),a6       ; source data
    move.l 8(a0),a1      ; destination
    move.w 14(a0),d2      ; data width pixels
    move.w 18(a0),d3      ; data height lines
    move.w 22(a0),d6      ; skew
    move.w #8,d4          ; unadjusted source y increment value
    move.l a6,a0

    tst.w d6
    beq _noskew


_noskew:

    lea _leftendmasks,a2
    move.w d6,d0              ; skew into a0
    add.w d0,d0               ; generate lookup table address
    move.w (a2,d0),d0         ; endmask1 value
    move.w d0,$ffff8a28.w     ; set endmask1

    move.w #$ffff,$ffff8a2a.w ; set endmask2

    lea _rightendmasks,a2     ; could just advance existing a2 pointer here
    move.w d2,d0              ; data width pixels into d0
    add.w d6,d0               ; calculate skewed sprite width

    move.w d2,d1              ; copy unskewed sprite width to d1
    add.w #15,d1
    lsr.w #4,d1               ; number of 16 pixel blocks required by unskewed sprite
    move.w d0,d5              ; copy skewed sprite width to d5
    add.w #15,d5
    lsr.w #4,d5               ; number of 16 pixel blocks required by skewed sprite
    cmp.w d1,d5               ; do the unskewed and skewed sprites need the same number of 16 pixel blocks?
    beq.s _no_block_add

    moveq.l #0,d4             ; adjust source y increment

_no_block_add:

    ; d5 should be xcount

    and.w #$f,d0              ; endmask3 lookup table index from skewed width
    add.w d0,d0               ; endmask3 lookup table offset
    move.w (a2,d0),d0         ; endmask3 value
    move.w d0,$ffff8a2c.w     ; set endmask3

    move.w #8,$ffff8a20.w     ; source x increment = 8

    ; when skew is zero, source y inc needs to be 0
    ; when skew is non-zero, source y inc needs to be 8
    move.w d4,$ffff8a22.w     ; source y increment DOUBLE CHECK!
    move.w #8,$ffff8a2e.w     ; dest x increment = 8

    move.w d5,$ffff8a36.w     ; x count

    ; so we are trying to do 4 blitter passes
    ; each pass draws all words on all lines within a single plane
    ; so it'll be something like 160 - (xcount * 8)

    move.w #168,d1
    lsl.w #3,d5               ; existing xcount value - this can be tidied up
    sub.w d5,d1               ; compute dest y increment
    move.w d1,$ffff8a30.w     ; set dest y increment

    move.w #$0203,$ffff8a3a.w ; set hop/op

    ; a0 needs to be source address
    ; a1 needs to be destination address
    ; a2 needs to be address of blitter y count register
    ; a4 needs to be address of blitter source register
    ; a5 needs to be address of blitter dest register
    ; a6 needs to be address of blitter control register
    ; d3 is lines to be drawn
    ; d6 is blitter start instruction $c0 (lower byte should be already be populated with skew)

    lea $ffff8a38.w,a2
    lea $ffff8a24.w,a4
    lea $ffff8a32.w,a5
    lea $ffff8a3c.w,a6

    move.b d6,$ffff8a3d.w ; skew

    ; DON'T FORGET TO TEST SKEW (it should work because move.b below)
    move.b #$c0,d6                      ; blitter start instruction
    move.w #$0203,($ffff8a3a).w         ; hop/op: read from source, source | destination

    move.l a0,(a4)             ; set source address
    move.l a1,(a5)             ; set destination
    move.w d3,(a2)
    move.b d6,(a6)

    addq.l #2,a1                        ; move dest to next bitplane
    addq.l #2,a0                        ; move source to next bitplane

    move.l a0,(a4)             ; set source address
    move.l a1,(a5)             ; set destination
    move.w d3,(a2)
    move.b d6,(a6)

    addq.l #2,a1                        ; move dest to next bitplane
    addq.l #2,a0                        ; move source to next bitplane

    move.l a0,(a4)             ; set source address
    move.l a1,(a5)             ; set destination
    move.w d3,(a2)
    move.b d6,(a6)

    addq.l #2,a1                        ; move dest to next bitplane
    addq.l #2,a0                        ; move source to next bitplane

    move.l a0,(a4)             ; set source address
    move.l a1,(a5)             ; set destination
    move.w d3,(a2)
    move.b d6,(a6)

    movem.l (sp)+,d2-d7/a2-a6

    rts

_leftendmasks:

    dc.w %1111111111111111
    dc.w %0111111111111111
    dc.w %0011111111111111
    dc.w %0001111111111111
    dc.w %0000111111111111
    dc.w %0000011111111111
    dc.w %0000001111111111
    dc.w %0000000111111111
    dc.w %0000000011111111
    dc.w %0000000001111111
    dc.w %0000000000111111
    dc.w %0000000000011111
    dc.w %0000000000001111
    dc.w %0000000000000111
    dc.w %0000000000000011
    dc.w %0000000000000001

_rightendmasks:

    dc.w %1111111111111111
    dc.w %1000000000000000
    dc.w %1100000000000000
    dc.w %1110000000000000
    dc.w %1111000000000000
    dc.w %1111100000000000
    dc.w %1111110000000000
    dc.w %1111111000000000
    dc.w %1111111100000000
    dc.w %1111111110000000
    dc.w %1111111111000000
    dc.w %1111111111100000
    dc.w %1111111111110000
    dc.w %1111111111111000
    dc.w %1111111111111100
    dc.w %1111111111111110


