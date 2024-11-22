    public _title_sound_play
    public _title_sound_address
    public _title_sound_length

_title_sound_play:
	lea.l		$ffff8900.w,a0							; dma audio base address

	;move.l		#dataSounds,addressAudioCurrentStart
	;lea.l		addressAudioCurrentStart,a1

    lea.l _title_sound_address,a1

	move.b		1(a1),$03(a0)							; set start address high byte
	move.b		2(a1),$05(a0)							; set start address middle byte
	move.b		3(a1),$07(a0)							; set start address low byte	

    move.l      _title_sound_length,d0
	add.l		d0,_title_sound_address							; size of sample

	move.b		1(a1),$0f(a0)							; set end address high byte
	move.b		2(a1),$11(a0)							; set end address middle byte
	move.b		3(a1),$13(a0)							; set end address low byte

	move.b		#%10000001,$21(a0)						; set dma sound to mono 12517Hz

	move.b		#1,$01(a0)								; start dma	

    rts

_title_sound_address:
    ds.l 1

_title_sound_length:
    ds.l 1
