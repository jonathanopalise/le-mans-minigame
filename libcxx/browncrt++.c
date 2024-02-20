//======================================================================================================================
//	BrownELF GCC startup: dml/2017
//======================================================================================================================
//	C++ startup/shutdown code including ctor/dtor,preinit/init/fini,atexit handling
//----------------------------------------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------------------------------------
//	system headers

#include <mint/sysbind.h>

// ----------------------------------------------------------------------------------------

#include <stdlib.h>
#include <stdio.h>

//int debug_printf(const char *pFormat, ...);

// ----------------------------------------------------------------------------------------

// references to GCC6 new-style ctor/dtor lists
//
// CAUTION: 
// while the .init_array does contain the constructor list, the .fini_array doesn't
// necessarily contain the list of destructors. \o/
// the destructors seem to be registered secretly by GCC via __cxa_atexit(pdtor,pobj)
// and will be executed during __cxa_finalize(). this is a bit wild and confusing, but
// that's how things really are in gnu land...
//
// Quote:
//	"The .ctors/.dtors sections are still relevant. They are slowy being superseded by 
//	.init_array/.fini_array however (on systems which support it) because .init_array 
//	sorts ascending and not descending.
//	In practice, the .dtors section is normally empty now (unless a function is otherwise
//	made a destructor by use of function attributes in GCC) because inside every global
//	and local static C++ .ctor a call to __cxa_atexit (or atexit on some non-compliant
//	targets) is made to register it's destructor.
//	So an OS must still call constructors in the .ctors list (or .init_array depending
//	on your system) and must implement __cxa_atexit and __cxa_finalize."

extern void (*__preinit_array_start []) (int argc, char **argv, char **envp) __attribute__((weak));
extern void (*__preinit_array_end []) (int argc, char **argv, char **envp) __attribute__((weak));
extern void (*__init_array_start []) (int argc, char **argv, char **envp) __attribute__((weak));
extern void (*__init_array_end []) (int argc, char **argv, char **envp) __attribute__((weak));
extern void (*__fini_array_start []) (void) __attribute__((weak));
extern void (*__fini_array_end []) (void) __attribute__((weak));

typedef void (*aefuncp) (void);         // atexit function pointer

int atexit(aefuncp func);

// execute the .fini_array, if relevant (but typically *not* destructors)
void __libc_csu_fini(void)
{
	size_t i = __fini_array_end - __fini_array_start;
	//debug_printf("__libc_csu_fini: fini count: %d\n", i);
	while (i-- > 0)
		(__fini_array_start[i])();
}

// execute the .init_array, if relevant (including constructors)
void __libc_csu_init(int argc, char **argv, char **envp)
{
	{
		const size_t size = __preinit_array_end - __preinit_array_start;
		//debug_printf("__libc_csu_init: preinit count: %d\n", size);
		size_t i;
		for (i = 0; i < size; i++)
			(__preinit_array_start[i])(argc, argv, envp);
	}

	{
		const size_t size = __init_array_end - __init_array_start;
		//debug_printf("__libc_csu_init: init count: %d\n", size);
		for (size_t i = 0; i < size; i++)
			(__init_array_start[i])(argc, argv, envp);
	}

	// register finalize/cleanup
	atexit(&__libc_csu_fini);
}

// ----------------------------------------------------------------------------------------
