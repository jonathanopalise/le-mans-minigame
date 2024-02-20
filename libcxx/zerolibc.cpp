//======================================================================================================================
//	ZeroLibC: dml/2017
//======================================================================================================================
//	Minimal libc support functions - can be used with TOS or ELF based GCC packages
//----------------------------------------------------------------------------------------------------------------------
//	Credits:
//	- Based on mStartup.cpp sourced from sqward/saulot as minimal C++ startup/crt
//	- Reworked into just libc parts unrelated to startup sequence
//	- File I/O added, some other changes
//	- Minor changes, additions, fixes for use as minimal libc for AGT
//----------------------------------------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------------------------------------
//	system headers

#include <mint/sysbind.h>

#include <memory.h>
#include <stdlib.h>
#include <stdio.h>

// ----------------------------------------------------------------------------------------

#pragma GCC diagnostic push
#pragma GCC diagnostic ignored "-Wunused-variable"
#pragma GCC diagnostic ignored "-Wunused-but-set-variable"

// ----------------------------------------------------------------------------------------

extern "C" {

// ----------------------------------------------------------------------------------------

void __assert_fail(const char * assertion, const char * file, unsigned int line, const char * function)
{
	puts("__assert_fail");
	//Pterm(-1);
}

// ----------------------------------------------------------------------------------------

void __main()
{
//	printf("___main()\n");
}

// ----------------------------------------------------------------------------------------

int tolower (int c) {
	if ('A' <= c && c <= 'Z') {
		c += 'a' - 'A';
	}
	return c;
}

int toupper (int c) {
	if ('a' <= c && c <= 'z') {
		c -= 'a' - 'A';
	}
	return c;
}

// ----------------------------------------------------------------------------------------

int puts( const char* pText )
{
	Cconws ( pText );
	Cconws ( "\r" );
	
	return 0;
}

// ----------------------------------------------------------------------------------------

size_t strlen ( const char * str )
{
	size_t len = 0;
	while ( *(char*)str ++ != 0 ) len++;
	return len;
}

// ----------------------------------------------------------------------------------------

int strcmp(const char *s1, const char *s2)
{
    while((*s1 && *s2) && (*s1++ == *s2++));
    return *(--s1) - *(--s2);
}

int strncmp(const char *s, const char *t, size_t n)
{
	int cc;
	if (n==0) return 0;
	do { cc = (*s++ - *t++); }
		while (!cc && s[-1] && --n>0);
	return cc;
}

// ----------------------------------------------------------------------------------------

void* memmove(void *destination, const void *source, size_t n)
{
	char* dest = (char*)destination;
	char* src = (char*)source;

    /* No need to do that thing. */
    if (dest == src)
        return destination;

    /* Check for destructive overlap.  */
    if (src < dest && dest < src + n) {
        /* Destructive overlap ... have to copy backwards.  */
        src += n;
        dest += n;
        while (n-- > 0)
            *--dest = *--src;
    } else {
        /* Do an ascending copy.  */
        while (n-- > 0)
            *dest++ = *src++;
    }
	
	return destination;
}

// ---------------------------------------------------------------------------------------------------------------------
// return 1 if character is a digit 0-9
// ---------------------------------------------------------------------------------------------------------------------

static __inline int _isdigit(int num)
{
	if (num >= '0' && num <= '9')
		return 1;

	return 0;
} // isdigit

// ---------------------------------------------------------------------------------------------------------------------
//	convert string to integer, until non-digit
// ---------------------------------------------------------------------------------------------------------------------

int atoi(const char *_string)
{
	int sign = 0;
	int r = 0;
	int n = 255;

	if ('-' == (*_string))
	{
		++_string;
		sign = -1;
	}

	while (_isdigit(*_string) && (n > 0))
	{
		r = (r * 10) + (*_string - '0');
		++_string;
		--n;
	}

	if (sign < 0)
		r = -r;

	return r;
} // atoi

// ----------------------------------------------------------------------------------------

void bcopy(const void *_src, void *_dest, size_t _len)
{
	char* src = (char*)_src;
	char* dest = (char*)_dest;

	if (dest < src)
	{
		while (_len--) 
		{	
			*dest++ = *src++;
		}
	}
	else
	{
		char *lasts = src + (_len-1);
		char *lastd = dest + (_len-1);

		while (_len--)
		{
			*(char *)lastd-- = *(char *)lasts--;
		}
	}
} // bcopy

// ----------------------------------------------------------------------------------------

int stricmp(const char *s, const char *t) 
{
	int cc;
	do { cc = tolower(*s++) - tolower(*t++); }
		while (!cc && s[-1]);
	return cc;
}

int strnicmp(const char *s, const char *t, size_t n) 
{
	int cc;
	if (n==0) return 0;
	do { cc = tolower(*s++) - tolower(*t++); }
		while (!cc && s[-1] && --n>0);
	return cc;
}

/* from http://clc-wiki.net/wiki/C_standard_library:string.h:strcpy */
char *strcpy(char *dest, const char* src)
{
    char *ret = dest;
    while (!!((*dest++) = (*src++)))
        ;
    return ret;
}

/* from http://clc-wiki.net/wiki/C_standard_library:string.h:strncpy */
char *strncpy(char *dest, const char *src, size_t n)
{
    char *ret = dest;
    do {
        if (!n--)
            return ret;
    } while (!!((*dest++) = (*src++)));
    while (n--)
        *dest++ = 0;
    return ret;
}

/* from http://clc-wiki.net/wiki/C_standard_library:string.h:strcat */
char *strcat(char *dest, const char *src)
{
    char *ret = dest;
    while (*dest)
        dest++;
    while (!!((*dest++) = (*src++)))
        ;
    return ret;
}


/* from http://clc-wiki.net/wiki/memcmp */
int memcmp(const void* s1, const void* s2,size_t n)
{
	const unsigned char *p1 = (const unsigned char *)s1, *p2 = (const unsigned char *)s2;
	while(n--)
		if( *p1 != *p2 )
			return *p1 - *p2;
		else
			p1++,p2++;
	return 0;
}

/* from http://clc-wiki.net/wiki/C_standard_library:string.h:memchr */
void *memchr(const void *s, int c, size_t n)
{
	unsigned char *p = (unsigned char*)s;
	while( n-- )
		if( *p != (unsigned char)c )
			p++;
		else
			return p;
	return 0;
}

/* from http://clc-wiki.net/wiki/strstr */
/* uses memcmp, strlen */
/* For 52 more bytes, an assembly optimized version is available in the .s file */
char *strstr(const char *s1, const char *s2)
{
	size_t n = strlen(s2);
	while(*s1)
		if(!memcmp(s1++,s2,n))
			return (char *) (s1-1);
	return (char *)0;
}

/* from http://clc-wiki.net/wiki/C_standard_library:string.h:strchr */
char *strchr(const char *s, int c)
{
	while (*s != (char)c)
		if (!*s++)
			return 0;
	return (char *)s;
}

/* from http://code.google.com/p/embox/source/browse/trunk/embox/src/lib/string/strlwr.c?spec=svn3211&r=3211 */
char *strlwr (char * string ) {
	char * cp;

	for (cp=string; *cp; ++cp) {
		if ('A' <= *cp && *cp <= 'Z') {
			*cp += 'a' - 'A';
		}
	}

	return(string);
}

char *strupr(char* _str)
{
	char *str = _str;
	if (str)
	while (*str)
	{
		*str = toupper(*str);
		str++;
	}
	return _str;
}

char *strdup(const char *s) 
{
	return strcpy((char*)malloc(strlen(s)+1), s);
}

/* Possible sources:
http://code.google.com/p/plan9front/source/browse/sys/src/libc/68000/?r=d992998a50139655fdb4cb7995e996219c6735d5
https://bitbucket.org/npe/nix/src/15af5ea53bed/src/9kron/libc/68000/
http://clc-wiki.net/wiki/C_standard_library
http://code.google.com/p/embox/source/browse/trunk/embox/src/lib/string/?r=3211
http://www.koders.com/noncode/fid355C9167E5496B5F863EAEB5758B4236711466D2.aspx
http://svn.opentom.org/opentom/trunk/linux-2.6/arch/m68knommu/lib/
*/

// ----------------------------------------------------------------------------------------

FILE * fopen(const char * filename, const char * mode)
{
	int h = 0;
	char c = 0;
	int update = 0, append = 0, read = 0, write = 0;
	short smode = 0;
	const char *pmode;

	pmode = mode;

	while (!!(c = (*pmode++)))
	{
		if (c == '+')
			update = 1;
		else 
		if (c == 'r')
			read = 1;
		else 
		if (c == 'w')
			write = 1;
		else 
		if (c == 'a')
			append = 1;
	}

	if (read && write)
		smode = S_READWRITE;
	else
	if (write)
		smode = S_WRITE;
	else
		smode = S_READ;

	if (smode == S_READ)
	{
		// file must exist
		h = (int)(short)Fopen(filename, smode);
	}
	else
	{
		if (append)
		{
			h = (int)(short)Fopen(filename, smode);
			if (h >= 0)
				Fseek(0, h, 2);
		}
		else
		{
			// create file
			h = (int)(short)Fcreate(filename, 0);
		}
	}

	if (h >= 0)
		return (FILE*)h;

	return (FILE*)NULL;
}

int fclose(FILE * stream)
{
	Fclose((short)(int)stream);
	return 0;
}

size_t fread(void * ptr, size_t size, size_t count, FILE * stream)
{
	int r;
	// todo: long multiplier
	r = Fread((short)(int)stream, size*count, ptr);
	if (r >= 0)
		return r;

	return 0;
}

size_t fwrite(const void * ptr, size_t size, size_t count, FILE * stream)
{
	int w;
	// todo: long multiplier
	w = Fwrite((short)(int)stream, size*count, ptr);
	if (w >= 0)
		return w;

	return 0;
}

int fseek(FILE * stream, long int offset, int origin)
{
	int pos;
	pos = Fseek(offset, (short)(int)stream, (origin==SEEK_SET) ? 0 : (origin==SEEK_CUR) ? 1 : 2);

	if (pos >= 0)
		return 0;

	return -1;
}

long int ftell(FILE * stream)
{
	int pos;
	pos = Fseek(0, (short)(int)stream, 1);

	if (pos >= 0)
		return pos;

	return -1;
}

int fgetc(FILE * stream)
{
	int r;
	char c = EOF;
	r = Fread((short)(int)stream, 1, &c);
	if (r > 0)
		return (int)c;

	return EOF;
}

// ----------------------------------------------------------------------------------------

}; //end extern "C"

// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// This is a dummy class to force a static initialisation and destruction lists
// to be built by gcc.

struct MicroStartupForceStaticCtorDtor
{
	int m_data;
	MicroStartupForceStaticCtorDtor()
	{
		m_data = 10;
	}
	~MicroStartupForceStaticCtorDtor()
	{
		//printf("static destruct %d\r\n", m_data);
		// dml: dtor must do something, otherwise GCC optimizer will prune the __DTOR_LIST__
		// and cause the link step to fail.
		m_data = 0;
	}
};

MicroStartupForceStaticCtorDtor ctordtor;

// ----------------------------------------------------------------------------------------
// This is required for basic std::list support 

/*
#include <list>

namespace std
{
	namespace __detail
	{
		void
		_List_node_base::_M_hook(std::__detail::_List_node_base* __position)
		{
		  this->_M_next = __position;
		  this->_M_prev = __position->_M_prev;
		  __position->_M_prev->_M_next = this;
		  __position->_M_prev = this;
		}
		
		void
		_List_node_base::_M_unhook()
		{
		_List_node_base* const __next_node = this->_M_next;
		_List_node_base* const __prev_node = this->_M_prev;
		__prev_node->_M_next = __next_node;
		__next_node->_M_prev = __prev_node;
		}
	}
}

*/

// ----------------------------------------------------------------------------------------

#pragma GCC diagnostic pop

// ----------------------------------------------------------------------------------------
