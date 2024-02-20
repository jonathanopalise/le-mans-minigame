*------------------------------------------------------------------------------*
*	BrownELF GCC startup: dml/2017
*------------------------------------------------------------------------------*
        
        xdef    ___cxa_pure_virtual
	xdef	__ZSt17__throw_bad_allocv

	xdef	_memcpy
	xdef	_memset

	xref	_main
	xref	_exit
	xdef	__exit
	xdef	___cxa_guard_acquire
	xdef	___cxa_guard_release

	xref	___libc_csu_init

*------------------------------------------------------------------------------*

BASEPAGE_SIZE		=		$100
USPS			=		$100*4

			ifd		ELF_CONFIG_STACK
SSPS			=		(ELF_CONFIG_STACK)
			else
SSPS			=		$4000
			endc

bbreak	macro
	andi		#~2,ccr
	bvc.s		*
	endm

*------------------------------------------------------------------------------*
__crt_entrypoint:	xdef		__crt_entrypoint
_start:			xdef		_start
*------------------------------------------------------------------------------*
	move.l		4(sp),a5
*-------------------------------------------------------*
*	command info
*-------------------------------------------------------*
;	lea		128(a5),a4
;	move.l		a4,cli
*-------------------------------------------------------*
*	Mshrink
*-------------------------------------------------------*
	move.l		12(a5),d0			; text segment
	add.l		20(a5),d0			; data segment
	add.l		28(a5),d0			; bss segment
	add.l		#BASEPAGE_SIZE+USPS,d0		; base page
*-------------------------------------------------------*
	move.l		a5,d1				; address to basepage
	add.l		d0,d1				; end of program
	and.w		#-16,d1				; align stack
	move.l		sp,d2
	move.l		d1,sp				; temporary USP stackspace
	move.l		d2,-(sp)	
*-------------------------------------------------------*
	move.l		d0,-(sp)
	move.l		a5,-(sp)
	clr.w		-(sp)
	move.w		#$4a,-(sp)
	trap		#1				; Mshrink
	lea		12(sp),sp	
*-------------------------------------------------------*
*	Program
*-------------------------------------------------------*
	bsr		user_start
*-------------------------------------------------------*
*	Begone
*-------------------------------------------------------*
	clr.w		-(sp)				; Pterm0
	trap		#1

user_start:
			
	; clear bss segment
					
	move.l		$18(a5),a0
	move.l		$1c(a5),d0				;length of bss segment
	move.l		d0,-(sp)
	pea		0.w
	move.l		a0,-(sp)
	jsr		_memset
	lea		12(sp),sp

;	if (REDIRECT_OUTPUT_TO_SERIAL==1)  
;	; redirect to serial
;	
;	move.w		#2,-(sp)
;	move.w		#1,-(sp)
;	move.w		#$46,-(sp)
;	trap		#1
;	addq.l		#6,sp
;
;	endif
	
	pea		super_start
	move.w		#38,-(sp)
	trap		#14
	addq.l		#6,sp

	rts

; --------------------------------------------------------------
super_start:
; --------------------------------------------------------------
	lea		new_ssp,a0
	move.l		a0,d0
	subq.l		#4,d0	
	and.w		#-16,d0
	move.l		d0,a0
	move.l		sp,-(a0)
	move.l		usp,a1
	move.l		a1,-(a0)
	move.l		a0,sp
	
;	__libc_csu_init(int argc, char **argv, char **envp);

	move.l		#0,-(sp)
	pea		dummy_argv
	pea		dummy_envp
	jsr		___libc_csu_init
	lea		12(sp),sp

	move.l		sp,entrypoint_ssp	

	jsr		_main
	
;	link to high level exit(0) function on return
	pea		0.w
	jmp		_exit
	
__exit:

;	level SSP, because exit() is a subroutine

	move.l		entrypoint_ssp,sp

	move.l		(sp)+,a0
	move.l		a0,usp
	move.l		(sp)+,sp
	rts
	
; --------------------------------------------------------------
_memcpy:	
; --------------------------------------------------------------
			rsreset
; --------------------------------------------------------------
.sp_return:		rs.l	1
.sp_pdst:		rs.l	1
.sp_psrc:		rs.l	1
.sp_size:		rs.l	1
; --------------------------------------------------------------
;	move.l		.sp_pdst(sp),a0
;	move.l		.sp_psrc(sp),a1
	move.l		.sp_pdst(sp),d0
	move.l		d0,a0
	move.l		.sp_psrc(sp),d1
	move.l		d1,a1
	or.w		d0,d1
	btst		#0,d1
	bne.s		.memcpy_misaligned
	
	move.l		.sp_size(sp),d1
	
	lsr.l		#4,d1					; num 16-byte blocks total
	move.l		d1,d0
	swap		d0					; num 1mb blocks (64k * 16bytes)
	subq.w		#1,d1					; num 16-byte blocks remaining
	bcs.s		.ev1mb

.lp1mb:
.lp16b:	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	dbra		d1,.lp16b

.ev1mb:	subq.w		#1,d0
	bpl.s		.lp1mb

	moveq		#16-1,d1
	and.w		.sp_size+2(sp),d1
	lsl.b		#4+1,d1
	bcc.s		.n8
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
.n8:	add.b		d1,d1
	bcc.s		.n4
	move.l		(a1)+,(a0)+
.n4:	add.b		d1,d1
	bcc.s		.n2
	move.w		(a1)+,(a0)+
.n2:	add.b		d1,d1
	bcc.s		.n1
	move.b		(a1)+,(a0)+
.n1:
	move.l		.sp_pdst(sp),d0
	rts

.memcpy_misaligned:
	move.w		a1,d1
	eor.w		d0,d1
	btst		#0,d1
	bne		.memcpy_misaligned_sgl
		
.memcpy_misaligned_pair:		
	move.l		.sp_size(sp),d1
	
	move.b		(a1)+,(a0)+
	subq.l		#1,d1
	beq		.done
	move.w		d1,.sp_size+2(sp)
	
	lsr.l		#4,d1					; num 16-byte blocks total
	move.l		d1,d0
	swap		d0					; num 1mb blocks (64k * 16bytes)
	subq.w		#1,d1					; num 16-byte blocks remaining
	bcs.s		.ev1mc

.lp1mc:
.lp16c:	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
	dbra		d1,.lp16c

.ev1mc:	subq.w		#1,d0
	bpl.s		.lp1mc

	moveq		#16-1,d1
	and.w		.sp_size+2(sp),d1
	lsl.b		#4+1,d1
	bcc.s		.n8c
	move.l		(a1)+,(a0)+
	move.l		(a1)+,(a0)+
.n8c:	add.b		d1,d1
	bcc.s		.n4c
	move.l		(a1)+,(a0)+
.n4c:	add.b		d1,d1
	bcc.s		.n2c
	move.w		(a1)+,(a0)+
.n2c:	add.b		d1,d1
	bcc.s		.n1c
	move.b		(a1)+,(a0)+
.n1c:
.done:	move.l		.sp_pdst(sp),d0
	rts

.memcpy_misaligned_sgl:		
	move.l		.sp_size(sp),d1
	
	lsr.l		#4,d1					; num 16-byte blocks total
	move.l		d1,d0
	swap		d0					; num 1mb blocks (64k * 16bytes)
	subq.w		#1,d1					; num 16-byte blocks remaining
	bcs.s		.ev1md

.lp1md:
.lp16d:	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	dbra		d1,.lp16d

.ev1md:	subq.w		#1,d0
	bpl.s		.lp1md

;	copy remaining bytes, if any

	moveq		#16-1,d1
	and.w		.sp_size+2(sp),d1
	add.w		d1,d1
	neg.w		d1
	jmp		.jtab(pc,d1.w)
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
	move.b		(a1)+,(a0)+
.jtab:	
	move.l		.sp_pdst(sp),d0
	rts
	
; --------------------------------------------------------------
_memset:
; --------------------------------------------------------------	

;	move.l		d2,-(sp)
	move.l		d2,a1
	
	; value
	move.b		0+8+3(sp),d0
	move.b		d0,d1
	lsl.w		#8,d1
	move.b		d0,d1
	move.w		d1,d2
	swap		d2
	move.w		d1,d2

	; size
	move.l		0+12(sp),d1
	
	; dest
	move.l		0+4(sp),d0
	move.l		d0,a0
	and.w		#1,d0
	beq.s		.aligned
	move.b		d2,(a0)+
	subq.l		#1,d1
	beq		.done
	move.w		d1,0+12+2(sp)
.aligned:	
	
	lsr.l		#4,d1
	move.l		d1,d0
	swap		d0
	subq.w		#1,d1
	bcs.s		.ev1mb

.lp1mb:
.lp16b:	move.l		d2,(a0)+
	move.l		d2,(a0)+
	move.l		d2,(a0)+
	move.l		d2,(a0)+
	dbra		d1,.lp16b

.ev1mb:	subq.w		#1,d0
	bpl.s		.lp1mb

	moveq		#16-1,d1
	and.w		0+12+2(sp),d1
	lsl.b		#4+1,d1
	bcc.s		.n8
	move.l		d2,(a0)+
	move.l		d2,(a0)+
.n8:	add.b		d1,d1
	bcc.s		.n4
	move.l		d2,(a0)+
.n4:	add.b		d1,d1
	bcc.s		.n2
	move.w		d2,(a0)+
.n2:	add.b		d1,d1
	bcc.s		.n1
	move.b		d2,(a0)+
.n1:

.done:	move.l		0+4(sp),d0

	move.l		a1,d2
;	move.l		(sp)+,d2
	rts
	
; --------------------------------------------------------------

; --------------------------------------------------------------
___mulsi3:		xdef	___mulsi3
; --------------------------------------------------------------
	move.w		6(sp),d0
	move.l		d0,a0
	mulu.w		8(sp),d0
	move.w		10(sp),d1
	move.l		d1,a1
	mulu.w		4(sp),d1
	add.w		d1,d0
	swap		d0
	clr.w		d0
	exg.l		a0,d0
	move.l		a1,d1
	mulu.w		d1,d0
	add.l		a0,d0
	rts

; --------------------------------------------------------------
___modsi3:		xdef	___modsi3
; --------------------------------------------------------------
	move.l		(sp)+,a0
	move.l		4(sp),d1
	bpl.s		.nabs
	neg.l		4(sp)
.nabs:	move.l		(sp),d0
	pea		.ret(pc)
	bpl.s		.nabsd
	neg.l		4(sp)
	subq.l		#2,(sp)
.nabsd:	bra		___udivsi3
	neg.l		d1
.ret:	move.l		d1,d0
	jmp		(a0)

; --------------------------------------------------------------
___udivsi3:		xdef	___udivsi3
; --------------------------------------------------------------
	move.l		d2,-(sp)
	move.l		12(sp),d0
	move.l		8(sp),d1
.norm:	cmpi.l		#$10000,d0
	bcs.s		.normd
	lsr.l		#1,d0
	lsr.l		#1,d1
	bra.s		.norm
.normd:	move.w		d1,d2
	clr.w		d1
	swap		d1
	divu.w		d0,d1
	movea.l		d1,a1
	move.w		d2,d1
	divu.w		d0,d1
	move.l		a1,d0
	swap		d0
	clr.w		d0
	andi.l		#$ffff,d1
	add.l		d1,d0
	move.l		12(sp),d2
	swap		d2
	move.l		d0,d1
	mulu.w		d2,d1
	movea.l		d1,a1
	swap		d2
	move.l		d0,d1
	swap		d1
	mulu.w		d2,d1
	add.l		a1,d1
	swap		d1
	clr.w		d1
	movea.l		d2,a1
	mulu.w		d0,d2
	add.l		d1,d2
	move.l		8(sp),d1
	sub.l		d2,d1
	bcc.s		.ninc
	subq.l		#1,d0
	add.l		a1,d1
.ninc:	move.l		(sp)+,d2
	rts

; --------------------------------------------------------------
___umodsi3:		xdef	___umodsi3
; --------------------------------------------------------------
	move.l		(sp)+,a0
	bsr		___udivsi3
	move.l		d1,d0
	jmp		(a0)

; --------------------------------------------------------------
___divsi3:		xdef	___divsi3
; --------------------------------------------------------------
	move.l		4(sp),d1
	bpl.s		.nabs1
	neg.l		4(sp)
.nabs1:	move.l		8(sp),d0
	bpl.s		.nabs2
	neg.l		8(sp)
.nabs2:	eor.l		d1,d0
	bpl.s		.npop
	move.l		(sp)+,a0
	pea		.ret(pc)
.npop:	bra		___udivsi3
.ret:	neg.l		d0
	jmp		(a0)
	
; --------------------------------------------------------------
_putchar:		xdef	_putchar
; --------------------------------------------------------------
	move.w		4+2(sp),d1
	movem.l		d2/a2,-(sp)
	move.w		d1,-(sp)
	move.w		#2,-(sp)
	move.w		#3,-(sp)
	trap		#13
	addq.l		#6,sp
	moveq		#0,d0
	movem.l		(sp)+,d2/a2
	rts
	
; --------------------------------------------------------------
	text
; --------------------------------------------------------------

_rand:	XDEF	_rand
___cxa_guard_acquire:
___cxa_guard_release:
	rts
		
; --------------------------------------------------------------
__ZSt17__throw_bad_allocv:
___cxa_pure_virtual:	
; --------------------------------------------------------------
	jmp		_exit

; --------------------------------------------------------------
	data
; --------------------------------------------------------------
	
dummy_argv:
dummy_envp:
	dc.b		0
	even

; --------------------------------------------------------------
	bss
; --------------------------------------------------------------
	
	ds.b		SSPS
new_ssp:
	ds.l		1
entrypoint_ssp:
	ds.l		1
	
; --------------------------------------------------------------

