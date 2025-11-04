using System;
using System.Text;

namespace PCMDLLINT
{
	/// <summary>
	/// Summary description for Class1.
	/// </summary>
	public class PCM
	{
	
		public struct legInfoType
				{
				public	float legMiles;
				public	float totMiles;
				public	float legCost;
				public	float totCost;
				public	float legHours;
				public	float totHours;
				};


	//
		//********************************************************************
		// Programmer's Note:
		//
		// In all the functions below, the following two 'C' arguments must be
		// declared correctly: server IDs and trip IDs. Their types are:
		//	PCMServerID As Short
		//	
		//	Trip As Int
		//	
		//	strings filled with data from ALK functions should be declared as type StringBuilder
		//	
		//	Not all of the functions declared below were tested with a .Net application tester.
		//  	There is a potential for a declaration error due to differences in Win 32 declarations of "C" functions
		//********************************************************************
		//***************************************************
		// FUNCTIONS DECLARATIONS:
		//***************************************************


	// Initialization functions
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSOpenServer", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern short PCMSOpenServer(int appInst, int hWnd);
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSCloseServer", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSCloseServer(short serverID);
	
	// Simple functions
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSCalcDistance", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSCalcDistance(short serverID, string orig, string dest);
	
	// Region functions
	
	// Trip management
	// Declare Function PCMSNewTrip (ByVal serverID as Integer) As Long
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSNewTrip", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSNewTrip(short serverID);
	
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSDeleteTrip", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern void PCMSDeleteTrip(int tripID);

	// Trip options, stops, etc.
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSCalculate", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSCalculate(int tripID);
	
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSAddStop", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSAddStop(int tripID, string stopName);
		
	// Lookup Functions
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSLookup", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSLookup(int tripID, string cityZip, int easy);

	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSGetMatch", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)] 
	public static extern int PCMSGetMatch(int tripID, int index, StringBuilder buffer, int bufLen);
	
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSGetFmtMatch2", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSGetFmtMatch2(int tripID, int index, StringBuilder addrBuffer, int addrLen, StringBuilder cityBuffer, int cityLen, StringBuilder stateBuffer, int stateLen, StringBuilder zipBuffer, int zipLen, StringBuilder countyBuffer, int countyLen);

	// Report functions
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSGetHTMLRpt", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSGetHTMLRpt(int tripID, int rptNum, StringBuilder buffer, int bufLen);
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSNumHTMLRptBytes", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSNumHTMLRptBytes(int tripID, int rptNum);
	
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSNumLegs", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSNumLegs(int tripID);
	
	[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSGetLegInfo", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
	public static extern int PCMSGetLegInfo(int tripID, int indx, ref legInfoType pLegInfo);
	
	// Trip options


	// Extended Trip options
		[System.Runtime.InteropServices.DllImport("PCMSRV32.DLL", EntryPoint="PCMSSetCalcTypeEx", ExactSpelling=false, CharSet=System.Runtime.InteropServices.CharSet.Ansi, SetLastError=true)]
		public static extern void PCMSSetCalcTypeEx(int tripID, int rtType, int optFlags, int vehType);
	
	// Trip Leg information
	
	// Extended functionality

	// Custom Places

	// Special Lists
	
	// Location Radius
	
	// Tolls
	
	// Mileage Caching
	
	// Utility functions
		
	//***************************************************
	// CONSTANTS:
	//***************************************************
	// Old style route types
	internal const short CALC_PRACTICAL = 0;
	internal const short CALC_SHORTEST = 1;
	internal const short CALC_NATIONAL = 2;
	internal const short CALC_AVOIDTOLL = 3;
	internal const short CALC_AIR = 4;
	internal const short CALC_FIFTYTHREE = 6;

	// New style route types
	internal const int CALCEX_TYPE_PRACTICAL = 1;
	internal const int CALCEX_TYPE_SHORTEST = 2;
	internal const int CALCEX_TYPE_AIR = 4;
	internal const int CALCEX_OPT_AVOIDTOLL = 256;
	internal const int CALCEX_OPT_NATIONAL = 512;
	internal const int CALCEX_OPT_FIFTYTHREE = 1024;
	internal const int CALCEX_VEH_TRUCK = 0;
	internal const int CALCEX_VEH_AUTO = 16777216;

	// Report types
	internal const short RPT_DETAIL = 0;
	internal const short RPT_STATE = 1;
	internal const short RPT_MILEAGE = 2;
	internal const short RPT_XML = 3;

	// Toll Mode types
	internal const short TOLL_NONE = 0;
	internal const short TOLL_CASH = 1;
	internal const short TOLL_DISCOUNT = 2;

	// Order of states in reports
	internal const short STATE_ORDER = 1;
	internal const short TRIP_ORDER = 2;

	// Option bits
	internal const int OPTS_NONE = 0X0;
	internal const int OPTS_MILES = 0X1;
	internal const int OPTS_CHANGEDEST = 0X2;
	internal const int OPTS_HUBMODE = 0X4;
	internal const int OPTS_BORDERS = 0X8;
	internal const int OPTS_ALPHAORDER = 0X10;
	internal const int OPTS_HEAVY = 0X20;
	internal const int OPTS_ERROR = 0XFFFF;

	// Error numbers
	internal const short PCMS_INVALIDPTR = 101;
	internal const short PCMS_NOINIFILE = 102;
	internal const short PCMS_LOADINIFILE = 103;
	internal const short PCMS_LOADGEOCODE = 104;
	internal const short PCMS_LOADNETWORK = 105;
	internal const short PCMS_MAXTRIPS = 106;
	internal const short PCMS_INVALIDTRIP = 107;
	internal const short PCMS_INVALIDSERVER = 108;
	internal const short PCMS_BADROOTDIR = 109;
	internal const short PCMS_BADMETANETDIR = 110;
	internal const short PCMS_NOLICENSE = 111;
	internal const short PCMS_TRIPNOTREADY = 112;
	internal const short PCMS_INVALIDPLACE = 113;
	internal const short PCMS_ROUTINGERROR = 114;
	internal const short PCMS_OPTERROR = 115;
	internal const short PCMS_OPTHUB = 116;
	internal const short PCMS_OPT2STOPS = 117;
	internal const short PCMS_OPT3STOPS = 118;
	internal const short PCMS_NOTENOUGHSTOPS = 119;
	internal const short PCMS_BADNETDIR = 120;
	internal const short PCMS_LOADGRIDNET = 121;
	internal const short PCMS_BADOPTIONDIR = 122;
	internal const short PCMS_DISCONNECTEDNET = 123;
	internal const short PCMS_NOTRUCKSTOP = 124;
	internal const short PCMS_INVALIDREGIONID = 125;
	internal const short PCMS_CLOSINGERROR = 126;
	internal const short PCMS_NORTENGINE = 127;
	internal const short PCMS_NODATASERVER = 128;
	internal const short PCMS_NOACTIVATE = 135;
	internal const short PCMS_EXPIRED = 136;
	internal const short PCMS_BADDLLPATH = 137;

	// Convert a 'C' string to a Basic string by resizing it
	// wherever the '\0' character is.
	//
//	internal static string CToBas(ref string s)
//	{
//		return s.Substring(0, PCMSStrLen(s));
//	}
			//
		}
	}
