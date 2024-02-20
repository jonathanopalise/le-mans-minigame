/*
 * Crtinit: C run-time initialization code.
 * Written by Eric R. Smith, and placed in the public domain.
 * Use at your own risk.
 *
 * 01/03/89 ++jrb
 *	The (new) meaning of _stksize: (thanks to allan pratt for the feedback)
 *
 *	_stksize			meaning
 *	  -1L	    keep all of memory (except MINFREE at top) and do
 *		    mallocs from own heap, with heap grown upwards towards
 *		    stack, and the stack growing down towards heap,
 *		    with a minimum slush between them so that they
 *		    dont meet (only checked while malloc'ing). With
 *		    this model, further spawning is not possible, but it is
 *		    well suited for programs such as gcc-cc1 etc.
 *		    Thanks to Piet van Oostrum & Atze Dijkstra for this idea
 *
 *	0L	    keep minimum amount of memory. this is also the
 *		    case when _stksize is undefined by the user.
 *	1L	    keep 1/4 of memory, free 3/4 ( as in Alcyon GEMSTART)
 *	2L	    keep 2/4 (1/2), free rest
 *	3L	    keep 3/4, free 1/4
 *	other	    keep that many bytes
 *	-other	    keep |other| bytes, use the heap for mallocs
 *
 * 02/14/90 ++jrb (thanks edgar)
 *	auto acc detect
 *	undump friendly
 *
 * NOTE: dumping applications should use _initial_stack instead: if
 *	 !=0, then _stksize is initialized from _initial_stack, and
 *	 mallocs are always from internal heap. (TeX works much better now),
 *	 thanks edgar!
 *
 * Acc convention:
 *      Preferred: user sets _stksize, then _heapbase is set to Malloc(_stksize)
 *      sp to _heapbase + _stksize and all mallocs happen from heap
 *
 *      Old way:
 *	user sets _heapbase to bottom of stack + heap area
 *	     sets _stksize to the size of this area
 *	     at startup, sp will be set to top of this area
 *	     (_heapbase  + _stksize ) and malloc()'s will happen from heap.
 *		(note malloc() and *not* Malloc())
 *
 * 02/16/90 ++jrb
 *  - bug fix: dont get screwed by desktop launch when fast bit is set
 *             convert env string to format usable
 *		(atari get your act together!!)
 *
 * 19 Jan 93 hohmuth
 *	new variable _PgmSize, holds size of program area
 *	(useful when doing Ptermres)
 *
 * 08 Apr 93 hohmuth
 *	added support for ARGV standard extension allowing empty arguments
 *
 * dml 2017
 *	
 *	added call to __libc_csu_init for C++ startup
 */

/* define this symbol to get ARGV argument passing that's strictly
 * compatible with the Atari standard. If it's not defined, then
 * the startup code won't validate the ARGV= variable by checking
 * the command byte for 0x127. Note that there are still some
 * applications (gulam is a notable example) that implement only
 * part of the standard and don't set the command byte to 0x127.
 */
#if 1
#define STRICTLY_COMPATIBLE_WITH_STANDARD
#endif
#define _GNU_SOURCE

#include <mint/basepage.h>
#include <mint/osbind.h>
#include <support.h>
#include <string.h>
#include "lib.h"
//#include "stksiz.h"

/* definitions needed in mintlib/crtinit.c and mintlib/stksize.c */

#define MINFREE	(8L * 1024L)		/* free at least this much mem on top */
#define MINKEEP (64L * 1024L)		/* keep at least this much mem on stack */

void __libc_csu_init(int argc, char **argv, char **envp);

#define isspace(c) ((c) == ' '||(c) == '\t')
#define isdigit(c) ((c) >= '0' && (c) <= '9')

static long parseargs(BASEPAGE *);

/*
 * accessories start here:
 */
static char *acc_argv[] = { "", NULL }; /* no name and no arguments */

void _acc_main(void)
{
        char *s;

	if (_stksize == 0 || _stksize == -1L)
		_stksize = MINKEEP;

	if (_stksize < 0)
		_stksize = -_stksize;

	if ((s = getenv("STACKSIZE")) != 0)
		_stksize = atoi(s);

	/* stack on word boundary */
	_stksize &= 0xfffffffeL;

	if (_heapbase == 0) {
		_heapbase = (void *)Malloc(_stksize);
	}
	_setstack((char *) _heapbase + _stksize);

	/* this is an accessory */
	_app = 0;

	_main(1L, acc_argv, acc_argv);
	/*NOTREACHED*/
}

void _crtinit(void)
{
	extern void etext();	/* a "function" to fake out pc-rel addressing */

	register BASEPAGE *bp;
	register long m;
	register long freemem;
	char *s;

	/* its an application */
	_app = 1;

	bp = _base;

	/* m = # bytes used by environment + args */
	m = parseargs(bp);

	/* make m the total number of bytes required by program sans stack/heap */
	m += (bp->p_tlen + bp->p_dlen + bp->p_blen + sizeof(BASEPAGE));
	m = (m + 3L) & (~3L);

	/* freemem the amount of free mem accounting for MINFREE at top */
	if ((freemem = (long)bp->p_hitpa - (long)bp - MINFREE - m) <= 0L)
	    goto notenough;
	
	if (_initial_stack) {
	    /* the primary use of _initial_stack will be in dumping */
	    /* applications where only a heap for malloc makes sense */
	    _heapbase = (void *)((long)bp + m);
	    _stksize = _initial_stack;
	} else {
		if (_stksize >= -1L)
			/* malloc from Malloc first, then from own heap */
			_split_mem = 1;
	}

	if (_stksize == -1L) {
		_stksize = freemem;
		_heapbase = (void *)((long)bp + m);
	} else if (_stksize == 0L) {	/* free all but MINKEEP */
		_stksize = MINKEEP;
	} else if (_stksize == 1L) { 	/* keep 1/4, free 3/4 */
		_stksize = freemem >> 2;
	} else if (_stksize ==  2L) {	/* keep 1/2, free 1/2 */
		_stksize = freemem >> 1;
	} else if (_stksize == 3L) {	/* keep 3/4, free 1/4 */
		_stksize = freemem - (freemem >> 2); 
	} else {
		if(_stksize < -1L) {	/* keep |_stksize|, use heap for mallocs */
			_stksize = -_stksize;
			_heapbase = (void *)((long)bp + m);
		}
	}

	if ((s = getenv("STACKSIZE")) != 0)
		_stksize = atoi(s);

	/* make m the total number of bytes including stack */
	_stksize = _stksize & (~3L);
	m += _stksize;

	/* make sure there's enough room for the stack */
	if (((long)bp + m) > ((long)bp->p_hitpa - MINFREE))
	    goto notenough;

	/* set up the new stack to bp + m  */
	_setstack((char *)bp + m);

	/* shrink the TPA */
	(void)Mshrink(bp, m);

	/* keep length of program area */
	_PgmSize = m;

	/* establish handlers,  call the main routine */
	_init_signal();

	/* start profiling, if we were linked with gcrt0.o */
	_monstartup((void *)bp->p_tbase, (void *)((long)etext - 1));

	/* needed for c++ static inits using ELF GCC6 */
	__libc_csu_init(__libc_argc, __libc_argv, environ);

	_main(__libc_argc, __libc_argv, environ);
	/* not reached normally */

notenough:
	(void) Cconws("Fatal error: insufficient memory\r\n");
	(void) Cconws("Hint: either decrease stack size using 'stack' command (not recomended)\r\n" \
		   "      or increase TPA_INITIALMEM value in mint.cnf.\r\n");
	Pterm(-1);
}

/*
 * parseargs(bp): parse the environment and arguments pointed to by the
 * basepage. Return the number of bytes of environment and arguments
 * that have been appended to the bss area (the environ and argv arrays
 * are put here, as is a temporary buffer for the command line, if
 * necessary).
 *
 * The MWC extended argument passing scheme is assumed.
 *
 */
static long parseargs(BASEPAGE *bp)
{
	long count = 4;		/* compensate for aligning */
	long  i;
	char *from, *cmdln, *to;
	char **envp, **arg;
	char *null_list = 0;
	/* flag to indicate desktop-style arg. passing */
	long desktoparg;

	/* handle the environment first */

	environ = envp = (char **)(( (long)bp->p_bbase + bp->p_blen + 4) & (~3));
	from = bp->p_env;
	while (*from) {

/* if we find MWC arguments, tie off environment here */
		if (*from == 'A' && *(from+1) == 'R' && *(from+2) == 'G' &&
		    *(from+3) == 'V' && *(from+4) == '=')
		{
			*envp++ = (char *) 0; count += 4;
			*from = 0;
#ifdef STRICTLY_COMPATIBLE_WITH_STANDARD
			if (bp->p_cmdlin[0] != 127)
				goto old_cmdlin;
#endif
			from += 5;        /* skip ARGV= string */

			/* find list of empty params
			 */
			if (*from == 'N' && *(from+1) == 'U'
			    && *(from+2) == 'L' && *(from+3) == 'L' 
			    && *(from+4) == ':')
			{
				null_list = from + 5;
			}
			    
			while (*from++) ; /* skip ARGV= value */
			__libc_argv = arg = envp;
			*arg++ = from; count+= 4;
			while (*from++) ; /* skip __libc_argv[0] */
			goto do_argc;
		}
		*envp++ = from;
		count += 4;
		desktoparg = 1;
		while (*from) {
			if (*from == '=') {
				desktoparg = 0;
			}
			from++;
		}
		from++;		/* skip 0 */

/* the desktop (and some shells) use the environment in the wrong
   way, putting in "PATH=\0C:\0" instead of "PATH=C:". so if we
   find an "environment variable" without an '=' in it, we
   see if the last environment variable ended with '=\0', and
   if so we append this one to the last one
 */
		if(desktoparg && envp > &environ[1]) 
		{
		/* launched from desktop -- fix up env */
		    char *p, *q;

		    q = envp[-2];	/* current one is envp[-1] */
		    while (*q) q++;
		    if (q[-1] == '=') {
			p = *--envp;
			while(*p)
			   *q++ = *p++;
		        *q = '\0';
		   }
		}
	}
	*envp++ = (char *)0;
	count += 4;

#ifdef STRICTLY_COMPATIBLE_WITH_STANDARD
old_cmdlin:
#endif
/* Allocate some room for the command line to be parsed */
	cmdln = bp->p_cmdlin;
	i = *cmdln++;
	from = to = (char *) envp;
	if (i > 0) {
		count += (i&(~3));
		envp = (char **) ( ((long) envp)  + (i&(~3)) );
	}
	envp += 2; count += 8;

/* Now parse the command line and put __libc_argv after the environment */

	__libc_argv = arg = envp;
	*arg++ = "";		/* __libc_argv[0] not available */
	count += 4;
	while(i > 0 && isspace(*cmdln) )
		cmdln++,--i;

	/* 
	 * MagXDesk only uses ARGV if the arg is longer than the 126 character
	 * of bp->cmdlin. If the arg is short enough and contains a file name with
	 * blanks it will be come quoted via bp->cmdlin!!
	*/ 
	if (cmdln[0] != '\'')
	{
		while (i > 0) {
			if (isspace(*cmdln)) {
				--i; cmdln++;
				while (i > 0 && isspace(*cmdln))
					--i,cmdln++;
				*to++ = 0;
			}
			else {
				if ((*to++ = *cmdln++) == 0) break;
				--i;
			}
		}
	}
	else
	{
		int in_quote = 0;
		
		while (i > 0) 
		{
			if (*cmdln == '\'')
			{
				i--; 
				cmdln++;
				if (in_quote)
				{
					if (*cmdln == '\'')		/* double ': file name contains ' */
					{
						*to++ = *cmdln++;
						i--;
					}
					else
					{
						in_quote = 0;
						*to++ = 0;				/* end of quoted arg */
						i--; cmdln++;
					}
				}
				else
					in_quote = 1;
			}
			else
			{
				if (*cmdln == ' ')
				{
					if (in_quote)
					{
						*to++ = *cmdln++;
						i--;
					}
					else
					{
						--i; cmdln++;
						*to++ = 0;
					}
				}
				else
				{
					*to++ = *cmdln++;
					i--;
				}
			}
		}
	}

	*to++ = '\0';
	*to = '\0'; /* bug fix example:cmdln == '\3' 'a' ' ' 'b' '\0' */
	/* the loop below expects \0\0 at end to terminate! */
	/* the byte @ cmdln[i+2] != 0 when fast bit is set */
do_argc:
	/* Find out the name we have been invoked with.  */
 	if (__libc_argv[0] != 0 && __libc_argv[0][0] != '\0') {
 	  char* p;
 	  program_invocation_name = __libc_argv[0];
 	  p = strrchr (program_invocation_name, '\\');
 	  if (p == 0)
 	    p = strrchr (program_invocation_name, '/');
 	  if (p != 0)
 	    p++;
 	  program_invocation_short_name = p == 0 ? 
 	      program_invocation_name : p;
 	}
 	
	__libc_argc = 1;		/* at this point __libc_argv[0] is done */
	while (*from) {
		*arg++ = from;
		__libc_argc++;
		count += 4;
		while(*from++) ;
	}
	*arg = (char *) 0;

	/* zero epmty params
	 */
	if (null_list) {
		char *s;
		long idx;

		while (*null_list) {
			s = null_list;
			
			while (* ++null_list) {	/* find ',' or '\0' */
				if (*null_list == ',') {
					*null_list++ = 0;
					break;
				}
			}
			
			idx = 0;
			for (;;) {
				if (! isdigit(*s))
					goto bail_out;
					
				/* don't feed this to strtol(),
				 * do the ascii -> long conversion by 
				 * hand for efficency
				 */
				idx += *s++ - '0';
				if (*s)
					idx = (idx << 3) + (idx << 1);
				else
					break;
			}
		
			if (idx < __libc_argc)
				*(__libc_argv[idx]) = 0;
			else
				goto bail_out;
		}
	}

bail_out:
	return count+4;
}
