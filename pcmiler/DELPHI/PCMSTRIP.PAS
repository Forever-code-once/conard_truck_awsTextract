unit PCMSTRIP;


interface
uses
  Wintypes;

{+//******************************************************************** }
{-* Defines }
{=******************************************************************** }

{+//******************************************************************** }
{-* Type of routing calculations }
{=******************************************************************** }
const
  CALC_PRACTICAL = 0;
const
  CALC_SHORTEST = 1;
const
  CALC_NATIONAL = 2;
const
  CALC_AVOIDTOLL = 3;
const
  CALC_AIR = 4;
const
  CALC_RESERVED = 5;
const
  CALC_FIFTYTHREE = 6;
{+//******************************************************************** }
{-* Report types }
{=******************************************************************** }
const
  RPT_DETAIL = 0;
const
  RPT_STATE = 1;
const
  RPT_MILEAGE = 2;

{+//******************************************************************** }
{-* Order of states in reports }
{=******************************************************************** }
const
  STATE_ORDER = 1;
const
  TRIP_ORDER = 2;

{+//******************************************************************** }
{-* Options }
{=******************************************************************** }
const
  OPTS_NONE = $0000;
const
  OPTS_MILES = $0001;
const
  OPTS_CHANGEDEST = $0002;
const
  OPTS_HUBMODE = $0004;
const
  OPTS_BORDERS = $0008;
const
  OPTS_ALPHAORDER = $0010;
const
  OPTS_HEAVY = $0020;
const
  OPTS_ERROR = $FFFF;

{+//******************************************************************** }
{-* Error codes }
{=******************************************************************** }
const
  PCMS_INVALIDPTR = 101;
const
  PCMS_NOINIFILE = 102;
const
  PCMS_LOADINIFILE = 103;
const
  PCMS_LOADGEOCODE = 104;
const
  PCMS_LOADNETWORK = 105;
const
  PCMS_MAXTRIPS = 106;
const
  PCMS_INVALIDTRIP = 107;
const
  PCMS_INVALIDSERVER = 108;
const
  PCMS_BADROOTDIR = 109;
const
  PCMS_BADMETANETDIR = 110;
const
  PCMS_NOLICENSE = 111;
const
  PCMS_TRIPNOTREADY = 112;
const
  PCMS_INVALIDPLACE = 113;
const
  PCMS_ROUTINGERROR = 114;
const
  PCMS_OPTERROR = 115;
const
  PCMS_OPTHUB = 116;
const
  PCMS_OPT2STOPS = 117;
const
  PCMS_OPT3STOPS = 118;
const
  PCMS_NOTENOUGHSTOPS = 119;
const
  PCMS_BADNETDIR = 120;
const
  PCMS_LOADGRIDNET = 121;
const
  PCMS_BADOPTIONDIR = 122;
const
  PCMS_DISCONNECTEDNET = 123;
const
  PCMS_NOTRUCKSTOP = 124;
const
  PCMS_INVALIDREGIONID = 125;
const
  PCMS_CLOSINGERROR = 126;
const
  PCMS_NORTENGINE = 127;
const
  PCMS_NODATASERVER = 128;
const
  PCMS_NOACTIVATE = 135;
const
  PCMS_EXPIRED = 136;

type
  Trip = LongInt;

{=************************************************************************* }
type
  PCMServerID = SmallInt;
{+//******************************************************************** }
{-* Detailed driving instruction in a structured format }
{=******************************************************************** }
type
  segmentStruct = record
    stateAbbrev: Array[0..2-1] of Char;
    toll: Integer;
    dir: Array[0..2-1] of Char; {= implies N, S, E, W; additional character for future use }
    route: Array[0..32-1] of Char;
    miles: Integer; {= includes implied decimal }
    minutes: Integer;
    interchange: Array[0..32-1] of Char;
end {segmentStruct};

type
  legInfoType = record
    legMiles: Single;
    totMiles: Single;
    legCost: Single;
    totCost: Single;
    legHours: Single;
    totHours: Single;
end {legInfoType};

{FUNCTIONS}

{+//************************************************************************* }
{-* Current debugging level: }
{-* 0 = no debugging }
{-* 1 = some debugging (currently not used) }
{-* 2 = lots of message boxes about the state of the DLL. }
{-* Returns previous debug level. }
{=************************************************************************* }

function PCMSSetDebug(levl: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Return current debugging level: }
{-* 0 = no debugging }
{-* 1 = some debugging (currently not used) }
{-* 2 = lots of message boxes about the state of the DLL. }
{=************************************************************************* }

function PCMSGetDebug: Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Initialize the server. The arguments are currently not used. In future }
{-* versions they will be used to find resources and define parent windows. }
{-* hAppInst - the calling application's instance handle. May be NULL. }
{-* hWnd - the parent window's window handle. May be NULL. }
{-* Returns a unique ID for this server connection. }
{=************************************************************************* }

function PCMSOpenServer(hAppInst: LongInt;
                        hWnd:  HWND): PCMSERVERID; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Clean up and dismiss the server: }
{-* server - the server's unique ID. Must be passed in to free resources. }
{=************************************************************************* }

function PCMSCloseServer(server: PCMSERVERID): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Check that server is a valid handle. I.e. initialized OK, and in client list. }
{=************************************************************************* }

function PCMSIsValid(server: PCMSERVERID): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Look up a specified error string. Returns number of characters copied into }
{-* buffer, -1 if the string is not found, 0 if buffer arg or size are not OK. }
{=************************************************************************* }

function PCMSGetErrorString(errorCode: Integer; 
                            buffer: PChar;
                            bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Return the error code of the last error the server encountered. }
{-* See the error codes in PCM_DEFS.H for their values. }
{=************************************************************************* }

function PCMSGetError: Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Return a string from the DLL's version resource. }
{-* Returns length of string on success, -1 on error, 0 if string not found. }
{-* See the Windows documentation on VERSIONINFO resources for valid values. }
{=************************************************************************* }

function PCMSAbout(const which: PChar; 
                   buffer: PChar;
                   bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Return the length of a NULL terminated 'C' string to Basic users. }
{=************************************************************************* }

function PCMSStrLen(str: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//************************************************************************* }
{-* Set/get the default region to the given regionID }
{-* Functions return zero on succes, -1 on error. }
{=************************************************************************* }

function PCMSSetDefaultRegion(regionId: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

function PCMSGetDefaultRegion(bufSize: SmallInt; 
                              regionId: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';


{ SIMPLE FUNCTIONS}
function PCMSCalcDistance(serv: PCMSERVERID;
                          const orig: PChar;
                          const dest: PChar): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Calculate distance from orig to dest using a given routing type*/ }

function PCMSCalcDistance2(serv: PCMSERVERID;
                           const orig: PChar; 
                           const dest: PChar;
                           routeType: Integer): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Calculate distance and minutes from orig to dest using given routing type*/ }

function PCMSCalcDistance3(serv: PCMSERVERID;
                           const orig: PChar; 
                           const dest: PChar; 
                           routeType: Integer; 
                           var minutes: LongInt): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Validate a city name or zip code. Returns the number of exact matches.*/ }

function PCMSCheckPlaceName(serv: PCMSERVERID;
                            const cityZip: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// For a given placename, return the corresponding latlong*/ }

function PCMSCityToLatLong(serv: PCMSERVERID;
                           const name: PChar; 
                           buffer: PChar;
                           bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// For a given latlong, return placename*/ }

function PCMSLatLongToCity(serv: PCMSERVERID;
                           const latlong: PChar; 
                           buffer: PChar; 
                           bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//******************************************************************** }
{-* COMPLEX INTERFACE }
{=******************************************************************** }

{+// Return number of region names in region manager list of region names*/ }

function PCMSNumRegions(serv: PCMSERVERID): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Return name for the idx-th region*/ }

function PCMSGetRegionName(serv: PCMSERVERID;
                           idx: Integer; 
                           name: PChar; 
                           bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Create a new PC*MILER trip.*/ }

function PCMSNewTrip(serv: PCMSERVERID): TRIP; far; stdcall; external 'pcmsrv32.dll';

{+// Create a new PC*MILER trip.*/ }

function PCMSNewTripWithRegion(serv: PCMSERVERID;
                               const regionID: PChar): TRIP; far; stdcall; external 'pcmsrv32.dll';

{+// Free up a PC*MILER trip.*/ }

procedure PCMSDeleteTrip(trip: TRIP); far; stdcall; external 'pcmsrv32.dll';

{+// Calculate distance from origin to destination using the trip's settings*/ }

function PCMSCalcTrip(trip: TRIP; 
                      const orig: PChar; 
                      const dest: PChar): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Calculate trip's distance through all the stops using current settings*/ }

function PCMSCalculate(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Optimize the order of stops. Must then calculate the route.*/ }
{+// Return -1 on error, 0 if already optimized, 1 on success*/ }

function PCMSOptimize(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Return duration of trip in minutes.*/ }

function PCMSGetDuration(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Add a stop to the trip's list of stops*/ }

function PCMSAddStop(trip: TRIP; 
                     const stop: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// delete a specified stop*/ }

function PCMSDeleteStop(trip: TRIP; 
                        which: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Clear the trip's list of all stops*/ }

procedure PCMSClearStops(trip: TRIP); far; stdcall; external 'pcmsrv32.dll';

{+// Return a place name from the stop list. Index the list from 0.*/ }
{+// Pass buffer with space for the string, and the buffer's length in bufSize.*/ }

function PCMSGetStop(trip: TRIP; 
                     which: Integer; 
                     buffer: PChar; 
                     bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Return number of stops*/ }

function PCMSNumStops(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Returns the type of the stop. Index the list from 0.*/ }

function PCMSGetStopType(trip: TRIP; 
                         which: Integer; 
                         var typeS: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Set loaded flag for a stop: loaded == true if loaded*/ }
{+// returns 1 on success, 0 on failure*/ }

function PCMSSetLoaded(trip: TRIP; 
                       which: Integer; 
                       loaded: Bool): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Look up a zip or city/state name and returns the number of matching places*/ }
{+// Use PCMSGetMatch() to retrieve each matching place.*/ }
{+// Set easyMatch to 1 to only return exact matches, 0 for partial matches.*/ }

function PCMSLookup(trip: TRIP; 
                    const city: PChar; 
                    easyMatch: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Return place name from last PCMSLookup(). Index list from 0.*/ }
{+// Pass buffer with space for the string, and the buffer's length in bufSize.*/ }

function PCMSGetMatch(trip: TRIP; 
                      which: Integer; 
                      buffer: PChar; 
                      bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Return placename form the last PCMSLookup() in a custom format*/ }

function PCMSGetFmtMatch(trip: TRIP; 
                         which: Integer; 
                         buffer: PChar; 
                         bufSize: Integer; 
                         zipLen: Integer; 
                         cityLen: Integer; 
                         countyLen: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Total number of matches in the last PCMSLookup(). Then index list from 0.*/ }

function PCMSNumMatches(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//******************************************************************** }
{-* REPORT DATA }
{=******************************************************************** }

{+// Saves all the report's text in user-supplied buffer. }
{=Returns length of the report in bytes. }

function PCMSGetRpt(trip: TRIP; 
                    rptNum: Integer; 
                    buffer: PChar; 
                    bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Copy a line from report into the buffer. Index lines from 0.*/ }
{+// Returns number of bytes copied, -1 on error, 0 if report not ready.*/ }

function PCMSGetRptLine(trip: TRIP; 
                        rptNum: Integer; 
                        lineNum: Integer; 
                        buffer: PChar; 
                        bufSize: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get the number of lines in each report.*/ }

function PCMSNumRptLines(trip: TRIP;
                         rptNum: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get total length of the entire report. Use this as the buffer size.*/ }

function PCMSNumRptBytes(trip: TRIP;
                         rptNum: Integer): LongInt; far; stdcall; external 'pcmsrv32.dll';


{+// Saves all the report's text in HTML format in user-supplied buffer. }
{=Returns length of the report in bytes. }

function PCMSGetHTMLRpt(trip: TRIP; 
                        rptNum: Integer; 
                        buffer: PChar; 
                        bufSize: LongInt): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Get total length of the entire report in HTML format. }
{=Use this as the buffer size. }

function PCMSNumHTMLRptBytes(trip: TRIP; 
                             rptNum: Integer): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Gets the number of segments in the detailed report*/ }

function PCMSGetNumSegments(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Gets a segment; if segNum == -1 then the next line is returned else }
{=segment segNum is returned }

function PCMSGetSegment(trip: TRIP;
                        segNum: Integer;
                        var aSegment: SEGMENTSTRUCT): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Gets the legInfo like legMiles, total Miles, legHrs... for a specified leg*/ }

function PCMSGetLegInfo(trip: TRIP; 
                        legNum: Integer; 
                        var pLegInfo: LEGINFOTYPE): Integer; far; stdcall; external 'pcmsrv32.dll';

{+//******************************************************************** }
{-* OPTIONS }
{=******************************************************************** }

{+// Set the criteria used to calculate the minimum distance*/ }
{+// Values are: CALC_PRACTICAL, CALC_SHORTEST, CALC_NATIONAL, CALC_AVOIDTOLL*/ }

procedure PCMSSetCalcType(trip: TRIP; 
                          routeType: Integer); far; stdcall; external 'pcmsrv32.dll';

{+// Return current routing type*/ }

function PCMSGetCalcType(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Run with borders open (true) or closed (false)*/ }

procedure PCMSSetBordersOpen(trip: TRIP; 
                             open: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Set the route flag "add ferry miles" to onOff*/ }

procedure PCMSSetShowFerryMiles(trip: TRIP; 
                                onOff: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Report distances in kilometers*/ }

procedure PCMSSetKilometers(trip: TRIP); far; stdcall; external 'pcmsrv32.dll';

{+// Report distances in miles*/ }

procedure PCMSSetMiles(trip: TRIP); far; stdcall; external 'pcmsrv32.dll';

{+// Do routing in hub mode. I.e. not THROUGH each stop, but TO each stop*/ }

procedure PCMSSetHubMode(trip: TRIP; 
                         onOff: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Set cost per mile*/ }

procedure PCMSSetCost(trip: TRIP; 
                      cost: Integer); far; stdcall; external 'pcmsrv32.dll';

{+// Get cost per mile*/ }

function PCMSGetCost(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// When optimizing, reorder all but the first stop (true),*/ }
{+// or all but the first and last stops (false).*/ }

procedure PCMSSetResequence(trip: TRIP; 
                            changeDest: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Set the trip's current settings from a set of flags.*/ }

procedure PCMSSetOptions(trip: TRIP; 
                         opts: LongInt); far; stdcall; external 'pcmsrv32.dll';

{+// Return all the current settings as a set of flags.*/ }

function PCMSGetOptions(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Return number of legs in the trip.*/ }

function PCMSNumLegs(trip: TRIP): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Reset all options to their defaults*/ }

procedure PCMSDefaults(trip: TRIP); far; stdcall; external 'pcmsrv32.dll';

{+// Sets break hours (input should be in minutes)*/ }

procedure PCMSSetBreakHours(trip: TRIP; 
                            hours: LongInt); far; stdcall; external 'pcmsrv32.dll';

{+// Gets break hours in minutes*/ }

function PCMSGetBreakHours(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Sets break waiting hours (input should be in minutes)*/ }

procedure PCMSSetBreakWaitHours(trip: TRIP; 
                                hours: LongInt); far; stdcall; external 'pcmsrv32.dll';

{+// Gets break waiting hours in minutes*/ }

function PCMSGetBreakWaitHours(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Sets border waiting hours (input should be in minutes)*/ }

procedure PCMSSetBorderWaitHours(trip: TRIP; 
                                 minutes: LongInt); far; stdcall; external 'pcmsrv32.dll';

{+// Gets border waiting hours in minutes*/ }

function PCMSGetBorderWaitHours(trip: TRIP): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Enable/disable on-road latlong geocoding*/ }

procedure PCMSSetOnRoad(trip: TRIP; 
                        onOff: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Run with custom mode on(true) or off(false)*/ }

procedure PCMSSetCustomMode(trip: TRIP; 
                            onOff: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Put state report in alphabetical (true) or in driving order (false).*/ }

procedure PCMSSetAlphaOrder(trip: TRIP; 
                            alphaOrder: Bool); far; stdcall; external 'pcmsrv32.dll';

{+// Favor/avoid all links the trip.*/ }

function PCMSAFLinks(trip: TRIP; 
                     favor: Bool): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// loads current set of avoided/favored links for the default region }
{=return values: -1 - error, 1 - success, 0 - no attribute }

function PCMSAFLoad(server: PCMSERVERID;
                    filename: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// saves current set of avoided/favored links for the default region to a file }
{=return values: -1 - error, 1 - success, 0 - no attribute }

function PCMSAFSave(serverid: PCMSERVERID): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// loads current set of avoided/favored links for the region, if regionID is NULL }
{-saves default region av/fav links; }
{=return values: -1 - error, 1 - success, 0 - no attribute }

function PCMSAFLoadForRegion(serv: PCMSERVERID;
                             filename: PChar; 
                             const regionID: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// saves current set of avoided/favored links for the region to a file, }
{-if regionID is NULL saves default region av/fav links; }
{=return values: -1 - error, 1 - success, 0 - no attribute }

function PCMSAFSaveForRegion(serv: PCMSERVERID;
                             const regionID: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Calculates an ortogonal distance to route from "location"*/ }

function PCMSCalcDistToRoute(trip: TRIP; 
                             location: PChar): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get location on the route 'miles' from the origin.*/ }

function PCMSGetLocAtMiles(trip: TRIP; 
                           miles: LongInt; 
                           pLocation: PChar; 
                           size: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get location on the route 'minutes' from the origin.*/ }

function PCMSGetLocAtMinutes(trip: TRIP; 
                             minutes: LongInt; 
                             pLocation: PChar; 
                             size: Integer): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get location on the route 'miles' from the origin (double latlong[2]).*/ }

function PCMSLatLongAtMiles(trip: TRIP; 
                            miles: LongInt; 
                            latlong: PChar; 
                            useShpPts: Bool): Integer; far; stdcall; external 'pcmsrv32.dll';

{+// Get location on the route 'minutes' from the origin (double latlong[2]).*/ }

function PCMSLatLongAtMinutes(trip: TRIP; 
                              minutes: LongInt; 
                              latlong: PChar; 
                              useShpPts: Bool): Integer; far; stdcall; external 'pcmsrv32.dll';

{ /* returns # of lat/long pairs in latlong buffer or 0 on error*/ }

function PCMSLatLongsEnRoute(trip: TRIP; 
                             var latlong: Double; 
                             numPairs: LongInt; 
                             shpPts: Bool): LongInt; far; stdcall; external 'pcmsrv32.dll';

{+// Set Hazardous Routing options; with HazMat add-on only*/ }

procedure PCMSSetHazOption(trip: TRIP; 
                           hazType: Integer); far; stdcall; external 'pcmsrv32.dll';




implementation

end.
