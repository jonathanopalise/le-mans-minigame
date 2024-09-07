dbasell     equ $ffff820d   ; addr of low byte of this reg
dbasel      equ $ffff8203   ; display base low
color0      equ $ffff8240   ; color palette #0

    move.l  $42e.w,d0
    swap    d0
    lsr.w   #3,d0 ; d2 = double counts of 512 blocks (so 512k = 1, 1 meg = 2...)
    cmp.w   #2,d0
    blt.s   fail

    move.b #$5a,dbasell.w    ; see if dbasell is RAM
    tst.b   dbasel.w            ; read another register to destroy
                                ; capacitance effects; dbasel is 00.
    move.b  dbasell.w,d0    ; read; don't say cmp.b because that
                                ; would put $5a on the bus.
    cmp.b   #$5a,d0       ; NOW cmp.b
    bne.s   fail
    
    clr.b   dbasell.w         ; try the test again using zero
    tst.w   color0.w            ; color0 is $FFF
    tst.b   dbasell.w         ; read back - should be zero.
    bne.s   fail

    lea $ffff8240.w,a0
    moveq.l #0,d0
    move.w d0,(a0)+
    move.w #$333,(a0)+

    move.l $44e,a1               ; logbase
    moveq.l #4,d0                ; 10 lines
line:
    jsr drawfivelogos
    lea 160*20+16(a1),a1
    jsr drawfivelogos
    lea 160*20-16(a1),a1
    dbra d0,line

    rts

fail:
    lea $ffff8240.w,a0
    moveq.l #0,d0
    move.w d0,(a0)+
    move.w #$755,(a0)+

    move.l $44e,a1               ; logbase
    lea 160*98+7*8(a1),a1
    lea.l errordata(pc),a0       ; a0 = error source

    moveq.l #4,d0                ; 5 lines
errorline:
    rept 6
    move.w (a0)+,(a1)            ; write to dest and increment source
    addq.l #8,a1                 ; next block of 16 pixels
    endr
    move.w (a0)+,(a1)            ; write to dest and increment source
    lea 160-8*6(a1),a1
    dbra d0,errorline

doomloop:
    bra.s doomloop

drawfivelogos:
    move.l a1,a2
    moveq.l #4,d1

drawtenlogosloop:
    jsr drawlogo
    lea 32(a2),a2
    dbra d1,drawtenlogosloop

drawlogo:
    lea.l logodata(pc),a0        ; a0 = logo source
    move.l a2,a3                ; copy dest to a2

    moveq.l #19,d2               ; 20 lines
logoline:
    move.w (a0)+,(a3)            ; write to dest and increment source
    addq.l #8,a3                 ; next block of 16 pixels
    move.w (a0)+,(a3)            ; write to dest and increment source
    lea 160-8(a3),a3
    dbra d2,logoline
    rts

logodata:

    dc.w %0000000000000110,%0000000000000000
    dc.w %0000000000000110,%0000000000000000
    dc.w %0110110111100111,%1101101100111100
    dc.w %0111101100110110,%0001111001100110
    dc.w %0111001100110110,%0001110001100110
    dc.w %0110001111110110,%0001100001100110
    dc.w %0110001100000110,%0001100001100110
    dc.w %0110001100110110,%0001100001100110
    dc.w %0110000111100011,%1101100000111100
    dc.w %0000000000000000,%0000000000000000
    dc.w %0101101111000111,%0110111100011110
    dc.w %0111011001101101,%0000110110110110
    dc.w %0110011001101100,%0110110110110010
    dc.w %0110011001101100,%0110110110110010
    dc.w %0110011001101100,%0110110110110010
    dc.w %0110011001101101,%0110110110110110
    dc.w %0110001111100111,%0110110110011110
    dc.w %0000000000000000,%0000000000000010
    dc.w %1111111111111111,%1111111111111110
    dc.w %0000000000000000,%0000000000000000

errordata:
    
    dc.w %0100001000101111,%1001111000011110,%1111101111100011,%1100111110011100,%1000101111101111,%0011111011110010,%0000010000010000
    dc.w %1100001101101000,%0010000000100000,%0010001000000010,%0010100000100010,%1000100010001000,%1010000010001010,%0010100010100000
    dc.w %0100001010101111,%0010011000011100,%0010001111000011,%1100111100101010,%1000100010001111,%0011110010001010,%0000100000100000
    dc.w %0100001000101000,%0010001000000010,%0010001000000010,%0010100000100110,%1000100010001000,%1010000010001000,%0010100010100000
    dc.w %1110001000101111,%1001111000111100,%0010001111100010,%0010111110011110,%0111001111101000,%1011111011110010,%0000010000010000
