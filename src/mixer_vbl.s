    public _mixer_vbl

; --- variable + fixed frequency 2-channel mixer. ideally put near the top of the vbl

_mixer_vbl:
	movem.l		d0-d3/a0-a2,-(sp)

	lea.l		$ffff8900.w,a0																; dma audio base address								
	move.w		#%0000011111111111,$24(a0)													; write microwire mask

	move.w		#63,$22(a0)																	; (JT) set master volume
	lea.l		addressAudioCurrentStart,a1
	move.b		9(a1),$03(a0)																; set start address high byte
	move.b		10(a1),$05(a0)																; set start address middle byte
	move.b		11(a1),$07(a0)																; set start address low byte
	move.b		13(a1),$0f(a0)																; set end address high byte
	move.b		14(a1),$11(a0)																; set end address middle byte
	move.b		15(a1),$13(a0)																; set end address low byte
	move.b		#1,$01(a0)																	; (re)start dma

	movem.l		(a1),d0-d3																	; thanks to Defence Force for pointing out movem as being quicker here!
	exg.l		d0,d2																		; I think this also saves some time in combination with another movem...
	exg.l		d1,d3
	movem.l		d0-d3,(a1)

	move.l		d2,a2																		; copy start address of work buffer for mixing routines

	move.w		variableEngineEffectPosition,d0												; current step into engine effect

	move.l		tableSoundEvents,a0															; first entry in table contains base address of engine sound effect
	lea.l		(a0,d0.w),a0																; offset current engine effect position into engine sound effect base address

    move.w      _player_car_speed,d0
	lsl.w		#4,d0																		; multiply revs by 4 to get scaler table offset
	lea.l		table12517HzScaler,a1														; scaler table base address
	move.l		(a1,d0.w),d1																; fetch value from scaler table offset

	moveq		#0,d0																		; clear it for use as engine offset
	swap		d1

	rept		250
	move.b		(a0,d0.w),(a2)+
	addx.l		d1,d0																			; effectively divide offset by 65536
	endr

	tst.w		variableSoundEventLatch														; is there a sound event?
	bmi			labelFinishedSoundMixing													; if not then just mix the engine sound

	move.l		variableSoundEventAddress,a1												; current sound event sample base address											
	move.w		variableSoundEventPosition,d2												; offset into sample data
	lea.l		(a1,d2),a1																	; adjust address

	lea.l		-250(a2),a2
	rept		250
	move.b		(a1)+,d2
	add.b		d2,(a2)+																	; put accumulated sample value into dma buffer
	endr

	add.w		#250,variableSoundEventPosition												; store current position of sound event effect
	move.w		variableSoundEventLength,d1													; fetch sound event length
	cmp.w		variableSoundEventPosition,d1												; compare current sound event position with sound event length
	bhi.s		labelFinishedSoundMixing													; if sound event length is higher than current position then nothing to do
	move.w		#$ffff,variableSoundEventLatch												; set sound event latch to null

labelFinishedSoundMixing

	add.w		d0,variableEngineEffectPosition												; store current position of engine sound effect
	cmp.w		#3116,variableEngineEffectPosition											; compare engine sound effect length with current position
	blo.s		labelFinishedAudio															; if current position is less than length then nothing to do
	sub.w		#3116,variableEngineEffectPosition											; otherwise adjust position

labelFinishedAudio

	movem.l		(sp)+,d0-d3/a0-a2

	rts
