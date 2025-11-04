/**************************************************************************
* File:            PCMSINIT.H
* Revision Number: 17.0
* Copyright:       1995-2003 ALK Technologies, Inc.
* Purpose:         PC*MILER/Connect DLL initialization header file.

***************************************************************************/

#ifndef __PCMSINIT_H
#define __PCMSINIT_H

/**************************************************************************
* Compilation: Define the macro _PCMSFN for client vs. server
**************************************************************************/
#define  _PCMSFN far _stdcall


#ifndef __WINDOWS_H
#include <windows.h>
#endif


/**************************************************************************
* Make sure everything is C callable.
**************************************************************************/
#ifdef __cplusplus
extern "C" {
#endif

/**************************************************************************
* Define the type of the server ID: a windows handle in local memory.
* WARNING! This is a 2 byte value! Use Integers in Visual Basic!
**************************************************************************/
typedef short  PCMServerID;

/**************************************************************************
* Current debugging level:
*	0 = no debugging
*	1 = some debugging (currently not used)
*	2 = lots of message boxes about the state of the DLL.
* Returns previous debug level.
**************************************************************************/
int _PCMSFN PCMSSetDebug(int levl);

/**************************************************************************
* Return current debugging level:
*	0 = no debugging
*	1 = some debugging (currently not used)
*	2 = lots of message boxes about the state of the DLL.
**************************************************************************/
int _PCMSFN PCMSGetDebug();

/**************************************************************************
* Initialize the server. The arguments are currently not used. In future
* versions they will be used to find resources and define parent windows.
*	hAppInst - the calling application's instance handle.  May be NULL.
*	hWnd - the parent window's window handle.  May be NULL.
* Returns a unique ID for this server connection.
**************************************************************************/
PCMServerID _PCMSFN PCMSOpenServer(HINSTANCE hAppInst, HWND hWnd);

/**************************************************************************
* Clean up and dismiss the server:
*	server - the server's unique ID.  Must be passed in to free resources.
**************************************************************************/
int _PCMSFN PCMSCloseServer(PCMServerID server);

/**************************************************************************
* Check that server is a valid handle. I.e. initialized OK, and in client list.
**************************************************************************/
int _PCMSFN PCMSIsValid(PCMServerID server);

/**************************************************************************
* Look up a specified error string. Returns number of characters copied into
* buffer, -1 if the string is not found, 0 if buffer arg or size are not OK.
**************************************************************************/
int _PCMSFN PCMSGetErrorString(int errorCode, char *buffer, int bufSize);

/**************************************************************************
* Return the error code of the last error the server encountered.
* See the error codes in PCM_DEFS.H for their values.
**************************************************************************/
int _PCMSFN PCMSGetError(void);

/**************************************************************************
* Return a string from the DLL's version resource.
* Returns length of string on success, -1 on error, 0 if string not found.
* See the Windows documentation on VERSIONINFO resources for valid values.
**************************************************************************/
int _PCMSFN PCMSAbout(const char FAR *which, char FAR *buffer, int bufSize);

/**************************************************************************
* Return the length of a NULL terminated 'C' string to Basic users.
**************************************************************************/
int _PCMSFN PCMSStrLen(char FAR *str);


/**************************************************************************
* Set/get the default region to the given regionID
* Functions return zero on succes, -1 on error.
**************************************************************************/
int _PCMSFN PCMSSetDefaultRegion(char FAR *regionId);
int _PCMSFN PCMSGetDefaultRegion(short bufSize, char FAR *regionId);

#ifdef __cplusplus
};
#endif

#endif	/* __PCMSINIT_H */

