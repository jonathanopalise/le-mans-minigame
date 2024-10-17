    public _initialise
    public _joy_data
    public _joy0
    public _joy1
    public _sky_gradient
    public _scenery_colours
    public _ground_colours
    public _vertical_shift
    public _vbl_title_screen_palette_source
    ;public _update_joy

_initialise:

    move.w	#$2700,sr       ; Stop all interrupts
    ;move.l ($118),a0
    ;move.l a0,(oldikbd)

    move.l #vbl,$70.w       ; Install our own VBL
    move.l #dummy,$68.w     ; Install our own HBL (dummy)
    move.l #dummy,$134.w    ; Install our own Timer A (dummy)
    move.l #timer_2,$120.w	; Install our own Timer B
    move.l #dummy,$114.w    ; Install our own Timer C (dummy)
    move.l #dummy,$110.w    ; Install our own Timer D (dummy)
	move.l #key_interrupt,$118.w  ; Install our own ACIA (dummy)
    clr.b $fffffa07.w       ; Interrupt enable A (Timer-A & B)
    clr.b $fffffa13.w       ; Interrupt mask A (Timer-A & B)
	move.b #$12,$fffffc02.w ; Kill mouse
     
    move.w #34,-(a7)        ; XBIOS function 34 kbdvbase
    trap   #14
    addq.l #2,a7            ; return IKBD vector table
    move.l d0,a0            ; a0 points to IKBD vectors
    move.l #read_joy,24(a0) ; input my joystick vector

    ; looks like parameters come first, then function name
	move.l #joy_on,-(a7)    ; pointer to IKBD instructions
    move.w #1,-(a7)         ; 2 instructions
    move.w #25,-(a7)        ; XBIOS function 25 ikbdws(WORD cnt, LONG ptr)
    trap   #14
    addq.l #8,a7

    move.w	#$2300,sr       ; Interrupts back on
    rts

_update_joy:
;    move.w #1,_waiting_for_joy

    ;move.w #$16,-(sp)   ; value 16
    ;move.w #4,-(sp)     ; keyboard port
    ;move.w #$3,-(sp)    ; bios console output
    ;trap #13
    ;addq.w #6,sp 

    ; looks like parameters come first, then function name
	;move.l #joy_interrogate,-(a7)    ; pointer to IKBD instructions
    ;move.w #0,-(a7)         ; 1 instructions
    ;move.w #25,-(a7)        ; XBIOS function 25 ikbdws(WORD cnt, LONG ptr)
    ;trap   #14
    ;addq.l #8,a7

;_foo:
;    tst.w _waiting_for_joy
;    beq.s _cont
;    bra.s _foo

_cont:
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
    cmp.w #5,_game_state ; title screen exit to game transition
    beq _title_screen_vbl
    cmp.w #6,_game_state ; game over exit transition
    beq.s game_over_exit_transition_vbl
    cmp.w #7,_game_state ; title screen entry transition
    beq _title_screen_vbl
    cmp.w #10,_game_state ; title screen exit to demo transition
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

;newikbd:
;    move d0,-(sp)
;    move sr,d0
;    and #$f8ff,d0
;    or #$500,d0
;    move d0,sr
;    move (sp)+,d0
;    dc.w $4ef9

oldikbd:
    dc.l 0

joy_on:
    dc.b $14,$12

joy_interrogate:
    dc.b $16,$0

_joy_data:
    dc.w 1

_waiting_for_joy:
    dc.w 1

_vertical_shift:
    dc.w 1

JOY_UP    equ 1
JOY_LEFT  equ 4
JOY_RIGHT equ 8
JOY_DOWN  equ 2
JOY_FIRE  equ $80

;/* key handeler info */
;struct key_handler_info_struct

key_info:
_joy0:     dc.b   0     ;  volatile byte joy0;
_joy1:     dc.b   0     ;  volatile byte joy1;
joy2:     dc.b   0     ;  volatile byte joy2; /* cursor joystick */
last_key: dc.b   0     ;  volatile byte last_key;

.even

key_interrupt:
     move.l    d0,-(sp)          ;
     moveq     #0,d0             ; clear register
     move.b    $fffffc02.w,d0    ; get scancode
     add.w     d0,d0             ; verdubbel keycode
     move.w    .table_start(pc,d0.w),d0; get jump offset
     jmp       .table_start(pc,d0.w)

.table_start:
     dc.w      .default-.table_start ;00
     dc.w      .default-.table_start ;01
     dc.w      .default-.table_start ;02
     dc.w      .default-.table_start ;03
     dc.w      .default-.table_start ;04
     dc.w      .default-.table_start ;05
     dc.w      .default-.table_start ;06
     dc.w      .default-.table_start ;07
     dc.w      .default-.table_start ;08
     dc.w      .default-.table_start ;09
     dc.w      .default-.table_start ;0a
     dc.w      .default-.table_start ;0b
     dc.w      .default-.table_start ;0c
     dc.w      .default-.table_start ;0d
     dc.w      .default-.table_start ;0e
     dc.w      .default-.table_start ;0f
     dc.w      .default-.table_start ;10
     dc.w      .default-.table_start ;11
     dc.w      .default-.table_start ;12
     dc.w      .default-.table_start ;13
     dc.w      .default-.table_start ;14
     dc.w      .default-.table_start ;15
     dc.w      .default-.table_start ;16
     dc.w      .default-.table_start ;17
     dc.w      .default-.table_start ;18
     dc.w      .default-.table_start ;19
     dc.w      .default-.table_start ;1a
     dc.w      .default-.table_start ;1b
     dc.w      .default-.table_start ;1c
     dc.w      .default-.table_start ;1d
     dc.w      .default-.table_start ;1e
     dc.w      .default-.table_start ;1f
     dc.w      .default-.table_start ;20
     dc.w      .default-.table_start ;21
     dc.w      .default-.table_start ;22
     dc.w      .default-.table_start ;23
     dc.w      .default-.table_start ;24
     dc.w      .default-.table_start ;25
     dc.w      .default-.table_start ;26
     dc.w      .default-.table_start ;27
     dc.w      .default-.table_start ;28
     dc.w      .default-.table_start ;29
     dc.w      .default-.table_start ;2a
     dc.w      .default-.table_start ;2b
     dc.w      .default-.table_start ;2c
     dc.w      .default-.table_start ;2d
     dc.w      .default-.table_start ;2e
     dc.w      .default-.table_start ;2f
     dc.w      .default-.table_start ;30
     dc.w      .default-.table_start ;31
     dc.w      .default-.table_start ;32
     dc.w      .default-.table_start ;33
     dc.w      .default-.table_start ;34
     dc.w      .default-.table_start ;35
     dc.w      .default-.table_start ;36
     dc.w      .default-.table_start ;37
     dc.w      .default-.table_start ;38
     dc.w      .cur_fire_p-.table_start ;39
     dc.w      .default-.table_start ;3a
     dc.w      .default-.table_start ;3b
     dc.w      .default-.table_start ;3c
     dc.w      .default-.table_start ;3d
     dc.w      .default-.table_start ;3e
     dc.w      .default-.table_start ;3f
     dc.w      .default-.table_start ;40
     dc.w      .default-.table_start ;41
     dc.w      .default-.table_start ;42
     dc.w      .default-.table_start ;43
     dc.w      .default-.table_start ;44
     dc.w      .default-.table_start ;45
     dc.w      .default-.table_start ;46
     dc.w      .default-.table_start ;47
     dc.w      .cur_up_p-.table_start ;48
     dc.w      .default-.table_start ;49
     dc.w      .default-.table_start ;4a
     dc.w      .cur_left_p-.table_start ;4b
     dc.w      .default-.table_start ;4c
     dc.w      .cur_right_p-.table_start ;4d
     dc.w      .default-.table_start ;4e
     dc.w      .default-.table_start ;4f
     dc.w      .cur_down_p-.table_start ;50
     dc.w      .default-.table_start ;51
     dc.w      .default-.table_start ;52
     dc.w      .default-.table_start ;53
     dc.w      .default-.table_start ;54
     dc.w      .default-.table_start ;55
     dc.w      .default-.table_start ;56
     dc.w      .default-.table_start ;57
     dc.w      .default-.table_start ;58
     dc.w      .default-.table_start ;59
     dc.w      .default-.table_start ;5a
     dc.w      .default-.table_start ;5b
     dc.w      .default-.table_start ;5c
     dc.w      .default-.table_start ;5d
     dc.w      .default-.table_start ;5e
     dc.w      .default-.table_start ;5f
     dc.w      .default-.table_start ;60
     dc.w      .default-.table_start ;61
     dc.w      .default-.table_start ;62
     dc.w      .default-.table_start ;63
     dc.w      .default-.table_start ;64
     dc.w      .default-.table_start ;65
     dc.w      .default-.table_start ;66
     dc.w      .default-.table_start ;67
     dc.w      .default-.table_start ;68
     dc.w      .default-.table_start ;69
     dc.w      .default-.table_start ;6a
     dc.w      .default-.table_start ;6b
     dc.w      .default-.table_start ;6c
     dc.w      .default-.table_start ;6d
     dc.w      .default-.table_start ;6e
     dc.w      .default-.table_start ;6f
     dc.w      .default-.table_start ;70
     dc.w      .default-.table_start ;71
     dc.w      .default-.table_start ;72
     dc.w      .default-.table_start ;73
     dc.w      .default-.table_start ;74
     dc.w      .default-.table_start ;75
     dc.w      .default-.table_start ;76
     dc.w      .default-.table_start ;77
     dc.w      .default-.table_start ;78
     dc.w      .default-.table_start ;79
     dc.w      .default-.table_start ;7a
     dc.w      .default-.table_start ;7b
     dc.w      .default-.table_start ;7c
     dc.w      .default-.table_start ;7d
     dc.w      .default-.table_start ;7e
     dc.w      .default-.table_start ;7f
     dc.w      .default-.table_start ;80
     dc.w      .default-.table_start ;81
     dc.w      .default-.table_start ;82
     dc.w      .default-.table_start ;83
     dc.w      .default-.table_start ;84
     dc.w      .default-.table_start ;85
     dc.w      .default-.table_start ;86
     dc.w      .default-.table_start ;87
     dc.w      .default-.table_start ;88
     dc.w      .default-.table_start ;89
     dc.w      .default-.table_start ;8a
     dc.w      .default-.table_start ;8b
     dc.w      .default-.table_start ;8c
     dc.w      .default-.table_start ;8d
     dc.w      .default-.table_start ;8e
     dc.w      .default-.table_start ;8f
     dc.w      .default-.table_start ;90
     dc.w      .default-.table_start ;91
     dc.w      .default-.table_start ;92
     dc.w      .default-.table_start ;93
     dc.w      .default-.table_start ;94
     dc.w      .default-.table_start ;95
     dc.w      .default-.table_start ;96
     dc.w      .default-.table_start ;97
     dc.w      .default-.table_start ;98
     dc.w      .default-.table_start ;99
     dc.w      .default-.table_start ;9a
     dc.w      .default-.table_start ;9b
     dc.w      .default-.table_start ;9c
     dc.w      .default-.table_start ;9d
     dc.w      .default-.table_start ;9e
     dc.w      .default-.table_start ;9f
     dc.w      .default-.table_start ;a0
     dc.w      .default-.table_start ;a1
     dc.w      .default-.table_start ;a2
     dc.w      .default-.table_start ;a3
     dc.w      .default-.table_start ;a4
     dc.w      .default-.table_start ;a5
     dc.w      .default-.table_start ;a6
     dc.w      .default-.table_start ;a7
     dc.w      .default-.table_start ;a8
     dc.w      .default-.table_start ;a9
     dc.w      .default-.table_start ;aa
     dc.w      .default-.table_start ;ab
     dc.w      .default-.table_start ;ac
     dc.w      .default-.table_start ;ad
     dc.w      .default-.table_start ;ae
     dc.w      .default-.table_start ;af
     dc.w      .default-.table_start ;b0
     dc.w      .default-.table_start ;b1
     dc.w      .default-.table_start ;b2
     dc.w      .default-.table_start ;b3
     dc.w      .default-.table_start ;b4
     dc.w      .default-.table_start ;b5
     dc.w      .default-.table_start ;b6
     dc.w      .default-.table_start ;b7
     dc.w      .default-.table_start ;b8
     dc.w      .cur_fire_r-.table_start ;b9
     dc.w      .default-.table_start ;ba
     dc.w      .default-.table_start ;bb
     dc.w      .default-.table_start ;bc
     dc.w      .default-.table_start ;bd
     dc.w      .default-.table_start ;be
     dc.w      .default-.table_start ;bf
     dc.w      .default-.table_start ;c0
     dc.w      .default-.table_start ;c1
     dc.w      .default-.table_start ;c2
     dc.w      .default-.table_start ;c3
     dc.w      .default-.table_start ;c4
     dc.w      .default-.table_start ;c5
     dc.w      .default-.table_start ;c6
     dc.w      .default-.table_start ;c7
     dc.w      .cur_up_r-.table_start ;c8
     dc.w      .default-.table_start ;c9
     dc.w      .default-.table_start ;ca
     dc.w      .cur_left_r-.table_start ;cb
     dc.w      .default-.table_start ;cc
     dc.w      .cur_right_r-.table_start ;cd
     dc.w      .default-.table_start ;ce
     dc.w      .default-.table_start ;cf
     dc.w      .cur_down_r-.table_start ;d0
     dc.w      .default-.table_start ;d1
     dc.w      .default-.table_start ;d2
     dc.w      .default-.table_start ;d3
     dc.w      .default-.table_start ;d4
     dc.w      .default-.table_start ;5d
     dc.w      .default-.table_start ;d6
     dc.w      .default-.table_start ;d7
     dc.w      .default-.table_start ;d8
     dc.w      .default-.table_start ;d9
     dc.w      .default-.table_start ;da
     dc.w      .default-.table_start ;db
     dc.w      .default-.table_start ;dc
     dc.w      .default-.table_start ;dd
     dc.w      .default-.table_start ;de
     dc.w      .default-.table_start ;df
     dc.w      .default-.table_start ;e0
     dc.w      .default-.table_start ;e1
     dc.w      .default-.table_start ;e2
     dc.w      .default-.table_start ;e3
     dc.w      .default-.table_start ;e4
     dc.w      .default-.table_start ;e5
     dc.w      .default-.table_start ;e6
     dc.w      .default-.table_start ;e7
     dc.w      .default-.table_start ;e8
     dc.w      .default-.table_start ;e9
     dc.w      .default-.table_start ;ea
     dc.w      .default-.table_start ;eb
     dc.w      .default-.table_start ;ec
     dc.w      .default-.table_start ;ed
     dc.w      .default-.table_start ;ee
     dc.w      .default-.table_start ;ef
     dc.w      .default-.table_start ;f0
     dc.w      .default-.table_start ;f1
     dc.w      .default-.table_start ;f2
     dc.w      .default-.table_start ;f3
     dc.w      .default-.table_start ;f4
     dc.w      .default-.table_start ;f5
     dc.w      .default-.table_start ;f6
     dc.w      .default-.table_start ;f7
     dc.w      .default-.table_start ;f8
     dc.w      .default-.table_start ;f9
     dc.w      .default-.table_start ;fa
     dc.w      .default-.table_start ;fb
     dc.w      .default-.table_start ;fc
     dc.w      .default-.table_start ;fd
     dc.w      .new_joy0-.table_start ;fe
     dc.w      .new_joy1-.table_start ;ff

; key action code
.default:
     move.b    $fffffc02.w,last_key; store last key
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.new_joy0:
     move.l    #.get_joy0,$118.w
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.new_joy1:
     move.l    #.get_joy1,$118.w
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done

.get_joy0:
     move.b    $fffffc02.w,_joy0  ; get joy0 status
     move.l    #key_interrupt,$118.w
     rte                         ; done
     
.get_joy1:
     move.b    $fffffc02.w,_joy1  ; get joy1 status
     move.l    #key_interrupt,$118.w
     rte                         ; done

.cur_up_p:
     or.b      #JOY_UP,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_up_r:
     and.b     #!JOY_UP,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_left_p:
     or.b      #JOY_LEFT,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_left_r:
     and.b     #!JOY_LEFT,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_right_p:
     or.b      #JOY_RIGHT,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_right_r:
     and.b     #!JOY_RIGHT,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_down_p:
     or.b      #JOY_DOWN,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_down_r:
     and.b     #!JOY_DOWN,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_fire_p:
     or.b      #JOY_FIRE,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
.cur_fire_r:
     and.b     #$7f,joy2
     move.l    (sp)+,d0          ; restore registers
     rte                         ; done
