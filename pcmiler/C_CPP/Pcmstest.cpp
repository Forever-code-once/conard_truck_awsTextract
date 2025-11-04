#include <iostream>
#include <stdio.h>
#include "pcmsinit.h"
#include "pcmstrip.h"

using namespace std;

#define BUFLEN 	256

#define TESTPLACE1  "Edmonton, AB"
#define TESTPLACE2  "Calgary, AB"
#define TESTPLACE3  "Princeton, NJ"
#define TESTPLACE4  "Chicago, IL"
#define TESTPLACE5  "Trenton, NJ"
#define TESTPLACE6  "San Diego, CA"
#define TESTPLACE7  "Portland, OR"
#define TESTPLACE8  "Seattle, WA"
#define TESTPLACE9  "Denver, CO"
#define TESTPLACE10 "Aiea, HI"
#define TESTPLACE11 "Akona, HI"
#define TESTPLACE12 "Santa Ana, PR"
#define TESTPLACE13 "San Juan, PR"
#define LOOKUPPLACE "PRI*, NJ"
#define BORDERSTOP1 "Detroit, MI"
#define BORDERSTOP2	"Buffalo, NY"
#define FERRYSTOP1	"Boston, MA"
#define FERRYSTOP2	"Nantucket,MA"
#define RESEQSTOP1	"Princeton,NJ"
#define RESEQSTOP2	"Boston,MA"
#define RESEQSTOP3	"Hartford,CT"
#define RESEQSTOP4	"Manchester,NH"
#define AVOIDPLACE	"ALK, NJ"
#define ALIAS1      "Makaka"
#define TESTZIP1    "92014"
#define TESTZIP2    "92020"
#define LATLONG1    "0402515n,0743340w"
#define LATLONG2    "40.421n,74.561w"
#define LATLONG3    "52.5n,92.5w"
#define LLPLACE     "Princeton, NJ"
#define HAZPLACE1     "59758"
#define HAZPLACE2     "Bozeman Hot Springs, MT"
#define SPLC1     "SPLC568110000"
#define SPLC2     "SPLC874430251"
#define SPLCCITY     "SPLCBoston, MA"
#define CANPOST1     "M5S 1A1"
#define CANPOST2     "A0A 1A0"

#define EUROTESTPLACE1  "Moskva, RU"
#define EUROTESTPLACE2  "Sankt Peterbu, RU"
#define EUROTESTPLACE3  "Frankfurt, GM"
#define EUROTESTPLACE4  "Madrid, ES"
#define EUROTESTPLACE5  "Berlin, GM"
#define EUROTESTPLACE6  "London, UK"
#define EUROTESTPLACE7  "Orleans, FR"
#define EUROTESTPLACE8  "Geneva, SW"
#define EUROTESTPLACE9  "Roma, IT"
#define EUROLOOKUPPLACE "FRANK*, GM"
#define EUROALIAS1      "Moscow"
#define EUROLATLONG1    "0485200n,0022200e"
#define EUROLATLONG2    "48.833n,2.333e"
#define EUROLLPLACE     "London, UK"

void Test_trip(PCMServerID server);
void Test_lookup(PCMServerID server);

int DemoRun()
{

	/* Declare a handle to the PCMSERVE engine */
	PCMServerID  serverID;

	/* Declare a buffer and variables to return values from server */
	char buffer[BUFLEN];
	float distance;
	long duration;

	/* Turn off all debugging: 0 = off, 1 = logging, 2 = msg boxes */

	/* Show the product name and version info */
	if (-1 != PCMSAbout("ProductName", buffer, BUFLEN))
        cout << "Product: " << buffer << endl;

    if (-1 != PCMSAbout("ProductVersion", buffer, BUFLEN))
		cout << "Version: " << buffer << endl;

    /* Open a connection to the server: don't pass an HINSTANCE or HWND. */
	serverID = PCMSOpenServer(NULL, NULL);
	if (!PCMSIsValid(serverID))
	{
		/* Print the error if we couldn't initialize */
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		cout << "Could not initialize: " << buffer << endl;
		return (1);
	}
    
    distance = PCMSCalcDistance2 (serverID, TESTPLACE3, TESTPLACE4, CALC_AIR) / 10.0;
    cout << "Air distance from " << TESTPLACE3 << " to " << TESTPLACE4 << ": " << distance << " miles" << endl;

    distance = PCMSCalcDistance3 (serverID, TESTPLACE1, TESTPLACE2,
		CALC_PRACTICAL, &duration) / 10.0;
	cout << "Distance (P) and hours From " << TESTPLACE1 << " To " << TESTPLACE2 << ": " << distance << " miles " 
         << duration << " minutes" << endl;

   	distance = PCMSCalcDistance (serverID, ALIAS1, TESTPLACE4) / 10.0;
	cout << "Distance From (alias) " << ALIAS1 << " To " << TESTPLACE4 << ": "  << distance << endl;

    distance = PCMSCalcDistance (serverID, TESTZIP1, TESTZIP2) / 10.0;
	cout << "Distance From " << TESTZIP1 << " To " << TESTZIP2 << ": " << distance << endl;

	distance = PCMSCalcDistance (serverID, TESTPLACE5, TESTPLACE3) / 10.0;
	cout << "Distance From " << TESTPLACE5 << " To " << TESTPLACE3 << ": " << distance << endl;

	/* Run a simple trip using the default routing calculation type */
	/* Note that the distance is returned in 10ths of miles */
	distance = PCMSCalcDistance (serverID, TESTPLACE3, TESTPLACE4) / 10.0;
	cout << "Distance From " << TESTPLACE3 << " To " << TESTPLACE4 << ": " << distance << endl;

	/* Run a simple trip using the SHORTEST route calculation */
	/* Note that the distance is returned in 10ths of miles */
	distance = PCMSCalcDistance2 (serverID, TESTPLACE3, TESTPLACE4,
		CALC_SHORTEST) / 10.0;
	cout << "Shortest Distance From " << TESTPLACE3 << " To " << TESTPLACE4 << ": " << distance << endl;

	/* Run a simple trip using the AVOIDTOLL route calculation, and get */
	/* the duration of the trip in minutes */
	/* Note that the distance is returned in 10ths of miles */
	distance = PCMSCalcDistance3 (serverID, TESTPLACE3, TESTPLACE4,
		CALC_AVOIDTOLL, &duration) / 10.0;
	cout << "Distance (toll) and hours from " << TESTPLACE3 << " to " << TESTPLACE4 << ": " << distance << " miles " << duration << " minutes" << endl;

	if (0 < PCMSCityToLatLong(serverID, LLPLACE,  buffer, BUFLEN))
		cout << "LatLong for " << LLPLACE << ": " << buffer << endl;

	if (0 < PCMSCityToLatLong(serverID, ALIAS1,  buffer, BUFLEN))
		cout << "LatLong for (alias)" << ALIAS1 << ": " << buffer << endl;

	if (0 < PCMSLatLongToCity(serverID, LATLONG1, buffer, BUFLEN))
		cout << "placename for " << LATLONG1 << ": " << buffer << endl;

	if (0 < PCMSLatLongToCity(serverID, LATLONG2, buffer, BUFLEN))
		cout << "placename for " << LATLONG2 << ": " << buffer << endl;

	/* Do some more complex trip calculations */
	Test_trip(serverID);

    /* Do some city lookups */
	Test_lookup(serverID);

	/* Close down the server engine */
	/* YOU MUST MAKE THIS CALL BEFORE YOUR PROGRAM EXITS! */
	PCMSCloseServer(serverID);

	return 0;
}


/* Test the trip software */
void Test_trip(PCMServerID server)
{
	Trip shortTrip, pracTrip;
	char buffer[BUFLEN];
	char buf[BUFLEN];
	float distance;
	int i;
	int retval;

	/* Create a new trip */
	shortTrip = PCMSNewTrip(server);

	/* Error handling */
	if (0 == shortTrip)
	{
		cout << "Could not create a trip:" << endl;
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		cout << buffer << endl;
		return;
	}

	/* Use SHORTEST routing. Note that distance is in 10ths of miles */
	PCMSSetCalcType(shortTrip, CALC_SHORTEST);
	retval = PCMSGetCalcType (shortTrip);
	cout << "Route type: " << retval << endl;

	distance = PCMSCalcTrip (shortTrip, TESTPLACE3, TESTPLACE6) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f", TESTPLACE3, TESTPLACE6, distance);
	cout << buf << endl;
	
    distance = PCMSCalcTrip (shortTrip, SPLC1, SPLC2) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f", SPLC1, SPLC2, distance);
	cout << buf << endl;

    distance = PCMSCalcTrip (shortTrip, CANPOST1, CANPOST1) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f", CANPOST1, CANPOST1, distance);
	cout << buf << endl;

    PCMSSetCalcType(shortTrip, CALC_PRACTICAL);
	distance = PCMSCalcTrip (shortTrip, HAZPLACE1, HAZPLACE2) / 10.0;
	sprintf (buf,"Distance (P) from %s to %s (no haz): %f", HAZPLACE1, HAZPLACE2, distance);
	cout << buf << endl;

    PCMSSetHazOption(shortTrip, 4);
	distance = PCMSCalcTrip (shortTrip, HAZPLACE1, HAZPLACE2) / 10.0;
	sprintf (buf,"Distance (P) from %s to %s (haz): %f", HAZPLACE1, HAZPLACE2, distance);
	cout << buf << endl;

	/* Create another new trip */
	pracTrip = PCMSNewTrip(server);

	/* Error handling */
	if (0 == pracTrip)
	{
		/* Delete the first trip we created */
		PCMSDeleteTrip(shortTrip);

		sprintf (buf,"Could not create a trip:");
		cout << buf << endl;
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf,"%s", buffer);
		cout << buf << endl;
    	return;
	}

	/* Use PRACTICAL routing, and convert distance to kilometers */
	PCMSSetCalcType(pracTrip, CALC_PRACTICAL);
	PCMSSetKilometers(pracTrip);

	/* Make the trip go through Chicago IL */
	PCMSAddStop(pracTrip, TESTPLACE3);
	PCMSAddStop(pracTrip, TESTPLACE4);
	PCMSAddStop(pracTrip, TESTPLACE6);

	/* Run the route calculation. Note that distance is in 10ths of miles */
	distance = PCMSCalculate(pracTrip) / 10.0;
	sprintf (buf,"Practical route (%s, %s, %s) in km: %f", TESTPLACE3, TESTPLACE4, TESTPLACE6, distance);
	cout << buf << endl;

	// Test cost
	distance = PCMSGetCost (pracTrip) / 10.0;
	cout << "Cost: " << distance;
	PCMSSetCost (pracTrip, 10);
	PCMSCalculate (pracTrip);
	distance = PCMSGetCost (pracTrip) / 10.0;
	cout << "Cost: " << distance;

	// Test distance to route
	distance = PCMSCalcDistToRoute (pracTrip, AVOIDPLACE) / 10.0;
	cout << "Distance from " << AVOIDPLACE << " to route: " << distance << endl;
    Trip airTrip = PCMSNewTrip (server);
    PCMSSetCalcType (airTrip, CALC_PRACTICAL);

    PCMSSetOnRoad (airTrip, false);
    distance = PCMSCalcTrip (airTrip, LATLONG1, LATLONG3) / 10.0;
	sprintf (buf,"Practical route (%s, %s, %s): %f", LATLONG1, LATLONG3, "Off road", distance);
	cout << buf << endl;

    Trip airTrip1 = PCMSNewTrip (server);
    PCMSSetCalcType (airTrip1, CALC_PRACTICAL);
    PCMSSetOnRoad (airTrip1, true);
    distance = PCMSCalcTrip (airTrip1, LATLONG1, LATLONG3) / 10.0;
	sprintf (buf,"Practical route (%s, %s, %s): %f", LATLONG1, LATLONG3, "On road", distance);
	cout << buf << endl;

	sprintf (buf,"*******************");
	cout << buf << endl;

    /* Show the detailed driving instruction for the route just run */
	/* Note: the first line is line 0. The buffer should be > 100 char */
	for (i = 0; i < PCMSNumRptLines(pracTrip, RPT_DETAIL); i++)
	{
		PCMSGetRptLine(pracTrip, RPT_DETAIL, i, buffer, BUFLEN);
		cout << buffer << endl;
	}

	/* Show the trip's state by state mileage breakdown, in driving order */
	/* Note: using the returned pointer directly in the printf */
	PCMSSetAlphaOrder(pracTrip, false);
	sprintf (buf,"*******************");
	cout << buf << endl;

	for (i = 0; i < PCMSNumRptLines(pracTrip, RPT_STATE); i++)
	{
		PCMSGetRptLine(pracTrip, RPT_STATE, i, buffer, BUFLEN);
		cout << buffer << endl;
	}

	sprintf (buf,"*******************");
	cout << buf << endl;

	PCMSSetAlphaOrder(pracTrip, true);
	sprintf (buf,"*******************");
	cout << buf << endl;

	for (i = 0; i < PCMSNumRptLines(pracTrip, RPT_STATE); i++)
	{
		PCMSGetRptLine(pracTrip, RPT_STATE, i, buffer, BUFLEN);
		cout << buffer << endl;
	}

	sprintf (buf,"*******************");
	cout << buf << endl;

	/* Show the trip's leg by leg mileage breakdown */
	/* Note: passing NULL as a buffer, returns ptr to internal memory */
	for (i = 0; i < PCMSNumRptLines(pracTrip, RPT_MILEAGE); i++)
	{
		PCMSGetRptLine(pracTrip, RPT_MILEAGE, i, buffer, BUFLEN);
		cout << buffer << endl;
	}

    /* Generate HTML Detailed Report */
    long numBytes = PCMSNumHTMLRptBytes (pracTrip, RPT_DETAIL);
    if (numBytes > 0)
    {
        char* htmlReport = new char [numBytes + 1];
        long ret = PCMSGetHTMLRpt (pracTrip, RPT_DETAIL, htmlReport, numBytes + 1);
        if (ret <= 0)
            cout << "Detailed HTML Report is empty" << endl;
        else
        {
            FILE* fl = fopen ("p.html", "w");
            fprintf (fl, htmlReport);
            fclose (fl);
            cout << "See p.html for Detailed HTML Report" << endl;
        }
    }
    else
        cout << "No detailed HTML Report" << endl;

    /* Generate HTML State Report */
    numBytes = PCMSNumHTMLRptBytes (pracTrip, RPT_STATE);
    if (numBytes > 0)
    {
        char* htmlReport = new char [numBytes + 1];
        long ret = PCMSGetHTMLRpt (pracTrip, RPT_STATE, htmlReport, numBytes + 1);
        if (ret <= 0)
            cout << "State HTML Report is empty" << endl;
        else
        {
            FILE* fl = fopen ("s.html", "w");
            fprintf (fl, htmlReport);
            fclose (fl);
            cout << "See s.html for State HTML Report" << endl;
        }
    }
    else
        cout << "No State HTML Report" << endl;

    /* Generate HTML Mileage Report */
    numBytes = PCMSNumHTMLRptBytes (pracTrip, RPT_MILEAGE);
    if (numBytes > 0)
    {
        char* htmlReport = new char [numBytes + 1];
        long ret = PCMSGetHTMLRpt (pracTrip, RPT_MILEAGE, htmlReport, numBytes + 1);
        if (ret <= 0)
            cout << "Mileage HTML Report is empty" << endl;
        else
        {
            FILE* fl = fopen ("m.html", "w");
            fprintf (fl, htmlReport);
            fclose (fl);
            cout << "See m.html for State HTML Report" << endl;
        }
    }
    else
        cout << "No State HTML Report" << endl;

    PCMSClearStops(pracTrip);


	/* Using the current settings, run a different trip */
	float dist = PCMSCalcDistance (server, TESTPLACE10, TESTPLACE11) / 10.0;
	sprintf (buf, "Distance From %s To %s : %f", TESTPLACE10, TESTPLACE11, dist);
	cout << buf << endl;
    if (0>dist) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "%s", buffer);
		cout << buf << endl;
    }

	PCMSSetCalcType(shortTrip, CALC_SHORTEST);
	distance = PCMSCalcTrip (shortTrip, TESTPLACE3, TESTPLACE12) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f, mi", TESTPLACE3, TESTPLACE12, distance);
	cout << buf << endl;
	if (0>distance) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "%s", buffer);
		cout << buf << endl;
    }

	distance = PCMSCalcTrip (shortTrip, TESTPLACE3, TESTPLACE13) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f", TESTPLACE3, TESTPLACE13, distance);
	cout << buf << endl;
	if (0>distance) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "%s", buffer);
		cout << buf << endl;
    }

	distance = PCMSCalcTrip (shortTrip, TESTPLACE3, TESTPLACE4) / 10.0;
	sprintf (buf,"Distance (S) from %s to %s: %f", TESTPLACE3, TESTPLACE4, distance);
	cout << buf << endl;
	if (0>distance) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "%s", buffer);
		cout << buf << endl;
    }


    /* Find location on route */
	PCMSAddStop(pracTrip, TESTPLACE4);
	PCMSAddStop(pracTrip, TESTPLACE3);
	PCMSAddStop(pracTrip, TESTPLACE4);
    PCMSCalculate(pracTrip);

    int retVal, strSize = 35;
    char location[35];
	retVal = PCMSGetLocAtMiles(pracTrip, 20, location, strSize);
	sprintf (buf, "miles: 2, retval: %d, location: %s", retVal, location);
	cout << buf << endl;
    if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSGetLocAtMiles(pracTrip, 7800, location, strSize);
	sprintf (buf, "miles: 780, retval: %d, location: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSLatLongAtMiles(pracTrip, 7800, location, TRUE);
	sprintf (buf, "miles: 780, retval: %d, latlong: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSLatLongAtMiles(pracTrip, 7800, location, FALSE);
	sprintf (buf, "miles: 780, retval: %d, latlong: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSGetLocAtMinutes(pracTrip, 0, location, strSize);
	sprintf (buf, "minutes: 0, retval: %d, location: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSGetLocAtMinutes(pracTrip, 5, location, strSize);
	sprintf (buf, "minutes: 5, retval: %d, location: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSLatLongAtMinutes(pracTrip, 5, location, TRUE);
	sprintf (buf, "minutes: 5, retval: %d, latlong: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	retVal = PCMSLatLongAtMinutes(pracTrip, 5, location, FALSE);	
	sprintf (buf, "minutes: 5, retval: %d, latlong: %s", retVal, location);
	cout << buf << endl;
        if (0==retVal) {
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "DLLerror: %s", buffer);
		cout << buf << endl;
    }

	PCMSClearStops(pracTrip);

	PCMSAddStop(pracTrip, TESTPLACE7);
	PCMSAddStop(pracTrip, TESTPLACE8);
	PCMSAddStop(pracTrip, TESTPLACE9);

	sprintf (buf, "Practical KM: %f", PCMSCalculate(pracTrip)/10.0);
	cout << buf << endl;

	// Test resequence thru all
	PCMSClearStops(pracTrip);
	PCMSAddStop (pracTrip, RESEQSTOP1);
	PCMSAddStop (pracTrip, RESEQSTOP2);
	PCMSAddStop (pracTrip, RESEQSTOP3);
	PCMSSetResequence (pracTrip, TRUE);
	PCMSOptimize (pracTrip);
	cout << "Optimized trip: " << endl;
	int numStops = PCMSNumStops (pracTrip);
	for (int sNum = 0; sNum < numStops; ++sNum)
	{
		PCMSGetStop (pracTrip, sNum, buffer, BUFLEN);
		cout << buffer << endl;
	}
	
	// Test resequence destination fixed
	PCMSClearStops(pracTrip);
	PCMSAddStop (pracTrip, RESEQSTOP1);
	PCMSAddStop (pracTrip, RESEQSTOP2);
	PCMSAddStop (pracTrip, RESEQSTOP3);
	PCMSAddStop (pracTrip, RESEQSTOP4);
	PCMSSetResequence (pracTrip, FALSE);
	PCMSOptimize (pracTrip);
	cout << "Optimized trip: " << endl;
	numStops = PCMSNumStops (pracTrip);
	for (sNum = 0; sNum < numStops; ++sNum)
	{
		PCMSGetStop (pracTrip, sNum, buffer, BUFLEN);
		cout << buffer << endl;
	}

	// Test Hub mode
	PCMSSetHubMode (pracTrip, TRUE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << "Hub Mode distance: " << distance << " miles" << endl;


	// Test break hours
	distance = PCMSGetBreakHours (pracTrip);
	cout << "Break time (min): " << distance;
	distance = PCMSGetBreakWaitHours (pracTrip);
	cout << "; Break wait time (min): " << distance;
	PCMSCalculate (pracTrip);
	distance = PCMSGetDuration (pracTrip); 
	cout << "; Trip time (mi): " << distance << endl;

	PCMSSetBreakHours (pracTrip, 10);
	PCMSSetBreakWaitHours (pracTrip, 2);
	PCMSCalculate (pracTrip);
	distance = PCMSGetDuration (pracTrip); 
	cout << "New trip time (mi): " << distance << endl;

	// Test delete stop
	PCMSDeleteStop (pracTrip, 0);
	numStops = PCMSNumStops (pracTrip);
	cout << "Number of stops after Delete: " << numStops << endl;

	// Test loaded option
	retval = PCMSSetLoaded (pracTrip, 0, TRUE);
	cout << "SetLoaded returned " << retval << endl;

	// Test borders options
	PCMSClearStops (pracTrip);
	PCMSAddStop (pracTrip, BORDERSTOP1);
	PCMSAddStop (pracTrip, BORDERSTOP2);
	PCMSSetBordersOpen (pracTrip, TRUE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << BORDERSTOP1 << " to " << BORDERSTOP2 << ", borders open: " << distance << endl;
	PCMSSetBordersOpen (pracTrip, FALSE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << BORDERSTOP1 << " to " << BORDERSTOP2 << ", borders closed: " << distance << endl;

	// Test ferry distance option
	PCMSClearStops (pracTrip);
	PCMSAddStop (pracTrip, FERRYSTOP1);
	PCMSAddStop (pracTrip, FERRYSTOP2);
	PCMSSetShowFerryMiles (pracTrip, TRUE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << FERRYSTOP1 << " to " << FERRYSTOP2 << ", ferry on: " << distance << endl;
	PCMSSetShowFerryMiles (pracTrip, FALSE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << FERRYSTOP1 << " to " << FERRYSTOP2 << ", ferry off: " << distance << endl;

	// Test avoid/favor
	PCMSClearStops (pracTrip);
	PCMSAddStop (pracTrip, TESTPLACE3);
	PCMSAddStop (pracTrip, AVOIDPLACE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << "Normal distance from " << TESTPLACE3 << " to " << AVOIDPLACE << ": " << distance << endl;
	PCMSAFLinks (pracTrip, FALSE);
	PCMSSetCustomMode (pracTrip, TRUE);
	distance = PCMSCalculate (pracTrip) / 10.0;
	cout << "Avoided distance from " << TESTPLACE3 << " to " << AVOIDPLACE << ": " << distance << endl;

	// Test latlongs en-route
	long nPairs = PCMSLatLongsEnRoute (pracTrip, NULL, 0, FALSE);
	double* pts = new double [nPairs * 2];
	PCMSLatLongsEnRoute (pracTrip, pts, nPairs, FALSE);
	cout << "Latlongs en-route:" << endl;
	for (long n = 0; n < nPairs * 2; n+=2)
		cout << pts [n] << "," << pts [n + 1];
	delete [] pts;

	/* Free up the trips we used. You can have up to 8 trips open at once. */
	PCMSDeleteTrip(shortTrip);
	PCMSDeleteTrip(pracTrip);
}


void Test_lookup(PCMServerID server)
{
	Trip trip, euroTrip;
	char buffer[BUFLEN];
	char buf[BUFLEN];
	int matches;
	int i;

    /* Check a place name without a trip. */
	matches = PCMSCheckPlaceName(server, LOOKUPPLACE);
   if (matches)
		sprintf (buf,"%s does exist in the database", LOOKUPPLACE);
   else
		sprintf (buf,"%s does not exist in the database", LOOKUPPLACE);
	cout << buf << endl;

	/* Create a new trip */
	trip = PCMSNewTrip (server);

	if (trip > 0)
   {
        matches = PCMSLookup(trip, LATLONG1, 1);
      if (0 < matches)
      {
   		PCMSGetFmtMatch(trip, 0, buf, BUFLEN, 10, 20, 20);
			cout << buf << endl;
      }

   	matches = PCMSLookup(trip, ALIAS1, 1);
      if (0 < matches)
      {
   		PCMSGetFmtMatch(trip, 0, buf, BUFLEN, 10, 20, 20);
			cout << buf << endl;
      }

      /* Lookup a specific city: try for an exact match */
      matches = PCMSLookup(trip, LOOKUPPLACE, 1);
      sprintf (buf,"%d matching (exactly) cities to %s", matches, LOOKUPPLACE);
      cout << buf << endl;
      if (matches)
      {
      	PCMSGetMatch(trip, 0, buf, 100);
      	cout << buf << endl;
      }
      /* Lookup a specific city: try for an exact match */
      matches = PCMSLookup(trip, LOOKUPPLACE, 2);
      sprintf (buf,"%d matching (by default) cities to %s", matches, LOOKUPPLACE);
      cout << buf << endl;
      if (matches)
      {
       	PCMSGetMatch(trip, 0, buf, 100);
      	cout << buf << endl;
      }
      /* Lookup all cities that match: i is the number of matches */
      matches = PCMSLookup(trip, LOOKUPPLACE, 0);
      sprintf (buf,"%d matching (partially) cities to '%s'", matches, LOOKUPPLACE);
      cout << buf << endl;

      /* Show all the matches again without buffer, and using 'matches' */
      for (i = 0; i < matches; i++)
      {
         PCMSGetMatch(trip, i, buffer, BUFLEN);
         sprintf (buf,"[%s]", buffer);
         cout << buf << endl;
      }

      /* Lookup all cities that match: i is the number of matches */
      matches = PCMSLookup(trip, SPLCCITY, 0);
      sprintf (buf,"%d matching (partially) cities to '%s'", matches, SPLCCITY);
      cout << buf << endl;
      /* Show all the matches again without buffer, and using 'matches' */
      for (i = 0; i < matches; i++)
      {
         PCMSGetMatch(trip, i, buffer, BUFLEN);
         sprintf (buf,"[%s]", buffer);
         cout << buf << endl;
      }
      /* Delete the trip */
      PCMSDeleteTrip(trip);
   }
   else
   {
		/* Print the error if we couldn't initialize */
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "Error creating trip: %s", buffer);
		cout << buf << endl;
   }

   sprintf (buf,"*******************");
   cout << buf << endl;

   int numRegions = PCMSNumRegions (server);
   cout << "Number of installed regions: " << numRegions << endl;
   while (0 != numRegions)
   {
		PCMSGetRegionName (server, numRegions - 1, buffer, BUFLEN);
		cout << buffer << endl;
		--numRegions;
   }
   cout << endl;

   // Create a trip in Europe
   const char* reg = "EUROPE";
	euroTrip = PCMSNewTripWithRegion (server, reg);
   if (euroTrip > 0)
   {
      // Lookup all cities that match: i is the number of matches
      matches = PCMSLookup(euroTrip, EUROLOOKUPPLACE, 0);
      sprintf (buf,"%d matching cities to '%s'", matches, EUROLOOKUPPLACE);
      cout << buf << endl;

      // Show all the matches again without buffer, and using 'matches'
      for (i = 0; i < matches; i++)
      {
//         PCMSGetFmtMatch(euroTrip, i, buffer, BUFLEN, 10, 5, 4);
         PCMSGetMatch(euroTrip, i, buffer, BUFLEN);
         sprintf (buf,"[%s]", buffer);
         cout << buf << endl;
      }

      // Delete the trip
      PCMSDeleteTrip(euroTrip);
   }
   else
   {
		// Print the error if we couldn't initialize
		PCMSGetErrorString(PCMSGetError(), buffer, BUFLEN);
		sprintf (buf, "Error creating trip: %s", buffer);
		cout << buf << endl;
   }
}

void main (int /*argc*/, char* /*argv*/ [])
{
	DemoRun();
}


