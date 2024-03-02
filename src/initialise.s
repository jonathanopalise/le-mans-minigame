    public _initialise
    public _joy_data

_initialise:

    move.w	#$2700,sr       ; Stop all interrupts
    move.l ($118),a0
    move.l a0,(oldikbd)

    move.l #vbl,$70.w       ; Install our own VBL
    move.l #dummy,$68.w     ; Install our own HBL (dummy)
    move.l #dummy,$134.w    ; Install our own Timer A (dummy)
    move.l #timer_2,$120.w	; Install our own Timer B
    move.l #dummy,$114.w    ; Install our own Timer C (dummy)
    move.l #dummy,$110.w    ; Install our own Timer D (dummy)
	move.l #newikbd,$118.w  ; Install our own ACIA (dummy)
    clr.b $fffffa07.w       ; Interrupt enable A (Timer-A & B)
    clr.b $fffffa13.w       ; Interrupt mask A (Timer-A & B)
	move.b #$12,$fffffc02.w ; Kill mouse
     
    move.w #34,-(a7)
    trap   #14
    addq.l #2,a7            ; return IKBD vector table
    move.l d0,a0            ; a0 points to IKBD vectors
    move.l #read_joy,24(a0) ; input my joystick vector
	move.l #joy_on,-(a7)    ; pointer to IKBD instructions
    move.w #1,-(a7)         ; 2 instructions
    move.w #25,-(a7)        ; send instruction to IKBD
    trap   #14
    addq.l #8,a7

    move.w	#$2300,sr       ; Interrupts back on
    rts

read_joy:
    move.b 2(a0),_joy_data ;store joy 1 data
    rts

dummy:
	rte

vbl:
    movem.l d0-d7/a0-a6,-(sp)

    move.w	#$2700,sr			; Stop all interrupts
    move.l	#timer_2,$120.w	; Install our own Timer B
    clr.b	$fffffa1b.w		; Timer B control (stop)
    bset	#0,$fffffa07.w		; Interrupt enable A (Timer B)
    bset	#0,$fffffa13.w		; Interrupt mask A (Timer B)
    move.b	#80+34,$fffffa21.w	; Timer B data (number of scanlines to next interrupt)
    bclr	#3,$fffffa17.w		; Automatic end of interrupt
    move.b	#8,$fffffa1b.w		; Timer B control (event mode (HBL))
    move.b	#9,$fffffa21.w	    ; extra dummy value - see https://www.atari-forum.com/viewtopic.php?t=21847&start=25
    move.w	#$2300,sr			; Interrupts back on

    lea.l sky_gradient,a0
    lea.l $ffff8246.w,a1
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.w (a0)+,(a1)+
    move.w #$321,$ffff8242.w ; mountain colour 1
    move.w #$200,$ffff8244.w ; mountain colour 2
 
    jsr _vbl_handler
    movem.l (sp)+,d0-d7/a0-a6
    rte

    ; timer 1 - top of mountains

timer_2:
    move.w	#$2700,sr			;Stop all interrupts
	;move.l	#timer_2,$120.w			;Install our own Timer B
	;clr.b	$fffffa1b.w			;Timer B control (stop)
	;move.b	#8,$fffffa21.w			;Timer B data (number of scanlines to next interrupt)
	;move.b	#8,$fffffa1b.w			;Timer B control (event mode (HBL))

wait:
    cmpi.b		#4,$fffffa21.w;	; timerb event counter
	bne.s		wait
	clr.b	$fffffa1b.w			;Timer B control (stop)

    ;move.w #$321,$ffff8244.w ; colour 10 = 474 hud laptime
    ;move.w #$200,$ffff8246.w ; colour 10 = 474 hud laptime

    move.w #$040,$ffff8242.w ; grass
    move.w #$777,$ffff8244.w ; lines
    move.w #$222,$ffff8246.w ; road

	move.w	#$2300,sr			;Interrupts back on
    rte



sky_gradient:
    dc.w $07f
    dc.w $0ef
    dc.w $06f
    dc.w $0df
    dc.w $05f
    dc.w $0cf
    dc.w $04f
    dc.w $0bf
    dc.w $03f
    dc.w $0af
    dc.w $02f
    dc.w $08f
    dc.w $00f

newikbd:
    move d0,-(sp)
    move sr,d0
    and #$f8ff,d0
    or #$500,d0
    move d0,sr
    move (sp)+,d0
    dc.w $4ef9

oldikbd:
    dc.l 0

joy_on:
    dc.b $14,$12

_joy_data:
    dc.w 1

