/*
    File: PCMSRV.h

    Description:

    Listing of Typedef's for the dll functions.
    It is used to simplify casting the retrun value from GetProcAddress.

	 Copyright:       1995-2001 ALK Technologies, Inc.


*/

#ifndef PCMSRV_H
#define PCMSRV_H

#include <windows.h>
#include <stdio.h>
#include "pcmsdefs.h"


extern "C"  {


//  *************************************************************************
//  From pcmsinit.h
//  *************************************************************************

typedef short PCMServerID;

typedef int
  (__stdcall *PCMSSetDebug_Proc)(int levl);

typedef int
  (__stdcall *PCMSGetDebug_Proc)();

typedef PCMServerID
  (__stdcall *PCMSOpenServer_Proc)(HINSTANCE hAppInst, HWND hWnd);

typedef int
  (__stdcall *PCMSCloseServer_Proc)(PCMServerID server);

typedef int
  (__stdcall *PCMSIsValid_Proc)(PCMServerID server);

typedef int
  (__stdcall *PCMSGetErrorString_Proc)(int errorCode,
                                       char *buffer,
                                       int bufSize);

typedef int
  (__stdcall *PCMSGetError_Proc)();

typedef int
  (__stdcall *PCMSAbout_Proc)(const char* which,
                              char *buffer, int bufSize);

typedef int
  (__stdcall *PCMSStrLen_Proc)(char *str);

typedef int
  (__stdcall *PCMSSetDefaultRegion_Proc)(char *regionId);

typedef int
  (__stdcall *PCMSGetDefaultRegion_Proc)(short bufSize, char *regionID); 

//  *************************************************************************
//  From pcmstrip.h
//  *************************************************************************

typedef long Trip;

//  SIMPLE INTERFACE
typedef long
  (__stdcall *PCMSCalcDistance_Proc)(PCMServerID serv,
                                     const char *orig,
	                             const char *dest);

typedef long
  (__stdcall *PCMSCalcDistance2_Proc)(PCMServerID serv,
                                      const char *orig,
	                              const char *dest,
                                      int routeType);

typedef long
  (__stdcall *PCMSCalcDistance3_Proc)(PCMServerID serv,
                                      const char *orig,
	                              const char *dest,
                                      int routeType,
                                      long *minutes);

typedef int
  (__stdcall *PCMSCheckPlaceName_Proc)(PCMServerID serv,
                                       const char *cityZip);

typedef int
  (__stdcall *PCMSCityToLatLong_Proc)(PCMServerID serv,
                                      const char *name,
                                      char *buffer, int bufSize);

typedef int
  (__stdcall *PCMSLatLongToCity_Proc)(PCMServerID serv, 
									  const char *latlong,
									  char *buffer, int bufSize);

//  COMPLEX INTERFACE
typedef int
  (__stdcall *PCMSNumRegions_Proc)(PCMServerID serv);

typedef int
  (__stdcall *PCMSGetRegionName_Proc)(PCMServerID serv, int idx,
									  char *name, int bufSize);

typedef Trip
  (__stdcall *PCMSNewTrip_Proc)(PCMServerID serv);

typedef Trip
  (__stdcall *PCMSNewTripWithRegions_Proc)(PCMServerID serv,
										   const char *regionID);

typedef void
  (__stdcall *PCMSDeleteTrip_Proc)(Trip trip);

typedef long
  (_stdcall *PCMSCalcTrip_Proc)(Trip trip,
                                const char *orig, const char *dest);

typedef long
  (__stdcall *PCMSCalculate_Proc)(Trip trip);

typedef int
  (__stdcall *PCMSOptimize_Proc)(Trip trip);

typedef long
  (__stdcall *PCMSGetDuration_Proc)(Trip trip);

typedef int
  (__stdcall *PCMSAddStop_Proc)(Trip trip, const char *stop);

typedef int
  (__stdcall *PCMSDeleteStop_Proc)(Trip trip, int which);

typedef void
  (__stdcall *PCMSClearStops_Proc)(Trip trip);

typedef int
  (__stdcall *PCMSGetStop_Proc)(Trip trip, int which,
                                char *buffer, int bufSize);

typedef int
  (__stdcall *PCMSNumStops_Proc)(Trip trip);

typedef int
  (__stdcall *PCMSGetStopType_Proc)(Trip trip, int which, int *type);

typedef int
  (__stdcall *PCMSSetLoaded_Proc)(Trip trip, int which, BOOL loaded);

typedef int
  (__stdcall *PCMSLookup_Proc)(Trip trip, const char *city, int easyMatch);

typedef int
  (__stdcall *PCMSGetMatch_Proc)(Trip trip, int which,
                                 char *buffer, int bufSize);

typedef int
  (__stdcall *PCMSGetFmtMatch_Proc)(Trip trip, int which,
                                 char *buffer, int bufSize,
                                 int zipLen, int cityLen, int countyLen);

typedef int
  (__stdcall *PCMSNumMatches_Proc)(Trip trip);

//  OPTIONS
typedef void
  (__stdcall *PCMSSetCalcType_Proc)(Trip trip, int routeType);

typedef int
  (__stdcall *PCMSGetCalcType_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetBordersOpen_Proc)(Trip trip, BOOL open);

typedef void
  (__stdcall *PCMSSetShowFerryMiles_Proc)(Trip trip, BOOL onOff);

typedef void
  (__stdcall *PCMSSetKilometers_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetMiles_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetHubMode_Proc)(Trip trip, BOOL onOff);

typedef void
  (__stdcall *PCMSSetCost_Proc)(Trip trip, int cost);

typedef int
  (__stdcall *PCMSGetCost_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetResequence_Proc)(Trip trip, BOOL changeDest);

typedef void
  (__stdcall *PCMSSetOptions_Proc)(Trip trip, long opts);

typedef long
  (__stdcall *PCMSGetOptions_Proc)(Trip trip);

typedef int
  (__stdcall *PCMSNumLegs_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSDefaults_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetBreakHours_Proc)(Trip trip, long hours);

typedef long
  (__stdcall *PCMSGetBreakHours_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetBreakWaitHours_Proc)(Trip trip, long hours);

typedef long
  (__stdcall *PCMSGetBreakWaitHours_Proc)(Trip trip);

typedef void
  (__stdcall *PCMSSetOnRoad_Proc)(Trip trip, BOOL onOff);

//REPORT DATA
typedef int
  (__stdcall *PCMSGetRpt_Proc)(Trip trip, int rptNum,
                               char *buffer, int bufSize);

typedef int
  (__stdcall *PCMSGetRptLine_Proc)(Trip trip, int rptNum, 
                                   int lineNum, char *buffer,
								   int bufSize);

typedef int
  (__stdcall *PCMSNumRptLines_Proc)(Trip trip, int rptNum);

typedef long
  (__stdcall *PCMSNumRptBytes_Proc)(Trip trip, int rptNum);

typedef long
  (__stdcall *PCMSGetHTMLRpt_Proc)(Trip trip, int rptNum,
                                   char *buffer, long bufSize);


typedef long
  (__stdcall *PCMSNumHTMLRptBytes_Proc)(Trip trip, int rptNum);

typedef int
  (__stdcall *PCMSGetNumSegments_Proc)(Trip trip);

typedef int
  (__stdcall PCMSGetSegment_Proc)(Trip trip, int segNum,
								  struct segmentStruct *aSegment);

typedef int
  (__stdcall PCMSGetLegInfo_Proc)(Trip trip, int legNum,
                                  struct legInfoType *pLegInfo);

typedef void
  (__stdcall *PCMSSetCustomMode_Proc)(Trip trip, BOOL onOff);

typedef void
  (__stdcall *PCMSSetAlphaOrder_Proc)(Trip trip, BOOL alphaOrder);

typedef int
  (__stdcall *PCMSAFLinks_Proc)(Trip trip, BOOL favor);

typedef int
  (__stdcall *PCMSAFLoad_Proc)(PCMServerID serv, char *filename);

typedef int
  (__stdcall *PCMSAFSave_Proc)(PCMServerID serv);

typedef int
  (__stdcall *PCMSAFLoadForRegion_Proc)(PCMServerID serv, char *filename,
                                        const char* regionID);

typedef int
  (__stdcall *PCMSAFSaveForRegion_Proc)(PCMServerID, const char* regionID);

typedef int
  (__stdcall *PCMSCalcDistToRoute_Proc)(Trip trip, char *location);


typedef int
  (__stdcall *PCMSGetLocAtMiles_Proc)(Trip trip, long miles,
                                      char *pLocation, int size);

typedef int
  (__stdcall *PCMSGetLocAtMinutes_Proc)(Trip trip, long minutes,
                                      char *pLocation, int size);

typedef int
  (__stdcall *PCMSLatLongAtMiles_Proc)(Trip trip, long miles, char *latlong, BOOL useShpPts);


typedef int
  (__stdcall *PCMSLatLongAtMinutes_Proc)(Trip trip, long minutes, char *latlong, BOOL useShpPts);


typedef long
  (__stdcall *PCMSLatLongsEnRoute_Proc)(Trip trip, double* latlong, long numPairs, BOOL shpPts);


typedef void
  (__stdcall *PCMSSetHazOption_Proc)(Trip trip, int hazType);


};  // extern "C"


//  *************************************************************************
//  PCMServerDLLHandler specification
//  *************************************************************************

class PCMServerDLLHandler {
  public:
    PCMServerDLLHandler();
    ~PCMServerDLLHandler();

    int                     status();

    //void                    simpleRoute(char*, char*, char**&, int&);

    void                    TestTrip(char*, char*);
  protected:
    PCMServerID             ServerID;

    HINSTANCE               PCMSRV32DLLInst;

  protected:

    int                     Status;

    PCMSOpenServer_Proc     PCMSOpenServer;

    PCMSCloseServer_Proc    PCMSCloseServer;

    PCMSNewTrip_Proc        PCMSNewTrip;

    PCMSDeleteTrip_Proc     PCMSDeleteTrip;

    PCMSCalculate_Proc      PCMSCalculate;

    PCMSAddStop_Proc        PCMSAddStop;

    PCMSIsValid_Proc        PCMSIsValid;

    PCMSGetRpt_Proc         PCMSGetRpt;

    PCMSNumRptBytes_Proc    PCMSNumRptBytes;

    PCMSGetRptLine_Proc     PCMSGetRptLine;

    PCMSNumRptLines_Proc    PCMSNumRptLines; 

};
    
             
#endif  /*  pcmsrv.h  */
