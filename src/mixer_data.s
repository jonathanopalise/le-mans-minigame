    public dataSounds
    public tableSoundEvents

; --- data and table
dataSounds							incbin		samples/faster-sounds.snd								

; 0	engine				1324
; 1	start beep low		12516
; 2 start beep high		12516
; 3 checkpoint beep		626
; 4	pass opponent car	12792
; 5	skid				10620
; 6 crash				8282
; 7	bounce				13772

    align 1
tableSoundEvents					dc.l    dataSounds														; engine
                                    dc.w    0																; n/a
                                    dc.w	0																; n/a
                                    dc.l	dataSounds+$a58													; start beep low
									dc.w	12516															; length
									dc.w	6250															; retrig time * sample frame
									dc.l	dataSounds+$3c36												; start beep high
									dc.w	12516															; length
									dc.w	6250															; retrig time * sample frame	
									dc.l	dataSounds+$6e14												; checkpoint beep
									dc.w	626																; length
									dc.w	250																; retrig time * sample frame
									dc.l	dataSounds+$7180												; pass opponent car
									dc.w	12792															; length
									dc.w	6500															; retrig time * sample frame
									dc.l	dataSounds+$a472												; skid
									dc.w	10620															; length
									dc.w	5250															; retrig time * sample frame
									dc.l	dataSounds+$cee8												; crash
									dc.w	8282															; length
									dc.w	4250															; retrig time * sample frame
									dc.l	dataSounds+$f03c												; bounce
									dc.w	13772															; length
									dc.w	13772															; retrig time * sample frame
