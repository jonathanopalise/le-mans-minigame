//======================================================================================================================
//	BrownELF GCC startup: dml/2017
//======================================================================================================================
//	C shutdown code
//----------------------------------------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------------------------------------
//	system headers

#include <mint/sysbind.h>

//int debug_printf(const char *pFormat, ...);

// ---------------------------------------------------------------------------------------------------------------------
//	Our AGT headers

// ----------------------------------------------------------------------------------------

#define MAX_ATEXIT 32

// ----------------------------------------------------------------------------------------

#include <stdlib.h>
#include <stdio.h>

// ----------------------------------------------------------------------------------------

// this will always be NULL since we don't have shared objects in TOS
void * __dso_handle = NULL;

// prototype for the internal exit(code) function
void _exit(int return_code) __attribute__((noreturn));

typedef void (*aefuncp) (void);         // atexit function pointer
typedef void (*oefuncp) (int, void *);  // on_exit function pointer
typedef void (*cxaefuncp) (void *);     // __cxa_atexit function pointer

// exit function types
typedef enum {
    ef_free,
    ef_in_use,
    ef_on_exit,
    ef_cxa_atexit
} ef_type;

// record for exit function variants and states
typedef struct exit_function_s
{
	long int type; // enum ef_type
	union 
	{
		struct 
		{
			oefuncp func;
			void *arg;
		} on_exit;
		struct 
		{
			cxaefuncp func;
			void *arg;
			void* dso_handle;
		} cxa_atexit;
	} funcs;
} exit_function_t;

static short exit_count = 0;
static exit_function_t exit_funcs[MAX_ATEXIT];

// internal atexit() registration
int __cxa_atexit(cxaefuncp func, void * arg, void * dso_handle)
{
	if (func == NULL)
		return 0;

	//debug_printf("__cxa_atexit(func,arg,dso_handle)\n");

	short pos = exit_count++;

	if (pos >= MAX_ATEXIT) 
	{
		exit_count = MAX_ATEXIT;
		return -1;
	}

	exit_function_t *efp = &exit_funcs[pos];
	efp->funcs.cxa_atexit.func = func;
	efp->funcs.cxa_atexit.arg = arg;
	efp->funcs.cxa_atexit.dso_handle = dso_handle;
	efp->type = ef_cxa_atexit;

	return 0;
}

// finalize
__attribute__((optimize("O0")))
void __cxa_finalize(void * dso_handle)
{
	int exit_count_snapshot = exit_count;

	/* In reverse order */
	while ((--exit_count_snapshot) >= 0) 
	{
		exit_function_t *efp = &exit_funcs[exit_count_snapshot];

		if (efp->type == ef_cxa_atexit)
		{
			efp->type = ef_free;

			//debug_printf("call ef_cxa_atexit\n");

			/* glibc passes status (0) too, but that's not in the prototype */
			(*efp->funcs.cxa_atexit.func)(efp->funcs.cxa_atexit.arg);
		}
		else
		{
			//debug_printf("unknown atexit type!\n");
		}
	}
}

// user-facing atexit()
int atexit(aefuncp func)
{
	//debug_printf("atexit(func)\n");
	return __cxa_atexit((cxaefuncp)func, NULL, NULL);
}

// user-facing exit()
void exit(int return_code)
{
	//debug_printf("_exit(code)\n");

	__cxa_finalize(NULL);

	_exit(return_code);
}

// ----------------------------------------------------------------------------------------
