/*********************************************************************
* File:            PCMSTRIP.H
* Revision Number: 17.0
* Copyright:       1995-2003 ALK Technologies, Inc.
* Purpose:         PC*MILER/Connect DLL C-interface header file.
*
**********************************************************************
* Notes/Revisions:
*
*********************************************************************/

#ifndef __PCMSTRIP_H
#define __PCMSTRIP_H

/*********************************************************************
**********************************************************************
*********************************************************************/

/*************************************************************************
* All function are explicitly exported.
**************************************************************************/

/*********************************************************************
* Includes
*********************************************************************/
#ifndef __WINDOWS_H
#include <windows.h>
#endif

#ifndef __PCMSDEFS_H
#include "pcmsdefs.h"
#endif

#ifndef __PCMSINIT_H
#include "pcmsinit.h"
#endif

#ifdef __cplusplus
extern "C" {
#endif	/* __cplusplus */

/*********************************************************************
* Types
*********************************************************************/
typedef long Trip;

/*********************************************************************
**********************************************************************
*********************************************************************/

/*********************************************************************
* SIMPLE INTERFACE
*********************************************************************/

/* Calculate distance from origin to destination. */
long _PCMSFN PCMSCalcDistance (PCMServerID serv, const char FAR *orig,
	const char FAR *dest);

/* Calculate distance from orig to dest using a given routing type */
long _PCMSFN PCMSCalcDistance2 (PCMServerID serv, const char FAR *orig,
	const char FAR *dest, int routeType);

/* Calculate distance and minutes from orig to dest using given routing type */
long _PCMSFN PCMSCalcDistance3 (PCMServerID serv, const char FAR *orig,
	const char FAR *dest, int routeType, long *minutes);

/* Validate a city name or zip code. Returns the number of exact matches. */
int _PCMSFN PCMSCheckPlaceName (PCMServerID serv, const char FAR *cityZip);

/* For a given placename, return the corresponding latlong */
int _PCMSFN PCMSCityToLatLong(PCMServerID serv, const char FAR *name,
                              char FAR *buffer, int bufSize);

/* For a given latlong, return placename */
int _PCMSFN PCMSLatLongToCity(PCMServerID serv, const char FAR *latlong,
                              char FAR *buffer, int bufSize);

/* For a given city & address, return the corresponding latlong */
int _PCMSFN PCMSAddressToLatLong(PCMServerID serv, const char *name, char *buffer, int bufSize);

/* For a given latlong, return city & address */
int _PCMSFN PCMSLatLongToAddress(PCMServerID serv, const char *latlong, char *buffer, int bufSize);

/*********************************************************************
* COMPLEX INTERFACE
*********************************************************************/

/* Return number of region names in region manager list of region names */
int _PCMSFN PCMSNumRegions(PCMServerID);

/* Return name for the idx-th region */
int _PCMSFN PCMSGetRegionName(PCMServerID, int idx,
                              char FAR *name, int bufSize);

/* Create a new PC*MILER trip. */
Trip _PCMSFN PCMSNewTrip(PCMServerID serv);

/* Create a new PC*MILER trip. */
Trip _PCMSFN PCMSNewTripWithRegion(PCMServerID serv, const char FAR *regionID);

/* Free up a PC*MILER trip. */
void _PCMSFN PCMSDeleteTrip(Trip trip);

/* Calculate distance from origin to destination using the trip's settings */
long _PCMSFN PCMSCalcTrip (Trip trip,
                           const char FAR *orig, const char FAR *dest);

/* Calculate trip's distance through all the stops using current settings */
long _PCMSFN PCMSCalculate(Trip trip);

/* Optimize the order of stops. Must then calculate the route. */
/* Return -1 on error, 0 if already optimized, 1 on success */
int _PCMSFN PCMSOptimize(Trip trip);

/* Return duration of trip in minutes. */
long _PCMSFN PCMSGetDuration(Trip trip);

/* Return toll amount for the trip in cents */
long _PCMSFN PCMSGetToll(Trip trip);

/* Number of toll discount programs (i.e. EZPass, FasTrak, etc.) recognized by PC*MILER */
/* Note that it also includes cash, which technically is not a discount */
int _PCMSFN PCMSNumTollDiscounts(PCMServerID serv);

/* Retrieve toll discount name by index. Returns actual number of bytes in buffer, -1 on error */
int _PCMSFN PCMSGetTollDiscountName(PCMServerID serv, int idx, char *buffer, int bufSize);

/* Get toll payed through discount program discProgram.
If State is NULL, returns tolls for all states */
long _PCMSFN PCMSGetTollBreakdown(Trip trip, int discProgram, char *state);

/* Add a stop to the trip's list of stops */
int _PCMSFN PCMSAddStop(Trip trip, const char FAR *stop);

/* delete a specified stop */
int _PCMSFN PCMSDeleteStop(Trip trip, int which);

/* Clear the trip's list of all stops */
void _PCMSFN PCMSClearStops(Trip trip);

/* Return a place name from the stop list. Index the list from 0. */
/* Pass buffer with space for the string, and the buffer's length in bufSize. */
int _PCMSFN PCMSGetStop(Trip trip, int which, char FAR *buffer, int bufSize);

/* Return number of stops */
int _PCMSFN PCMSNumStops(Trip trip);

/* Returns the type of the stop. Index the list from 0. */
int _PCMSFN PCMSGetStopType(Trip trip, int which, int *type);

/* Set loaded flag for a stop: loaded == true if loaded */
/* returns 1 on success, 0 on failure                   */
int _PCMSFN PCMSSetLoaded(Trip trip, int which, BOOL loaded);

/* Look up a zip or city/state name and returns the number of matching places */
/* Use PCMSGetMatch() to retrieve each matching place. */
/* Set easyMatch to 1 to only return exact matches, 0 for partial matches. */
int _PCMSFN PCMSLookup(Trip trip, const char FAR *city, int easyMatch);

/* Return place name from last PCMSLookup(). Index list from 0. */
/* Pass buffer with space for the string, and the buffer's length in bufSize. */
int _PCMSFN PCMSGetMatch(Trip trip, int which, char FAR *buffer, int bufSize);

/* Return placename form the last PCMSLookup() in a custom format */
int _PCMSFN PCMSGetFmtMatch(Trip trip, int which,
                            char FAR *buffer, int bufSize,
                            int zipLen, int cityLen, int countyLen);

/* return the placename from the last PCMSLookup() separated into
   address, city, state, zip and county */
int _PCMSFN PCMSGetFmtMatch2(Trip trip, int which,
                            char *addrBuf, int addrLen,
                            char *cityBuf, int cityLen,
                            char *stateBuf, int stateLen,
                            char *zipBuf, int zipLen,
                            char *countyBuf, int countyLen);

/* Total number of matches in the last PCMSLookup(). Then index list from 0. */
int _PCMSFN PCMSNumMatches(Trip trip);

/* Return the number of POI categories.
*/
int _PCMSFN PCMSNumPOICategories(PCMServerID serv);

/*	Get the name of a POI category.
	Valid index is from 0 to return value - 1 of POICategoriesNum function.
	Return value is number bytes written in buffer.
*/
int _PCMSFN PCMSPOICategoryName(PCMServerID serv, int index, char *buffer, int bufSize);

/*	Lookup cities, postal codes, custom places, and/or POIs within miles defined by radius
	for a particular city/state or zip defined in city buffer.
	To find index for a POI category use POICategoriesNum and POICategoryName functions.
	Return value of number of items found.
*/
int _PCMSFN PCMSLocRadLookup(Trip trip, const char * city, int radius, BOOL cities,
							 BOOL postalCodes, BOOL customPlaces, BOOL poi, int poiCategoryIndex);

/*	Get an item found in LocRadLookup query.
	Valid index is from 0 to return value - 1 of LocRadLookup function.
	Return value is number of bytes written in buffer.
*/
int _PCMSFN PCMSGetLocRadItem(Trip trip, int index, char *buffer, int bufSize);


/*********************************************************************
* REPORT DATA
*********************************************************************/

/* Saves all the report's text in user-supplied buffer.
   Returns length of the report in bytes. */
int _PCMSFN PCMSGetRpt(Trip trip, int rptNum, char FAR *buffer, int bufSize);

/* Copy a line from report into the buffer. Index lines from 0. */
/* Returns number of bytes copied, -1 on error, 0 if report not ready. */
int _PCMSFN PCMSGetRptLine(Trip trip, int rptNum, int lineNum,
	                       char FAR *buffer, int bufSize);

/* Get the number of lines in each report. */
int _PCMSFN PCMSNumRptLines(Trip trip, int rptNum);

/* Get total length of the entire report. Use this as the buffer size. */
long _PCMSFN PCMSNumRptBytes(Trip trip, int rptNum);


/* Saves all the report's text in HTML format in user-supplied buffer.
   Returns length of the report in bytes. */
long _PCMSFN PCMSGetHTMLRpt(Trip trip, int rptNum,
                                      char FAR *buffer, long bufSize);

/* Get total length of the entire report in HTML format.
   Use this as the buffer size. */
long _PCMSFN PCMSNumHTMLRptBytes(Trip trip, int rptNum);

/* Gets the number of segments in the detailed report */
int _PCMSFN PCMSGetNumSegments(Trip trip);

/* Gets a segment; if segNum == -1 then the next line is returned else
	segment segNum is returned 											*/
int _PCMSFN PCMSGetSegment(Trip trip, int segNum,
                           struct segmentStruct *aSegment);

/* Gets the legInfo like legMiles, total Miles, legHrs... for a specified leg */
int _PCMSFN PCMSGetLegInfo(Trip trip, int legNum,
                           struct legInfoType *pLegInfo);

/*********************************************************************
* OPTIONS
*********************************************************************/

/* Set the criteria used to calculate the minimum distance */
/* See CALC_ #defines for more information */
void _PCMSFN PCMSSetCalcType(Trip trip, int calcType);
int _PCMSFN PCMSGetCalcType(Trip trip);

/* Set the criteria used to calculate distance using extended combinations */
/* See CALCEX_ #defines for more information */
void _PCMSFN PCMSSetCalcTypeEx(Trip trip, int rtType, int optFlags, int vehType);
int _PCMSFN PCMSGetCalcTypeEx(Trip trip, int* pRtType, int* pOptFlags, int* pVehType);

/* Run with borders open (true) or closed (false) */
void _PCMSFN PCMSSetBordersOpen(Trip trip, BOOL open);

/* Set the route flag "add ferry miles" to onOff */
void _PCMSFN PCMSSetShowFerryMiles(Trip trip, BOOL onOff);

/* Report distances in kilometers */
void _PCMSFN PCMSSetKilometers(Trip trip);

/* Report distances in miles */
void _PCMSFN PCMSSetMiles(Trip trip);

/* Do routing in hub mode. I.e. not THROUGH each stop, but TO each stop */
void _PCMSFN PCMSSetHubMode(Trip trip, BOOL onOff);

/* Set cost per mile */
void _PCMSFN PCMSSetCost(Trip trip, int cost);

/* Get cost per mile */
int _PCMSFN PCMSGetCost(Trip trip);

/* When optimizing, reorder all but the first stop (true), */
/* or all but the first and last stops (false). */
void _PCMSFN PCMSSetResequence(Trip trip, BOOL changeDest);

/* Enable/disable toll information
	mode values as follows:
	0 - no toll information,
	1 - cash toll amount,
	2 - discount toll amount */
void _PCMSFN PCMSSetTollMode(Trip trip, int mode);

/* Set the trip's current settings from a set of flags. */
void _PCMSFN PCMSSetOptions(Trip trip, long opts);

/* Return all the current settings as a set of flags. */
long _PCMSFN PCMSGetOptions(Trip trip);

/* Return number of legs in the trip. */
int _PCMSFN PCMSNumLegs(Trip trip);

/* Reset all options to their defaults */
void _PCMSFN PCMSDefaults(Trip trip);

/* Sets break hours (input should be in minutes)*/
void _PCMSFN PCMSSetBreakHours(Trip trip, long hours);

/* Gets break hours in minutes*/
long _PCMSFN PCMSGetBreakHours(Trip trip);

/* Sets break waiting hours (input should be in minutes)*/
void _PCMSFN PCMSSetBreakWaitHours(Trip trip, long hours);

/* Gets break waiting hours in minutes*/
long _PCMSFN PCMSGetBreakWaitHours(Trip trip);

/* Sets border waiting hours (input should be in minutes) */
void _PCMSFN PCMSSetBorderWaitHours(Trip trip, long minutes);

/* Gets border waiting hours in minutes */
long _PCMSFN PCMSGetBorderWaitHours(Trip trip);

/* Enable/disable on-road latlong geocoding */
void _PCMSFN PCMSSetOnRoad(Trip trip, BOOL onOff);

/* Run with custom mode on(true) or off(false) */
void _PCMSFN PCMSSetCustomMode(Trip trip, BOOL onOff);

/* Put state report in alphabetical (true) or in driving order (false). */
void _PCMSFN PCMSSetAlphaOrder(Trip trip, BOOL alphaOrder);

/* Favor/avoid all links the trip. */
int _PCMSFN PCMSAFLinks(Trip trip, BOOL favor);

/* loads current set of avoided/favored links for the default region
return values: -1 - error, 1 - success, 0 - no attribute */
int _PCMSFN PCMSAFLoad(PCMServerID, char *filename);

/* saves current set of avoided/favored links for the default region to a file
return values: -1 - error, 1 - success, 0 - no attribute */
int _PCMSFN PCMSAFSave(PCMServerID);

/* loads current set of avoided/favored links for the region, if regionID is NULL
saves default region av/fav links;
return values:       -1 - error, 1 - success, 0 - no attribute */
int _PCMSFN PCMSAFLoadForRegion(PCMServerID serv, char *filename, const char* regionID);

/* saves current set of avoided/favored links for the region to a file,
if regionID is NULL saves default region av/fav links;
return values:       -1 - error, 1 - success, 0 - no attribute */
int _PCMSFN PCMSAFSaveForRegion(PCMServerID serv, const char* regionID);

/* Calculates an ortogonal distance to route from "location" */
int _PCMSFN PCMSCalcDistToRoute(Trip trip, char *location);

/* Get location on the route 'miles' from the origin. */
int _PCMSFN PCMSGetLocAtMiles(Trip trip, long miles,
                              char FAR *pLocation, int size);

/* Get location on the route 'minutes' from the origin. */
int _PCMSFN PCMSGetLocAtMinutes(Trip trip, long minutes,
                                char FAR *pLocation, int size);

/* Get location on the route 'miles' from the origin (double latlong[2]). */
int _PCMSFN PCMSLatLongAtMiles(Trip trip, long miles,
                               char FAR *latlong, BOOL useShpPts);

/* Get location on the route 'minutes' from the origin (double latlong[2]). */
int _PCMSFN PCMSLatLongAtMinutes(Trip trip, long minutes,
                                 char FAR *latlong, BOOL useShpPts);

/*	returns # of lat/long pairs in latlong buffer or 0 on error */
long _PCMSFN PCMSLatLongsEnRoute(Trip trip, double* latlong,
                                 long numPairs, BOOL shpPts);


/* Run with heavy vehicle on(true) or off(false) */
void _PCMSFN PCMSSetVehicleType(Trip trip, BOOL onOff);
/* Match address on road name only (true).  Match address exactly(false).*/
void _PCMSFN PCMSSetRoadNameOnly(Trip trip, BOOL onOff);
void _PCMSFN PCMSSetExactLevel(PCMServerID serv, int threshold);
int _PCMSFN PCMSGetExactLevel(PCMServerID serv);

/* Set Hazardous Routing options; with HazMat add-on only */
void _PCMSFN PCMSSetHazOption(Trip trip, int hazType);
/*       hazType:
            0 - none;
            1 - general;
            2 - explosive;
            3 - inhalant;
            4 - radioactive; */


#ifdef __cplusplus
};
#endif	/* __cplusplus */

#endif	/* __PCMSTRIP_H */


