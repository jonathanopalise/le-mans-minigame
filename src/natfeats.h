/*
 * natfeats.h - NatFeats API header file
 *
 * Copyright (c) 2014 by Eero Tamminen
 *
 * This file is distributed under the GPL, version 2 or at your
 * option any later version.  See doc/license.txt for details.
 */

#ifndef _NATFEAT_H
#define _NATFEAT_H

/* AHCC uses registers to pass arguments, but
 * NatFeats calls expect arguments to be in stack.
 * "cdecl" can be used to declare that arguments
 * should be passed in stack.
 */
#if __AHCC__
#define CDECL cdecl
#else
#define CDECL
#endif

/* nf_asm.s ASM helper interface for natfeats.c */
long CDECL nf_id(const char *);
long CDECL nf_call(long ID, ...);
/* call only from Supervisor mode */
int CDECL detect_nf(void);

extern char nf_strbuf[256];

/* natfeats.c public prototypes */

/**
 * detect & initialize native features
 * returns zero for fail
 */
int nf_init(void);

/**
 * print string to emulator console
 * returns number of chars output
 */
long nf_print(const char *text);

#endif /* _NATFEAT_H */
