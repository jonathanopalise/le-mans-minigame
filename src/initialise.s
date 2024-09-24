    public _initialise
    public _joy_data
    public _sky_gradient
    public _scenery_colours
    public _ground_colours
    public _vertical_shift
    public _vbl_title_screen_palette_source

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
    move.w #0,_waiting_for_vbl

    cmp.w #4,_game_state ; in game
    beq.s in_game_vbl
    cmp.w #8,_game_state ; in game
    beq.s in_game_vbl
    cmp.w #2,_game_state ; title screen
    beq _title_screen_vbl
    cmp.w #5,_game_state ; title screen exit transition
    beq _title_screen_vbl
    cmp.w #6,_game_state ; game over exit transition
    beq.s game_over_exit_transition_vbl
    cmp.w #7,_game_state ; title screen entry transition
    beq _title_screen_vbl
    rte

_vanilla_vbl:

    rte

game_over_exit_transition_vbl:

    move.w	#$2700,sr			; Stop all interrupts
    movem.l d0-d1/a0-a1,-(sp)

    jsr _music_tick
    jsr _trigger_in_game_colours

    movem.l (sp)+,d0-d1/a0-a1
    rte

in_game_vbl:

    move.w	#$2700,sr			; Stop all interrupts

    movem.l d0-d1/a0-a1,-(sp)

    jsr _mixer_vbl
    jsr _music_tick
    jsr _hardware_playfield_handle_vbl
    jsr _trigger_in_game_colours
    
    movem.l (sp)+,d0-d1/a0-a1
    rte

    ; timer 1 - top of mountains

_trigger_in_game_colours:
    move.l	#timer_1,$120.w	; Install our own Timer B
    clr.b	$fffffa1b.w		; Timer B control (stop)
    bset	#0,$fffffa07.w		; Interrupt enable A (Timer B)
    bset	#0,$fffffa13.w		; Interrupt mask A (Timer B)
    move.w  #((80+4)-29),d0
    add.w   _vertical_shift,d0
    move.b	d0,$fffffa21.w	; Timer B data (number of scanlines to next interrupt)
    bclr	#3,$fffffa17.w		; Automatic end of interrupt
    move.b	#8,$fffffa1b.w		; Timer B control (event mode (HBL))
    move.b	#9,$fffffa21.w	    ; extra dummy value - see https://www.atari-forum.com/viewtopic.php?t=21847&start=25
    move.w	#$2300,sr			; Interrupts back on

    lea.l _sky_gradient,a0

    move.w #$ee1,$ffff8242.w ; status panel colour

    lea.l $ffff8246.w,a1
    move.l (a0)+,(a1)+ ; sky gradient start 8246, 8248
    move.l (a0)+,(a1)+ ; 8a2a, 824c
    move.l (a0)+,(a1)+ ; 824e, 8250
    move.l (a0)+,(a1)+ ; 8252, 8254
    move.l (a0)+,(a1)+ ; 8256, 8258
    move.l (a0)+,(a1)+ ; 825a, 825c
    move.w (a0)+,$ffff8244.w

    move.w (a0),$ffff825e.w  ; index 15 (lamppost illumination and stars)
    rts

timer_1:
    movem.l a0-a1,-(sp)
    move.w	#$2700,sr			;Stop all interrupts

wait_timer_1:
    cmpi.b		#4,$fffffa21.w;	; timerb event counter
	bne.s		wait_timer_1
	clr.b	$fffffa1b.w			;Timer B control (stop)

    ; scenery colours, then tail light
    lea _scenery_colours,a0
    lea $ffff8242.w,a1
    move.l (a0)+,(a1)+ ; mountain colours 1, 2
    lea $ffff8248.w,a1
    move.l (a0)+,(a1)+ ; indexes 4, 5 8248, 824a
    move.l (a0)+,(a1)+ ; indexes 6, 7 824c, 824e 
    move.l (a0)+,(a1)+ ; indexes 8, 9 8250, 8252
    move.l (a0)+,(a1)+ ; indexes 10, 11 8254, 8256
    move.l (a0)+,(a1)+ ; indexes 12, 13 8258, 825a
    move.w (a0)+,(a1)+ ; index 14 825c
    ;move.w #$777,(a1)+  ; index 15 (lamppost illumination and stars)

    ; tail lights should be at index 14
    ;move.w #$f00,(a1)+ ; tail lights

    ;move.w #$133,$ffff8248.w
    ;move.w #$3dc,$ffff824a.w
    ;move.w #$dde,$ffff824c.w
    ;move.w #$d00,$ffff824e.w
    ;move.w #$333,$ffff8250.w
    ;move.w #$eff,$ffff8252.w
    ;move.w #$005,$ffff8254.w
    ;move.w #$fe0,$ffff8256.w
    ;move.w #$ca1,$ffff8258.w
    ;move.w #$1b6,$ffff825a.w

    move.l	#timer_2,$120.w	; Install our own Timer B
    clr.b	$fffffa1b.w		; Timer B control (stop)
    bset	#0,$fffffa07.w		; Interrupt enable A (Timer B)
    bset	#0,$fffffa13.w		; Interrupt mask A (Timer B)
    move.b	#54,$fffffa21.w	; Timer B data (number of scanlines to next interrupt)
    bclr	#3,$fffffa17.w		; Automatic end of interrupt
    move.b	#8,$fffffa1b.w		; Timer B control (event mode (HBL))
    move.b	#9,$fffffa21.w	    ; extra dummy value - see https://www.atari-forum.com/viewtopic.php?t=21847&start=25
	move.w	#$2300,sr			;Interrupts back on
    movem.l (sp)+,a0-a1
    rte

timer_2:
    movem.l a0-a1,-(sp)
    move.w	#$2700,sr			;Stop all interrupts
	;move.l	#timer_2,$120.w			;Install our own Timer B
	;clr.b	$fffffa1b.w			;Timer B control (stop)
	;move.b	#8,$fffffa21.w			;Timer B data (number of scanlines to next interrupt)
	;move.b	#8,$fffffa1b.w			;Timer B control (event mode (HBL))

wait_timer_2:
    cmpi.b		#4,$fffffa21.w;	; timerb event counter
	bne.s		wait_timer_2
	clr.b	$fffffa1b.w			;Timer B control (stop)

    ;move.w #$321,$ffff8244.w ; colour 10 = 474 hud laptime
    ;move.w #$200,$ffff8246.w ; colour 10 = 474 hud laptime

    lea _ground_colours,a0
    lea $ffff8242.w,a1
    move.l (a0)+,(a1)+
    move.w (a0)+,(a1)+
    ;move.w #$221,$ffff8242.w ; grass
    ;move.w #$777,$ffff8244.w ; lines
    ;move.w #$222,$ffff8246.w ; road

	move.w	#$2300,sr			;Interrupts back on
    movem.l (sp)+,a0-a1
    rte

_title_screen_vbl:
    move.w	#$2700,sr			; Stop all interrupts

    movem.l a0-a1,-(sp)
    move.w #24,_title_screen_lines_remaining

    lea $ffff8a20.w,a0
    move.w #0,(a0)+                      ; source x increment
    move.w #2,(a0)+                      ; source y increment
    move.l _vbl_title_screen_palette_source,(a0)+  ; source address
    move.w #$ffff,(a0)+                  ; endmask1
    move.w #$ffff,(a0)+                  ; endmask2
    move.w #$ffff,(a0)+                  ; endmask3
    move.w #0,(a0)+                      ; destination x increment
    move.w #2,(a0)+                    ; destination y increment
    move.l #$ffff8240,(a0)+              ; destination address
    move.w #1,(a0)+                     ; x count
    add.w #2,a0                          ; skip y count
    move.w #$0103,(a0)+                  ; hop/op

    move.l _vbl_title_screen_palette_source,_title_screen_palette_source

    ; set palette for initial 8 lines
    move.l _title_screen_palette_source,a0
    lea.l $ffff8240.w,a1 
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+

    ; now set halftone for next palette change
    lea.l $ffff8a00.w,a1
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    ; end of repeated

    add.l #64,_title_screen_palette_source

    move.l	#_title_screen_line_vbl,$120.w	    ; Install our own Timer B
    clr.b	$fffffa1b.w		    ; Timer B control (stop)
    bset	#0,$fffffa07.w		; Interrupt enable A (Timer B)
    bset	#0,$fffffa13.w		; Interrupt mask A (Timer B)
    move.b	#8,$fffffa21.w	    ; Timer B data (number of scanlines to next interrupt)
    bclr	#3,$fffffa17.w		; Automatic end of interrupt
    move.b	#8,$fffffa1b.w		; Timer B control (event mode (HBL))

    movem.l (sp)+,a0-a1
    move.w	#$2300,sr			; Interrupts back on

    rte

_title_screen_line_vbl:

    move.w	#$2700,sr			;Stop all interrupts

    move.l #$ffff8240,$ffff8a32.w ; set destination to palette registers
    move.w #16,$ffff8a38.w
    move.b #$c0,$ffff8a3c.w

    ; do colour changes here

    movem.l a0-a1,-(sp)

    sub.w #1,_title_screen_lines_remaining
    tst.w _title_screen_lines_remaining
    beq.s _no_more_lines

    move.l _title_screen_palette_source,a0
    lea.l $ffff8a00.w,a1
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    move.l (a0)+,(a1)+
    add.l #32,_title_screen_palette_source

    movem.l (sp)+,a0-a1

	move.w	#$2300,sr			;Interrupts back on
    rte

_no_more_lines:
    clr.b     $fffffa1b.w
    movem.l (sp)+,a0-a1
	move.w	#$2300,sr			;Interrupts back on
    rte

_title_screen_palette_source:
    dc.l 0

_vbl_title_screen_palette_source:
    dc.l 0

_title_screen_lines_remaining:
    dc.w 0

_sky_gradient:
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
    dc.w $09f
    dc.w $01f
    dc.w $fff

_scenery_colours:
    dc.w $321f
    dc.w $200f
    dc.w $133
    dc.w $3dc
    dc.w $dde
    dc.w $d00
    dc.w $333
    dc.w $eff
    dc.w $005
    dc.w $fe0
    dc.w $ca1
    dc.w $1b6
    dc.w $f00

_ground_colours:
    dc.w $221 ; grass
    dc.w $777 ; lines
    dc.w $222 ; road

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

_vertical_shift:
    dc.w 1

