/*
 * natfeat.c - NatFeats API examples
 *
 * Copyright (c) 2014 by Eero Tamminen
 * 
 * NF initialization & calling is based on EmuTOS code,
 * Copyright (c) 2001-2003 The EmuTOS development team
 * 
 * This file is distributed under the GPL, version 2 or at your
 * option any later version.  See doc/license.txt for details.
 */

#if __GNUC__
# include <mint/osbind.h>
#else	/* VBCC/AHCC/Pure-C */
# include <tos.h>
#endif
#include "natfeats.h"


/* NatFeats available & initialized */
static int nf_ok;

/* handles for NF features that may be used more frequently */
static long nfid_print, nfid_debugger, nfid_fastforward;

char nf_strbuf[256];

/* API documentation is in natfeats.h header */

int nf_init(void)
{
	//void *sup = (void*)Super(0);
	nf_ok = detect_nf();
	//Super(sup);

	if (nf_ok) {
		/* initialize commonly used handles */
		nfid_print = nf_id("NF_STDERR");
		nfid_debugger = nf_id("NF_DEBUGGER");
		nfid_fastforward = nf_id("NF_FASTFORWARD");
	} else {
		Cconws("Native Features initialization failed!\r\n");
	}
	return nf_ok;
}

long nf_print(const char *text)
{
	if (nfid_print) {
		return nf_call(nfid_print, text);
	} else {
		Cconws("NF_STDERR unavailable!\r\n");
		return 0;
	}
}

