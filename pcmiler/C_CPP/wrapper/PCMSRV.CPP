/*
    File: PCMSRV.cpp

    Description:

	Loads the dll pcmsrv32.dll.

   Gets all the entry points for the dll functions.

   Contains examples of using some of the dll functions.

	 Copyright:       1995-2001 ALK Technologies, Inc.


*/

#include "pcmsrv.h"

#include <stdio.h>
#include <string.h>


PCMServerDLLHandler::PCMServerDLLHandler()  {

  Status = 0;
  //  Load the DLL

  if ( (PCMSRV32DLLInst = LoadLibrary("PCMSRV32.DLL")) == NULL ) return;

   // Attach the DLL functions.

  PCMSOpenServer =
    (PCMSOpenServer_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSOpenServer");

  PCMSCloseServer =
    (PCMSCloseServer_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSCloseServer");

  PCMSNewTrip =
    (PCMSNewTrip_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSNewTrip");

  PCMSDeleteTrip =
    (PCMSDeleteTrip_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSDeleteTrip");

  PCMSCalculate =
    (PCMSCalculate_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSCalculate");

  PCMSAddStop =
    (PCMSAddStop_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSAddStop");

  PCMSIsValid =
    (PCMSIsValid_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSIsValid");


    PCMSGetRpt =
    (PCMSGetRpt_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSGetRpt");

  PCMSNumRptBytes =
    (PCMSNumRptBytes_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSNumRptBytes");

  PCMSGetRptLine =
    (PCMSGetRptLine_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSGetRptLine");

  PCMSNumRptLines =
    (PCMSNumRptLines_Proc)GetProcAddress(PCMSRV32DLLInst, "PCMSNumRptLines");
    
  //  Check to see everything is OK
  if ( PCMSOpenServer == NULL || PCMSCloseServer == NULL ||
       PCMSNewTrip == NULL || PCMSDeleteTrip == NULL ||
       PCMSCalculate == NULL || PCMSAddStop == NULL ||
       PCMSIsValid == NULL ||
       PCMSGetRpt == NULL || PCMSNumRptBytes == NULL ||
       PCMSGetRptLine == NULL || PCMSNumRptLines == NULL  ) {
    return ;
  }

  ServerID  = PCMSOpenServer(NULL, NULL);

  Status = 1;
}


PCMServerDLLHandler::~PCMServerDLLHandler()  {

  PCMSCloseServer(ServerID);

  if ( PCMSRV32DLLInst != NULL ) FreeLibrary(PCMSRV32DLLInst);
}


int PCMServerDLLHandler::status()  {

  return ( Status ? (int)PCMSIsValid(ServerID) : 0 );
}


void  PCMServerDLLHandler::TestTrip (char *origin, char *dest)
{
  
	FILE *fp = stdout;

	if ( !PCMSIsValid(ServerID) )
	{
	    fprintf(stdout, "Invalid serverID\n");

		return;
	}
	
	Trip  trip = PCMSNewTrip(ServerID);

	if ( !trip ) 
	{
	    return;
	}
	
    int ret;
    ret = PCMSAddStop(trip, origin);
    ret = PCMSAddStop(trip, dest);
    long dist = PCMSCalculate(trip);

	  
	fprintf(fp, "Generating Detailed report:Origin: %s Destination: %s\n",
            origin, dest);

	char buf[256];;

	for ( int i = 0; i < PCMSNumRptLines(trip, RPT_DETAIL); i++ ) {

		PCMSGetRptLine(trip, RPT_DETAIL, i, buf, 256);
		fprintf(fp, "%s\n", buf);
  }

  PCMSDeleteTrip(trip);

}