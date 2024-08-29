    public _music_init
    public _music_stop
    public _music_tick

_music_init:
    lea _music_data,a0
    jsr (a0)
    move.w #1,_music_active
    rts

_music_stop:
    clr.w _music_active
    lea _music_data,a0
    lea 4(a0),a0
    jsr (a0)
    
    rts

_music_tick:
    tst.w _music_active
    beq.s _music_tick_failed

    lea _music_data,a0
    lea 8(a0),a0
    jsr (a0)
_music_tick_failed:
    rts

_music_active:  
    dc.w 0

_music_data:
    incbin jracer.snd

