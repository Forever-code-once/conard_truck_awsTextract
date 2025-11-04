/* Copyright:       1995-2001 ALK Technologies, Inc. */

#include <windows.h>

#include <stdio.h>
#include <string.h>

#include "pcmsrv.h"


void main()
{

  PCMServerDLLHandler  *ServerHandler = new PCMServerDLLHandler;

  if ( ServerHandler->status() == 0 ) return;

  ServerHandler->TestTrip("Marlton, NJ", "Princeton, NJ");
  //ServerHandler->TestTrip("San Diego, CA", "Trenton, NJ");

  delete ServerHandler;
}

