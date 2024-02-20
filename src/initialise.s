    public _initialise
    public _joy_data

_initialise:

    move.w	#$2700,sr       ; Stop all interrupts
    move.l ($118),a0
    move.l a0,(oldikbd)

    move.l #vbl,$70.w       ; Install our own VBL
    move.l #dummy,$68.w     ; Install our own HBL (dummy)
    move.l #dummy,$134.w    ; Install our own Timer A (dummy)
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
    jsr _vbl_handler
    movem.l (sp)+,d0-d7/a0-a6
    rte

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

