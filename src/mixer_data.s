    public dataSounds
    public tableSoundEvents

; --- data and table
dataSounds							incbin		samples/faster-sounds.snd								

; ID	name					size
; 0		engine					1574
; 1		start beep low			6258
; 2 	start beep high			6258
; 3 	checkpoint beep			626
; 4 	crash					8282
; 5		bounce loud				5132
; 6 	bounce quiet			5132
; 7		skid					10620
; 8		pass opponent loud		12792
; 9 	pass opponent medium	12792
; 10 	pass opponent quiet		12792

    align 1
tableSoundEvents					;		engine (always needs to be first in list!)
									dc.l    dataSounds
                                    dc.w    0
                                    dc.w	0

 									;		start beep (low pitch)
                                    dc.l	dataSounds+$626
									dc.w	6258															; length
									dc.w	3000															; retrig time * sample frame

 									;		start beep (high pitch)
                                    dc.l	dataSounds+$1f92
									dc.w	6258															; length
									dc.w	3000															; retrig time * sample frame

 									;		checkpoint beep
                                    dc.l	dataSounds+$38fe
									dc.w	626																; length
									dc.w	250																; retrig time * sample frame

 									;		crash
                                    dc.l	dataSounds+$3c6a
									dc.w	8282															; length
									dc.w	4000															; retrig time * sample frame

 									;		bounce (normal volume)
                                    dc.l	dataSounds+$5dbe
									dc.w	13772															; length
									dc.w	6750															; retrig time * sample frame

 									;		bounce (low volume)
                                    dc.l	dataSounds+$72c4
									dc.w	13772															; length
									dc.w	6750															; retrig time * sample frame

 									;		skid
                                    dc.l	dataSounds+$87ca
									dc.w	10620															; length
									dc.w	5250															; retrig time * sample frame

 									;		pass opponent car (normal volume)
                                    dc.l	dataSounds+$b240
									dc.w	12792															; length
									dc.w	6250															; retrig time * sample frame

 									;		pass opponent car (medium volume)
                                    dc.l	dataSounds+$e532
									dc.w	12792															; length
									dc.w	6250															; retrig time * sample frame

									;		pass opponent car (low volume)
                                    dc.l	dataSounds+$11824
									dc.w	12792															; length
									dc.w	6250															; retrig time * sample frame
