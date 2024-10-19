    public _mixer_vbl

; --- variable + fixed frequency 2-channel mixer. ideally put near the top of the vbl

_mixer_vbl:
	movem.l		d0-d3/a0-a3,-(sp)

	lea.l		$ffff8900.w,a0																; dma audio base address								
	lea.l		addressAudioCurrentStart,a1													; base address of audio variables
	move.l		a1,a3																		; copy it

	movem.l		(a1),d0-d3																	; thanks to Defence Force for pointing out movem as being quicker here!
	exg.l		d0,d2																		; I think this also saves some time in combination with another movem...
	exg.l		d1,d3
	movem.l		d0-d3,(a1)

	addq.l		#1,a1																		; skip upper byte of audio buffer start address
	move.b		(a1)+,$03(a0)																; set start address high byte
	move.b		(a1)+,$05(a0)																; set start address middle byte (of buffer a)
	move.b		(a1)+,$07(a0)																; set start address low byte
	addq.l		#1,a1																		; skip upper byte of audio buffer end address
	move.b		(a1)+,$0f(a0)																; set end address high byte
	move.b		(a1)+,$11(a0)																; set end address middle byte (of buffer a)
	move.b		(a1)+,$13(a0)																; set end address low byte

	move.b		#1,$01(a0)																	; (re)start dma

	move.l		d2,a2																		; copy start address of work buffer for mixing routines

	move.w		16(a3),d0																	; variableEngineEffectPosition - current offset into engine sound effect

	move.l		tableSoundEvents,a0															; first entry in table contains base address of engine sound effect
	lea.l		(a0,d0.w),a0																; offset current engine effect position into engine sound effect base address

	moveq		#0,d1																		; makes sure there's no cruft in d1 before it's swapped
    move.w      _player_car_speed,d1														; fetch current speed
	lsl.w		#5,d1																		; multiply speed by 32
	add.w		#27167,d1																	; add "idle speed / 0rpm" scaler value to current speed (max value at 299mph should end up totalling 65535)
	swap		d1																			; d1 is now frequency scaler

	moveq		#0,d0																		; clear it for use as engine offset

	rept		250
	move.b		(a0,d0.w),(a2)+
	addx.l		d1,d0																		; effectively divide offset by 65536
	endr

	tst.w		18(a3)																		; variableSoundEventLatch - is there a sound event?
	bmi			labelFinishedSoundMixing													; if not then just mix the engine sound

	move.l		20(a3),a1																	; variableSoundEventAddress - current sound event sample base address											
	move.w		28(a3),d2																	; variableSoundEventPosition - offset into sample data
	lea.l		(a1,d2),a1																	; adjust address

	lea.l		-250(a2),a2
	rept		62																			; need to mix 250 bytes in total - start by copying first 248 bytes as longwords
	move.l		(a1)+,d2																	; fetch sample data
	add.l		d2,(a2)+																	; add it to sample data already in buffer
	endr
	move.w		(a1)+,d2																	; fetch the last two bytes as a word
	add.w		d2,(a2)+																	; add it to sample data already in buffer

	add.w		#250,28(a3)																	; variableSoundEventPosition - store current position of sound event effect
	move.w		24(a3),d1																	; variableSoundEventLength - fetch sound event length
	cmp.w		28(a3),d1																	; variableSoundEventPosition - compare current sound event position with sound event length
	bhi.s		labelFinishedSoundMixing													; if sound event length is higher than current position then nothing to do
	move.w		#$ffff,18(a3)																; variableSoundEventLatch - set sound event latch to null

labelFinishedSoundMixing

	add.w		d0,16(a3)																	; variableEngineEffectPosition - store current position of engine sound effect
	cmp.w		#1324,16(a3)																; variableEngineEffectPosition - compare engine sound effect length with current position
	blo.s		labelFinishedAudio															; if current position is less than length then nothing to do
	sub.w		#1324,16(a3)																; variableEngineEffectPosition - otherwise adjust position

labelFinishedAudio

	movem.l		(sp)+,d0-d3/a0-a3

	rts
