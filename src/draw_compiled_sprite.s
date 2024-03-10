    public _draw_compiled_sprite

; a2 needs to be destination
; source doesn't matter at this point

_draw_compiled_sprite:
    move.l sp,a0
    movem.l d2/a2,-(sp)
    move.l 4(a0),a1
    ; generated START


    move.w #10,$ffff8a20.w ; set source x increment
    move.w #8,$ffff8a2e.w ; set dest x increment
    move.w #$0,$ffff8a3a.w ; set hop/op

    move.w #2,$ffff8a30.w ; dest y increment (per length group)
    move.w #1,$ffff8a36.w ; x count (per length group)

    lea.l 8(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffe,$ffff8a28.w ; set endmask1
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 984(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7,$ffff8a28.w ; set endmask1
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3544(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffc0,$ffff8a28.w ; set endmask1
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3704(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffc0,$ffff8a28.w ; set endmask1
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3864(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ff00,$ffff8a28.w ; set endmask1
    move.w #$c080,$ffff8a3c.w ; set blitter control

    move.w #-6,$ffff8a30.w ; dest y increment (per length group)
    move.w #2,$ffff8a36.w ; x count (per length group)

    lea.l 168(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7fff,$ffff8a28.w ; set endmask1
    move.w #$c000,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    move.w #-14,$ffff8a30.w ; dest y increment (per length group)
    move.w #3,$ffff8a36.w ; x count (per length group)

    lea.l 320(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$1,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$f000,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 480(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$3,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$f800,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 640(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$3fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fff0,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 800(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffc,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 960(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3520(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$1f,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffcf,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3680(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$1f,$ffff8a28.w ; set endmask1
    move.w #$ff80,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$7,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3840(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$f,$ffff8a28.w ; set endmask1
    move.w #$ff00,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$1,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    move.w #-22,$ffff8a30.w ; dest y increment (per length group)
    move.w #4,$ffff8a36.w ; x count (per length group)

    lea.l 1120(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 1280(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 1440(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 1600(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 1760(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 1920(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffff,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2080(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ffff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2240(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2400(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2560(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2720(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$3fff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fffe,$ffff8a2c.w ; set enmove.w #$fffe,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 2880(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$3ff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fff8,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3040(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$1ff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$fff0,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3200(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$ff,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffe0,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    lea.l 3360(a1),a2 ; calc destination address into a2
    move.l a2,$ffff8a32.w ; set destination address
    move.w #$4,$ffff8a38.w ; set ycount (4 bitplanes)
    move.w #$7f,$ffff8a28.w ; set endmask1
    move.w #$ffff,$ffff8a2a.w ; set endmask2 (might be able to merge this and following call)
    move.w #$ffe0,$ffff8a2c.w ; set endmask3
    move.w #$c080,$ffff8a3c.w ; set blitter control

    ; generated END
    movem.l (sp)+,d2/a2
    rts
