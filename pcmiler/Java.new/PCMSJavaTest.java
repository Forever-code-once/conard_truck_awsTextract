import java.io.*;
import alk.connect.*;
import alk.connect.results.*;

public class PCMSJavaTest{

	// Defines
	static final int EF_Null			= 1;
	static final int EF_Fatal			= 2;
	static final int EF_ErrorExpected	= 4;

	static final int RF_Lines			= 8;
	static final int RF_Bytes			= 16;
	static final int RF_Html			= 32;

	static final int LF_UseMatch		= 64;
	static final int LF_UseFmtMatch		= 128;
	static final int LF_UseFmtMatch2	= 256;

	static final String HOME = "08518";
	static final String WORK = "08540";

	static final String HOME_LL = "0400649N,0744816W";
	static final String WORK_LL = "0402055N,0743932W";

	static final String HOME_ADDR = "08518; 317 E. 5th St.";
	static final String WORK_ADDR = "08540; 1000 Herrontown Rd";

	static final String HOME_ALIAS = "Home";
	static final String WORK_ALIAS = "Work";

	// class-wide PCMSJava instance
	static PCMSJava hPCMSJava = new PCMSJava();

    public static void main(String[] args)
	{

        ///////////////////////////////////////////////////////////////////////////
        NewSection("Starting test...");

        int ret				= -1;
		long dist			= -1;
		long tripID			= -1;

		short serverID		= hPCMSJava.OpenServer(0, 0);
		String str			= new String();
		StringBuffer strBuf = new StringBuffer();
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetDebug, SetDebug");

			int orig = hPCMSJava.GetDebug();
			System.out.println("Current debug level: " + hPCMSJava.GetDebug());
			hPCMSJava.SetDebug(orig + 1);
			System.out.println("Set orig level plus one: " + hPCMSJava.GetDebug());
			hPCMSJava.SetDebug(orig);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("IsValid");

			TestErr(hPCMSJava.IsValid((short)323534), "IsValid(badID)", EF_ErrorExpected);
			TestErr(hPCMSJava.IsValid((short)10000), "IsValid(10000) -- not yet open", EF_ErrorExpected);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("About");

			StringBuffer strProdName = new StringBuffer();
			StringBuffer strProdVer  = new StringBuffer();

			TestErr(hPCMSJava.About("ProductName", strProdName), "About(\"ProductName\")", EF_Null);
        	TestErr(hPCMSJava.About("ProductVersion", strProdVer), "About(\"ProductVersion\")", EF_Null);

			System.out.println("  " + strProdName + " " + strProdVer + "\n");
        }
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetDefaultRegion, NumRegions, GetRegionName, SetDefaultRegion");

			hPCMSJava.GetDefaultRegion(strBuf);
			System.out.println("Default Region: " + strBuf);

			System.out.println("Installed Regions: ");
			int nRegions = hPCMSJava.NumRegions(serverID);
			for (int iRegion = 0; iRegion < nRegions; ++iRegion)
			{
				hPCMSJava.GetRegionName(serverID, iRegion, strBuf);
				System.out.println("  " + iRegion + ") " + strBuf);
			}
			System.out.println("");

			// Add 1 when testing DefaultRegion calls since they return 0 on success
			TestErr(hPCMSJava.SetDefaultRegion("Europe") + 1, "Failed to set region to Europe", EF_ErrorExpected);
			TestErr(hPCMSJava.SetDefaultRegion("NA") + 1, "Failed to set region to NA", EF_Null);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CalcDistance");

			dist = hPCMSJava.CalcDistance(serverID, "08518", "08540");
			TestErr((int)dist, "PCMSCalcDistance(serverID, \"08518\", \"08540\")", EF_Null);
			System.out.println("  Distance from home to work (zip to zip): " + dist/10.0f);

			dist = hPCMSJava.CalcDistance(serverID, "08518; 317 E 5th St", "08540; 1000 Herrontown Rd");
			TestErr((int)dist, "PCMSCalcDistance(serverID, \"08518; 317 E 5th St\", \"08540; 1000 Herrontown Rd\")", EF_Null);
			System.out.println("  Distance from home to work (addr to addr): " + dist/10.0f);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CalcDistance2");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 0);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 0)", EF_Null);
			System.out.println("  Home to work (rtType == 0 (Pra)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 1);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 1)", EF_Null);
			System.out.println("  Home to work (rtType == 1 (Sho)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 2);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 2)", EF_Null);
			System.out.println("  Home to work (rtType == 2 (Nat)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 3);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 3)", EF_Null);
			System.out.println("  Home to work (rtType == 3 (Tol)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 4);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 4)", EF_Null);
			System.out.println("  Home to work (rtType == 4 (Air)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 5);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 5)", EF_Null);
			System.out.println("  Home to work (rtType == 5 (POV)): " + dist/10.0f + " miles");

			dist = hPCMSJava.CalcDistance2(serverID, HOME, WORK, 6);
			TestErr((int)dist, "CalcDistance2(serverID, HOME, WORK, 6)", EF_Null);
			System.out.println("  Home to work (rtType == 6 (53)): " + dist/10.0f + " miles");
        }
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CalcDistance3");

			long arr[] = hPCMSJava.CalcDistance3(serverID, HOME, WORK, 0);

			dist = arr[0];
			long min = arr[1];

			TestErr((int)dist, "CalcDistance3(serverID, HOME, WORK, 0)", EF_Null);
			System.out.println("  Home to work: " + dist/10.0f + " miles, " + min + " minutes");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("NewTripWithRegion (with DeleteTrip)");

			tripID = hPCMSJava.NewTripWithRegion(serverID, "Europe");
			TestErr((int)tripID, "NewTripWithRegion with Europe", EF_ErrorExpected);
			hPCMSJava.DeleteTrip(tripID);
			tripID = -1;

			tripID = hPCMSJava.NewTripWithRegion(serverID, "NA");
			TestErr((int)tripID, "NewTripWithRegion with NA", EF_Fatal);
			hPCMSJava.DeleteTrip(tripID);
			tripID = -1;
		}

		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("NewTrip");

			tripID = hPCMSJava.NewTrip(serverID);
			TestErr((int)tripID, "NewTrip", EF_Fatal);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CalcTrip");

			dist = hPCMSJava.CalcTrip(tripID, HOME, WORK);
			TestErr((int)dist, "CalcTrip(tripID, HOME, WORK)", EF_Null);
			System.out.println("  Home to work: " + dist/10.0f + " miles");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("NumStops, GetStop, GetStopType");
			DumpStops(tripID);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("AddStop, DeleteStop");

			System.out.println("  Deleting stop 1 (should be 08540)...");
			hPCMSJava.DeleteStop(tripID, 1);
			DumpStops(tripID);

			System.out.println("  Adding 12345...");
			hPCMSJava.AddStop(tripID, "12345");
			DumpStops(tripID);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("Calculate, GetDuration");

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0f + " miles, " + hPCMSJava.GetDuration(tripID) + " minutes");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("ClearStops");

			hPCMSJava.ClearStops(tripID);
			DumpStops(tripID);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("Optimize");

			System.out.println("  Creating a trip: 08540 -> 90210 -> 12345 -> 08518...");
			hPCMSJava.AddStop(tripID, "08540");
			hPCMSJava.AddStop(tripID, "90210");
			hPCMSJava.AddStop(tripID, "12345");
			hPCMSJava.AddStop(tripID, "08518");

	/*
			System.out println("  Creating a trip w/ addrs...");
			PCMSAddStop(tripID, "08540; 1000 Herrontown Rd");
			PCMSAddStop(tripID, "90210");
			PCMSAddStop(tripID, "12345");
			PCMSAddStop(tripID, "08518; 317 E 5th St");
	*/

			System.out.println("  " + hPCMSJava.Calculate(tripID)/10.0f + " miles, " + hPCMSJava.GetDuration(tripID) + " minutes");
			DumpStops(tripID);

			System.out.println("  Optimizing...");

			hPCMSJava.SetResequence(tripID, false);
			ret = hPCMSJava.Optimize(tripID);
			TestErr(ret, "Optimize(tripID)", EF_Null);

			if (ret > 0)
			{
				System.out.println("  " + hPCMSJava.Calculate(tripID)/10.0f + " miles, " + hPCMSJava.GetDuration(tripID) + " minutes (no change dest)");
				DumpStops(tripID);
			}

			hPCMSJava.SetResequence(tripID, true);
			ret = hPCMSJava.Optimize(tripID);
			TestErr(ret, "Optimize(tripID)", EF_Null);

			if (ret > 0)
			{
				System.out.println("  " + hPCMSJava.Calculate(tripID)/10.0f + " miles, " + hPCMSJava.GetDuration(tripID) + " minutes (change dest)");
				DumpStops(tripID);
			}

			System.out.println("  Removing two stops and trying to resequence...");
			hPCMSJava.DeleteStop(tripID, 0);
			hPCMSJava.DeleteStop(tripID, 0);
			ret = hPCMSJava.Optimize(tripID);
			TestErr(ret, "Optimize", EF_ErrorExpected);

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);
			hPCMSJava.AddStop(tripID, "12345");
			hPCMSJava.Calculate(tripID);
		}
		
		/*
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("NumPOICategories, POICategoryName, LocRadLookup, GetLocRadItem");

			int nCategories = hPCMSJava.NumPOICategories(serverID);
			TestErr(nCategories, "NumPOICategoriesf", EF_Null);
			System.out.println("  " + nCategories + "total POI Categories:");

			for(int i = 0; i < nCategories; i++)
			{
				ret = hPCMSJava.POICategoryName(serverID, i, strBuf);
				TestErr(ret, "POICategoryname", EF_Null);
				System.out.println("    " + strBuf);
			}

			System.out.println("");
			int nItems = hPCMSJava.LocRadLookup(tripID, "08540", 10, true, true, true, true, 10);
			TestErr(nItems, "LocRadLookup (with all parameters true and POI category 10", EF_Null);

			for(int i = 0; i < nItems;  i++)
			{
				ret = hPCMSJava.GetLocRadItem(tripID, i, strBuf);
				TestErr(ret, "GetLocRadItem", EF_Null);
				System.out.println("    " + strBuf);
			}
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetRptLine, NumRptLines");

			DumpReport(tripID, 0, RF_Lines);
			System.out.println("");
			DumpReport(tripID, 1, RF_Lines);
			System.out.println("");
			DumpReport(tripID, 2, RF_Lines);
			System.out.println("");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetRpt, NumRptBytes");

			DumpReport(tripID, 0, RF_Bytes);
			System.out.println("");
			DumpReport(tripID, 1, RF_Bytes);
			System.out.println("");
			DumpReport(tripID, 2, RF_Bytes);
			System.out.println("");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetHTMLRpt, NumHTMLRptBytes");

			DumpReport(tripID, 0, RF_Html);
			System.out.println("");
			DumpReport(tripID, 1, RF_Html);
			System.out.println("");
			DumpReport(tripID, 2, RF_Html);
			System.out.println("");
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetLegInfo, NumLegs");

			int nLegs = hPCMSJava.NumLegs(tripID);
			if (TestErr(nLegs, "PCMSNumLegs", EF_Null))
			{
				int colWidth = 16;
				System.out.println(		  pad("Leg Cost", colWidth)
										+ pad("Leg Hours", colWidth)
										+ pad("Leg Miles", colWidth)
										+ pad("Tot Cost", colWidth)
										+ pad("Tot Hours", colWidth)
										+ pad("Tot Miles", colWidth));

				for (int iLeg = 0; iLeg < nLegs; ++iLeg)
				{
					LegInfoType legInfo = new LegInfoType();

					ret = hPCMSJava.GetLegInfo(tripID, iLeg, legInfo);
					if (TestErr(ret, "GetLegInfo", EF_Null)) // GetLegInfo returns 0 on success
					{
						System.out.println(		  padF(legInfo.legCost, colWidth)
												+ padF(legInfo.legHours, colWidth)
												+ padF(legInfo.legMiles, colWidth)
												+ padF(legInfo.totCost, colWidth)
												+ padF(legInfo.totHours, colWidth)
												+ padF(legInfo.totMiles, colWidth));
					}
				}
			}
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetSegment, GetNumSegments");

			int nSegments = hPCMSJava.GetNumSegments(tripID);
			if (TestErr(nSegments, "GetNumSegments", EF_Null))
			{
				System.out.println(		  pad("State", 6)
										+ pad("Toll", 5)
										+ pad("Dir", 4)
										+ pad("Route", 33)
										+ pad("Miles", 8)
										+ pad("Min", 8)
										+ pad("Interchange", 33));

				for (int iSegment = 0; iSegment < nSegments; ++iSegment)
				{
					SegmentStruct seg = new SegmentStruct();

					ret = hPCMSJava.GetSegment(tripID, iSegment, seg);
                    if (TestErr(ret, "GetSegment", EF_Null))
					{
						System.out.println(		  pad (seg.stateAbbrev.toString(), 6)
												+ padF(seg.toll, 5)
												+ pad (seg.dir.toString(), 4)
												+ pad (seg.route.toString(), 33)
												+ padF((float)((seg.miles)/10.0), 8)
												+ padF(seg.minutes, 8)
												+ pad (seg.interchange.toString(), 33));
					}
				}
	        }
		}

*/
		///////////////////////////////////////////////////////////////////////////
		{
        NewSection("Lookup, NumMatches, GetMatch");

			TestLookup(tripID, "PRI*,NJ", 0, LF_UseMatch);
			TestLookup(tripID, "PRI*,NJ", 1, LF_UseMatch);
			TestLookup(tripID, "PRI*,NJ", 2, LF_UseMatch);

			TestLookup(tripID, "Boston,MA", 0, LF_UseMatch);
			TestLookup(tripID, "Boston,MA", 1, LF_UseMatch);
			TestLookup(tripID, "Boston,MA", 2, LF_UseMatch);

			TestLookup(tripID, "abc123", 0, LF_UseMatch);
			TestLookup(tripID, "abc123", 1, LF_UseMatch);
			TestLookup(tripID, "abc123", 2, LF_UseMatch);

			TestLookup(tripID, "SPLCBoston, MA", 0, LF_UseMatch);
			TestLookup(tripID, "SPLC1110", 0, LF_UseMatch);

			TestLookup(tripID, "08540; 1000 Herrontown Rd", 0, LF_UseMatch);
			TestLookup(tripID, "08540; 1000 Herrontown Rd", 1, LF_UseMatch);
			TestLookup(tripID, "08540; 1000 Herrontown Rd", 2, LF_UseMatch);

			TestLookup(tripID, "08540; Herrontown", 0, LF_UseMatch);
			TestLookup(tripID, "08540; Herrontown", 1, LF_UseMatch);
			TestLookup(tripID, "08540; Herrontown", 2, LF_UseMatch);

			TestLookup(tripID, "Florence, NJ; 317 E 5th St", 0, LF_UseMatch);
			TestLookup(tripID, "Florence, NJ; 317 E 5th St", 1, LF_UseMatch);
			TestLookup(tripID, "Florence, NJ; 317 E 5th St", 2, LF_UseMatch);

			TestLookup(tripID, "SPLCBoston, MA; 1 Broad St", 0, LF_UseMatch);

			TestLookup(tripID, "Home", 0, LF_UseMatch);
			TestLookup(tripID, "abcdef, xx; 1 main st", 0, LF_UseMatch);

   
     }
		///////////////////////////////////////////////////////////////////////////

		{
			NewSection("GetFmtMatch");
			TestLookup(tripID, "PRI*,NJ", 0, LF_UseFmtMatch);
			TestLookup(tripID, HOME_LL, 0, LF_UseFmtMatch);
		}

		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("PCMSCheckPlaceName");

			TestCheckPlaceName(serverID, "08518");
			TestCheckPlaceName(serverID, "BOS, MA");
			TestCheckPlaceName(serverID, "Bosto, MA");
			TestCheckPlaceName(serverID, "Boston, MA");
			TestCheckPlaceName(serverID, "Pri,NJ");
			TestCheckPlaceName(serverID, "Pri*,NJ");
			TestCheckPlaceName(serverID, "abcdef,gh");
			TestCheckPlaceName(serverID, "abc123");
/*
			TestCheckPlaceName(serverID, "08518; 317 e 5th st");
			TestCheckPlaceName(serverID, "08540; Herrontown");
			TestCheckPlaceName(serverID, "xxxx");
*/
		}
		///////////////////////////////////////////////////////////////////////////
		{
	        NewSection("PCMSGetFmtMatch2");

			TestLookup(tripID, "PRI*,NJ", 0, LF_UseFmtMatch2);
/*
			TestLookup(tripID, "08518; 317 E 5th St", 0, LF_UseFmtMatch2);
*/
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("GetLocAtMiles, GetLocAtMinutes, LatLongAtMiles, LatLongAtMinutes");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);
			dist = hPCMSJava.Calculate(tripID);

			ret = hPCMSJava.GetLocAtMiles(tripID, 10, strBuf);
			if (TestErr(ret, "GetLocAtMiles", EF_Null))
				System.out.println("  Location after one mile: " + strBuf);

			ret = hPCMSJava.GetLocAtMinutes(tripID, 1, strBuf);
			if (TestErr(ret, "GetLocAtMinutes", EF_Null))
				System.out.println("  Location after one minute: " + strBuf);

			ret = hPCMSJava.LatLongAtMiles(tripID, 0, strBuf, true);
			if (TestErr(ret, "LatLongAtMiles", EF_Null))
				System.out.println("  LatLong at start: " + strBuf);

			ret = hPCMSJava.LatLongAtMiles(tripID, 3, strBuf, true);
			if (TestErr(ret, "LatLongAtMiles", EF_Null))
				System.out.println("  LatLong at .3 miles: " + strBuf);

			ret = hPCMSJava.LatLongAtMiles(tripID, 10, strBuf, true);
			if (TestErr(ret, "LatLongAtMiles", EF_Null))
				System.out.println("  LatLong after 1 mile: " + strBuf);

			ret = hPCMSJava.LatLongAtMiles(tripID, 100, strBuf, true);
			if (TestErr(ret, "LatLongAtMiles", EF_Null))
				System.out.println("  LatLong after 10 miles: " + strBuf);

			ret = hPCMSJava.LatLongAtMiles(tripID, dist, strBuf, true);
			if (TestErr(ret, "LatLongAtMiles", EF_Null))
				System.out.println("  LatLong at end: " + strBuf);

			ret = hPCMSJava.LatLongAtMinutes(tripID, 1, strBuf, true);
			if (TestErr(ret, "LatLongAtMinutes", EF_Null))
				System.out.println("  LatLong after one minute: " + strBuf);

		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("LatLongsEnRoute");

			long lRet = hPCMSJava.NumLatLongsEnRoute(tripID, true);

			if (TestErr((int)lRet, "NumLatLongsEnRoute", EF_Null))
			{
				double[] pLatLongs = hPCMSJava.LatLongsEnRoute(tripID, lRet, true);

				if(0 < pLatLongs.length)
					ret = 1;
				else
					ret = 0;

				TestErr(ret, "LatLongsEnRoute", EF_Null);

				System.out.println("  " + lRet + " latlongs found...");

				int iLL = 0;
				if (lRet > 10)
				{
					for (iLL = 0; iLL < 5; ++iLL)
						System.out.println("  " + pLatLongs[2*iLL+0] + "N, " + -pLatLongs[2*iLL+1] + "W");
					System.out.println("  " + "...");
					for (iLL = (int)(lRet - 5); iLL < lRet; ++iLL)
						System.out.println("  " + pLatLongs[2*iLL+0] + "N, " + -pLatLongs[2*iLL+1] + "W");
				}
				else
				{
					for (iLL = 0; iLL < lRet; ++iLL)
						System.out.println("  " + pLatLongs[2*iLL+0] + "N, " + -pLatLongs[2*iLL+1] + "W");
				}
			}
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CalcDistToRoute");

			dist = hPCMSJava.CalcDistToRoute(tripID, "Trenton, NJ");
			if (TestErr((int)dist, "PCMSCalcDistToRoute", EF_Null))
				System.out.println("  Distance from Trenton to route: " + dist/10.0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("AirDistToRte");

			dist = hPCMSJava.AirDistToRte(tripID, "Trenton, NJ", 0);
			if (TestErr((int)dist, "AirDistToRte", EF_Null))
				System.out.println("  Distance from Trenton to route: " + dist/10.0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("AddressToLatLong, LatLongToAddress");

			StringBuffer strLatLong = new StringBuffer();
			ret = hPCMSJava.AddressToLatLong(serverID, HOME_ADDR, strLatLong);
			if (TestErr(ret, "AddressToLatLong", EF_Null))
				System.out.println("  Home (addr) to LatLong: " + strLatLong);
			else
				strLatLong.append("40.1176N,74.8013W"); // replace it with a known latlong, in order to test PCMSLatLongToAddress

			ret = hPCMSJava.LatLongToAddress(serverID, strLatLong.toString(), strBuf);
			if (TestErr(ret, "LatLongToAddress", EF_Null))
				System.out.println("  " + strLatLong + " to addr: " + strBuf);

			strBuf.delete(0, strBuf.length());
			strLatLong.delete(0, strLatLong.length());
			strLatLong.append("40N,75W");

			ret = hPCMSJava.LatLongToAddress(serverID, strLatLong.toString(), strBuf);
			if (TestErr(ret, "LatLongToAddress", EF_Null))
				System.out.println("  " + strLatLong + " to city: " + strBuf);

			strBuf.delete(0, strBuf.length());
			ret = hPCMSJava.LatLongToAddress(serverID, "Makaka", strBuf);
			if (TestErr(ret, "LatLongToAddress", EF_Null))
				System.out.println("  Makaka to Address: " + strBuf);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("CityToLatLong, LatLongToCity");

			strBuf.delete(0, strBuf.length());
			StringBuffer strLatLong = new StringBuffer();

			ret = hPCMSJava.CityToLatLong(serverID, HOME, strLatLong);
			if (TestErr(ret, "CityToLatLong", EF_Null))
				System.out.println("  Home (city) to LatLong: " + strLatLong);
			else
				strLatLong.append("40.1176N,74.8013W"); // replace it with a known latlong, in order to test LatLongToCity

			ret = hPCMSJava.LatLongToCity(serverID, strLatLong.toString(), strBuf);
			if (TestErr(ret, "LatLongToCity", EF_Null))
				System.out.println("  " + strLatLong + " to city: " + strBuf);

			strLatLong.delete(0, strLatLong.length());
			ret = hPCMSJava.CityToLatLong(serverID, WORK, strLatLong);
			if (TestErr(ret, "CityToLatLong", EF_Null))
				System.out.println("  Work (city) to LatLong: " + strLatLong);
			else
				strLatLong.append("40.1176N,74.8013W"); // replace it with a known latlong, in order to test LatLongToCity

			strBuf.delete(0, strBuf.length());
			ret = hPCMSJava.LatLongToCity(serverID, strLatLong.toString(), strBuf);
			if (TestErr(ret, "LatLongToCity", EF_Null))
				System.out.println("  " + strLatLong + " to city: " + strBuf);

			strLatLong.delete(0, strLatLong.length());
			strLatLong.append("40N,75W");
			ret = hPCMSJava.LatLongToCity(serverID, strLatLong.toString(), strBuf);
			if (TestErr(ret, "LatLongToCity", EF_Null))
				System.out.println("  " + strLatLong + " to city: " + strBuf);

			strLatLong.delete(0, strLatLong.length());
			ret = hPCMSJava.CityToLatLong(serverID, "Makaka", strLatLong);
			if (TestErr(ret, "CityToLatLong", EF_Null))
				System.out.println("  Makaka to LatLong: " + strLatLong);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetCustomMode");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);

			hPCMSJava.SetCustomMode(tripID, false);
			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (custom mode false)");

			hPCMSJava.SetCustomMode(tripID, true);
			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (custom mode true)");
			System.out.println("  " + "* Requires a link from 08518 -> 08540 to be avoided");

			hPCMSJava.SetCustomMode(tripID, false);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetCalcType, GetCalcType");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);

			int eval = -1;

			hPCMSJava.SetCalcType(tripID, 0);
			if(0 == hPCMSJava.GetCalcType(tripID)) eval = 1;
			TestErr(eval, "SetCalcType/GetCalcType", EF_Null);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (practical)");

			hPCMSJava.SetCalcType(tripID, 1);
			ret = hPCMSJava.GetCalcType(tripID);
			if(1 == ret) eval = 1; else eval = -1;
			TestErr(eval, "SetCalcType/GetCalcType", EF_Null);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (shortest)");

			hPCMSJava.SetCalcType(tripID, 0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetCalcTypeEx, GetCalcTypeEx");

			CalcTypeEx origCalcTypeEx = new CalcTypeEx();
			CalcTypeEx calcTypeEx = new CalcTypeEx();

			ret = hPCMSJava.GetCalcTypeEx(tripID, origCalcTypeEx);
			TestErr(ret, "GetCalcTypeEx(tripID, origCalcTypeEx)", EF_Null);
			System.out.println("Original options: " + origCalcTypeEx.rtType + " | " + origCalcTypeEx.optFlags + " | " + origCalcTypeEx.vehType);

			calcTypeEx.rtType		= 12345;
			calcTypeEx.optFlags		= 12345;
			calcTypeEx.vehType		= 12345;

			hPCMSJava.SetCalcTypeEx(tripID, calcTypeEx);

			ret = hPCMSJava.GetCalcTypeEx(tripID, calcTypeEx);
			TestErr(ret, "SetCalcTypeEx/GetCalcTypeEx", EF_Null);
			System.out.println("New options: " + calcTypeEx.rtType + " | " + calcTypeEx.optFlags + " | " + calcTypeEx.vehType);

			hPCMSJava.SetCalcTypeEx(tripID, origCalcTypeEx);
			ret = hPCMSJava.GetCalcTypeEx(tripID, calcTypeEx);
			TestErr(ret, "SetCalcTypeEx/GetCalcTypeEx", EF_Null);
			System.out.println("Restore options: " + calcTypeEx.rtType + " | " + calcTypeEx.optFlags + " | " + calcTypeEx.vehType);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetBreakHours, GetBreakHours");

			int eval = -1;

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);

			hPCMSJava.SetBreakHours(tripID, 0);
			hPCMSJava.SetBreakWaitHours(tripID, 0);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + hPCMSJava.GetDuration(tripID) + " minutes (no breaks)");

			hPCMSJava.SetBreakHours(tripID, 15);
			if(15 == hPCMSJava.GetBreakHours(tripID)) eval = 1;
			TestErr(eval, "SetBreakHours/GetBreakHours", EF_Null);

			hPCMSJava.SetBreakWaitHours(tripID, 30);
			if(30 == hPCMSJava.GetBreakWaitHours(tripID)) eval = 1; else eval = 0;
			TestErr(eval, "SetBreakWaitHours/GetBreakWaitHours", EF_Null);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + hPCMSJava.GetDuration(tripID) + " minutes (30 min break every 15 min)");

			hPCMSJava.SetBreakHours(tripID, 0);
			hPCMSJava.SetBreakWaitHours(tripID, 0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetBorderWaitHours, GetBorderWaitHours");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "Sheshatshit, NF");
			hPCMSJava.AddStop(tripID, "Poop, MX");

			hPCMSJava.SetBorderWaitHours(tripID, 0);

			int eval = -1;

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + hPCMSJava.GetDuration(tripID) + " minutes (no border wait)");

			hPCMSJava.SetBorderWaitHours(tripID, 30);
			if(30 == hPCMSJava.GetBorderWaitHours(tripID)) eval = 1;
			TestErr(eval, "SetBorderWaitHours/GetBorderWaitHours", EF_Null);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + hPCMSJava.GetDuration(tripID) + " minutes (two 30 min border waits)");

			hPCMSJava.SetBorderWaitHours(tripID, 0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetAlphaOrder");

			System.out.println("-- Default Order --");
			hPCMSJava.Defaults(tripID);
			hPCMSJava.Calculate(tripID);
			DumpReport(tripID, 1, RF_Lines);

			System.out.println("-- State Order --");
			hPCMSJava.SetAlphaOrder(tripID, true);
			hPCMSJava.Calculate(tripID);
			DumpReport(tripID, 1, RF_Lines);

			System.out.println("-- Route Order --");
			hPCMSJava.SetAlphaOrder(tripID, false);
			hPCMSJava.Calculate(tripID);
			DumpReport(tripID, 1, RF_Lines);

			hPCMSJava.SetAlphaOrder(tripID, true);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetBordersOpen");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "Buffalo, NY");
			hPCMSJava.AddStop(tripID, "Detroit, MI");

			hPCMSJava.SetBordersOpen(tripID, true);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (borders open)");

			hPCMSJava.SetBordersOpen(tripID, false);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("  " + dist/10.0 + " miles (borders closed)");

			hPCMSJava.SetBordersOpen(tripID, true);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetShowFerryMiles");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "02554");
			hPCMSJava.AddStop(tripID, "02205");

			hPCMSJava.SetShowFerryMiles(tripID, true);

			long withFerryMiles = hPCMSJava.Calculate(tripID);
			TestErr((int)withFerryMiles, "Calculate(tripID)", EF_Null);
			System.out.println("  " + withFerryMiles/10.0 + " miles (ferry miles)");

			hPCMSJava.SetShowFerryMiles(tripID, false);

			long noFerryMiles = hPCMSJava.Calculate(tripID);
			TestErr((int)noFerryMiles, "Calculate(tripID)", EF_Null);
			System.out.println("  " + noFerryMiles/10.0 + " miles (no ferry miles)");

			int eval = (withFerryMiles == noFerryMiles) ? 0 : 1;
			TestErr(eval, "SetShowFerryMiles", EF_Null);

			hPCMSJava.SetShowFerryMiles(tripID, true);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetMiles, SetKilometers");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);

			hPCMSJava.SetMiles(tripID);

			long distMiles = hPCMSJava.Calculate(tripID);
			TestErr((int)distMiles, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distMiles/10.0 + " miles");

			hPCMSJava.SetKilometers(tripID);

			long distKM = hPCMSJava.Calculate(tripID);
			TestErr((int)distKM, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distKM/10.0 + " KM");

			int eval = (distMiles == distKM) ? 0 : 1;
			TestErr(eval, "SetKilometers", EF_Null);

			hPCMSJava.SetMiles(tripID);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetHazOption");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "Black Butte Ranch, MT");
			hPCMSJava.AddStop(tripID, "West Yellowstone, MT");

			hPCMSJava.SetHazOption(tripID, 0);

			long distBefore = hPCMSJava.Calculate(tripID);
			TestErr((int)distBefore, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distBefore/10.0 + " miles (no haz)");

			hPCMSJava.SetHazOption(tripID, 1);

			long distAfter = hPCMSJava.Calculate(tripID);
			TestErr((int)distAfter, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distAfter/10.0 + " miles (haz)");

			int eval = (distBefore == distAfter) ? 0 : 1;
			TestErr(eval, "SetHazOption", EF_Null);

			hPCMSJava.SetHazOption(tripID, 0);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetHubMode");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);
			hPCMSJava.AddStop(tripID, "12345");

			hPCMSJava.SetHubMode(tripID, false);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("-- Hub mode off --");
			DumpReport(tripID, 2, RF_Lines);

			hPCMSJava.SetHubMode(tripID, true);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("-- Hub mode on --");
			DumpReport(tripID, 2, RF_Lines);

			hPCMSJava.SetHubMode(tripID, false);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetCost, GetCost");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);

			int origCost = hPCMSJava.GetCost(tripID);

			hPCMSJava.SetCost(tripID, 100);
			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);

			System.out.println("-- Cost $1.00/mile --");
			DumpReport(tripID, 2, RF_Lines);

			hPCMSJava.SetCost(tripID, 1000);
			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);

			System.out.println("-- Cost $10.00/mile --");
			DumpReport(tripID, 2, RF_Lines);

			hPCMSJava.SetCost(tripID, origCost);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetLoaded");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "08518");
			hPCMSJava.AddStop(tripID, "08016");
			hPCMSJava.AddStop(tripID, "08518");

			hPCMSJava.SetLoaded(tripID, 1, false);
			hPCMSJava.SetLoaded(tripID, 2, true);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "hPCMSJava.Calculate(tripID)", EF_Null);

			System.out.println("-- Stop 1 Unloaded, Stop 2 Loaded --");
			DumpReport(tripID, 0, RF_Lines);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("Defaults");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "08540");
			hPCMSJava.AddStop(tripID, "90210");

			hPCMSJava.SetCustomMode(tripID, true);
			hPCMSJava.SetAlphaOrder(tripID, false);
			hPCMSJava.SetBorderWaitHours(tripID, 4*60);
			hPCMSJava.SetBreakHours(tripID, 47);
			hPCMSJava.SetBreakWaitHours(tripID, 71);
			hPCMSJava.SetCalcType(tripID, 3);
			hPCMSJava.SetKilometers(tripID);
			hPCMSJava.SetShowFerryMiles(tripID, false);
			hPCMSJava.SetCost(tripID, 10000);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("-- tripID with all sorts of funky options --");
			DumpReport(tripID, 2, RF_Lines);

			hPCMSJava.Defaults(tripID);

			dist = hPCMSJava.Calculate(tripID);
			TestErr((int)dist, "Calculate(tripID)", EF_Null);
			System.out.println("-- tripID after hPCMSJava.Defaults --");
			DumpReport(tripID, 2, RF_Lines);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetOptions, GetOptions");

			System.out.println("  " + "Start: " + hPCMSJava.GetOptions(tripID));

			hPCMSJava.SetOptions(tripID, 0);
			System.out.println("  " + "All Off: " + hPCMSJava.GetOptions(tripID));

			hPCMSJava.SetOptions(tripID, 0xFFFFFFFF);
			System.out.println("  " + "All On: " + hPCMSJava.GetOptions(tripID));

			hPCMSJava.Defaults(tripID);
			System.out.println("  " + "Defaults: " + hPCMSJava.GetOptions(tripID));
		}

		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetVehicleType");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, "08016");
			hPCMSJava.AddStop(tripID, "19007");

			hPCMSJava.SetVehicleType(tripID, true);
			long distHeavy = hPCMSJava.Calculate(tripID);
			TestErr((int)distHeavy, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distHeavy/10.0 + " miles (heavy)");

			hPCMSJava.SetVehicleType(tripID, false);
			long distLight = hPCMSJava.Calculate(tripID);
			TestErr((int)distLight, "Calculate(tripID)", EF_Null);
			System.out.println("  " + distLight/10.0 + " miles (light)");

			int eval = (distHeavy == distLight) ? 0: 1;
			TestErr(eval, "SetVehicleType", EF_Null);

			hPCMSJava.SetVehicleType(tripID, true);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetRoadNameOnly");

			System.out.println(" Road Name Only = false");
			hPCMSJava.SetRoadNameOnly(tripID, false);
			TestLookup(tripID, "08540; 15000 Herrontown Rd", 0, LF_UseMatch);

			System.out.println(" Road Name Only = true");
			hPCMSJava.SetRoadNameOnly(tripID, true);
			TestLookup(tripID, "08540; 15000 Herrontown Rd", 0, LF_UseMatch);

			hPCMSJava.SetRoadNameOnly(tripID, false);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("SetExactLevel, GetExactLevel");

			System.out.println(" Exact Level = 100");
			hPCMSJava.SetExactLevel(serverID, 100);
			int eval = (100 == hPCMSJava.GetExactLevel(serverID)) ? 1 : 0;
			TestErr(eval, "SetExactLevel(serverID)", EF_Null);
			TestLookup(tripID, "08518; 317 E 5th Rd", 1, LF_UseMatch);

			System.out.println(" Exact Level = 85");
			hPCMSJava.SetExactLevel(serverID, 85);
			eval = (85 == hPCMSJava.GetExactLevel(serverID)) ? 1 : 0;
			TestErr(eval, "hPCMSJava.SetExactLevel(serverID)", EF_Null);
			TestLookup(tripID, "08518; 317 E 5th Rd", 1, LF_UseMatch);

			hPCMSJava.SetExactLevel(serverID, 100);
		}

		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("TranslateAlias, General Alias functionality");

			System.out.println("  " + "* Requires aliases named " + HOME_ALIAS + " and " + WORK_ALIAS + ".");

			System.out.println("Translate Alias Off");
			hPCMSJava.TranslateAlias(tripID, false);

			TestLookup(tripID, HOME_ALIAS, 1, LF_UseMatch);
			System.out.println("");

			TestLookup(tripID, WORK_ALIAS, 1, LF_UseFmtMatch);
			System.out.println("");
			System.out.println("");

			System.out.println("Translate Alias On");
			hPCMSJava.TranslateAlias(tripID, true);

			TestLookup(tripID, HOME_ALIAS, 1, LF_UseMatch);
			System.out.println("");

			TestLookup(tripID, WORK_ALIAS, 1, LF_UseFmtMatch);
			System.out.println("");
			System.out.println("");

			System.out.println("Run route and display report w/ aliases");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.AddStop(tripID, HOME_ALIAS);
			hPCMSJava.AddStop(tripID, WORK_ALIAS);
			hPCMSJava.Calculate(tripID);

			DumpReport(tripID, 2, RF_Lines);
		}
		///////////////////////////////////////////////////////////////////////////
		{
			NewSection("AFLinks");

			hPCMSJava.ClearStops(tripID);
			hPCMSJava.Defaults(tripID);

			hPCMSJava.AddStop(tripID, HOME);
			hPCMSJava.AddStop(tripID, WORK);
			System.out.println(hPCMSJava.Calculate(tripID)/10.0 + " miles before.");

			hPCMSJava.AFLinks(tripID, false);
			System.out.println(hPCMSJava.Calculate(tripID)/10.0 + " miles after.");
		}
		///////////////////////////////////////////////////////////////////////////
        {
			NewSection("DeleteTrip");

			hPCMSJava.DeleteTrip(tripID);
			tripID = -1;
		}
		//////////////////////////////////////////////////////////////////////////////
		{
			NewSection("CloseServer");

			ret = hPCMSJava.CloseServer(serverID);
			TestErr(ret, "CloseServer", EF_Null);
		}
    }

	/**************************************************************************
	* PCMSJavaTest Utility Functions
	**************************************************************************/
    public static String pad(String s, int len)
    {
        StringBuffer buf = new StringBuffer();

        int slen = s.length();
        if (len > slen)
        {
            int diff = len - s.length();
            for(int i = 0; i < diff; i++)
            {
                buf.append(" ");
            }
        }
        buf.append(s);

        return buf.toString();
    }

    public static String padF(float f, int len)
    {
        StringBuffer s = new StringBuffer();

		s.append(f);
        int slen = s.length();
		if(len > slen)
		{
			s.delete(0, slen);

			int diff = len - slen;
			for(int i = 0; i < diff; i++)
			{
				s.append(" ");
			}

			s.append(f);
		}

        return s.toString();
    }

	private static boolean TestErr(int bTest, String strFunc, int flags)
	{
		StringBuffer buf = new StringBuffer("");

		if (0 < bTest) // no error occurred
		{
			if(1 == (flags & EF_ErrorExpected))
			{
				System.out.println("***** NO ERROR/Error expected -- " + strFunc + "*****");
				return false;
			}
		}
		else // error occurred
		{
			hPCMSJava.GetErrorString(hPCMSJava.GetError(), buf);

			if(1 == (EF_ErrorExpected & flags))
			{
				System.out.println("  Error (expected) -- " + strFunc + " -- " + buf);
			}
			else
			{
				System.out.println("***** ERROR -- " + strFunc + " -- " + buf + " *****");
				return false;
			}

			if(1 == (EF_Fatal & flags))
			{
//				throw PCMException();
			}
		}
		return true;
	}

	private static void DumpReport(long tripID, int rptType, int rptFlags)
	{
		System.out.println(" -- RptType: " + rptType + " ---------------------------------------------------");

		StringBuffer strBuf = new StringBuffer();

		switch(rptFlags)
		{
			case RF_Lines:
			{
				int nLines = hPCMSJava.NumRptLines(tripID, rptType);
				if (TestErr(nLines, "NumRptLines", EF_Null))
				{
					for (int iLine = 0; iLine < nLines; ++iLine)
					{
						int ret = hPCMSJava.GetRptLine(tripID, rptType, iLine, strBuf);
						TestErr(ret + 1, "GetRptLine", EF_Null); // TestErr trips on 0 vals
						System.out.println("  " + strBuf);
					}
				}
				break;
			}

		case RF_Bytes:
			{
				long nBytes = hPCMSJava.NumRptBytes(tripID, rptType);
				if (TestErr((int)nBytes, "NumRptBytes", EF_Null))
				{
					int ret = hPCMSJava.GetRpt(tripID, rptType, strBuf);
					if (TestErr(ret, "GetRpt", EF_Null))
						System.out.println(strBuf);
				}
				break;
			}

		case RF_Html:
			{
				long nBytes = hPCMSJava.NumHTMLRptBytes(tripID, rptType);
				if (TestErr((int)nBytes, "NumHTMLRptBytes", EF_Null))
				{
					long ret = hPCMSJava.GetHTMLRpt(tripID, rptType, strBuf);
					if (TestErr((int)ret, "GetHTMLRpt", EF_Null))
					{
						String strFile = "";
						switch (rptType)
						{
							case 0:
								strFile = "detail.htm";
								break;
							case 1:
								strFile = "state.htm";
								break;
							case 2:
								strFile = "mile.htm";
								break;
						}

						try
						{
							BufferedWriter out = new BufferedWriter(new FileWriter(strFile));
							out.write( strBuf.toString() );
							out.flush();
							out.close();
						}
						catch(IOException e)
						{
							System.out.println(e.getMessage());
						}
					}
				}

				break;
			}

		default:
			System.out.println("***** INTERNAL TESTER ERROR -- invalid report type! *****");
		}
	}

	private static void TestLookup(long tripID, String strLookup, int easyMatch, int flag)
	{
		StringBuffer strBuf = new StringBuffer();
		int nMatches = hPCMSJava.Lookup(tripID, strLookup, easyMatch);

		System.out.println(" -- Looking up " + strLookup + ", LookupType: " + easyMatch + ", " +
							nMatches + " found  ---------------------------------");

		if (TestErr(nMatches + 1, "Lookup", EF_Null))
		{
			if (nMatches > 0)
			{
				for (int iMatch = 0; iMatch < nMatches; ++iMatch)
				{
					switch (flag)
					{
						case LF_UseMatch:
						{
							hPCMSJava.GetMatch(tripID, iMatch, strBuf);
							break;
						}
						case LF_UseFmtMatch:
						{
							hPCMSJava.GetFmtMatch(tripID, iMatch, strBuf, 8, 32, 32);
							break;
						}
						case LF_UseFmtMatch2:
						{
							StringBuffer addr = new StringBuffer();
							StringBuffer city = new StringBuffer();
							StringBuffer state = new StringBuffer();
							StringBuffer zip = new StringBuffer();
							StringBuffer county = new StringBuffer();

							hPCMSJava.GetFmtMatch2(tripID, iMatch, addr, city, state, zip, county);
							strBuf.append(zip + "|" + city + "|" + state + "|" + county + "|" + addr);
							break;
						}
						default:
						{
							strBuf.append("  ***** INTERNAL TESTER ERROR -- invalid lookup type! *****");
						}
					}
					System.out.println(strBuf);
					strBuf.delete(0, strBuf.length());
				}
			}
			else
			{
				System.out.println("  NO MATCHES FOUND!");
			}
		}
	}

	private static void TestCheckPlaceName(short serverID, String strPlace)
	{
		int nMatches = hPCMSJava.CheckPlaceName(serverID, strPlace);
		TestErr(nMatches, "CheckPlaceName", EF_Null);
		System.out.println("  " + strPlace + " -> " + nMatches + " match" + (1 == nMatches ? "" : "es"));
		System.out.println("");
	}

	private static void DumpStops(long tripID)
	{
		StringBuffer strBuf = new StringBuffer();

		System.out.println("  Dumping stops...");
		int nStops = hPCMSJava.NumStops(tripID);
		for (int iStop = 0; iStop < nStops; ++iStop)
		{
			hPCMSJava.GetStop(tripID, iStop, strBuf);
			int type = hPCMSJava.GetStopType(tripID, iStop);
			System.out.println("    " + iStop + ") " + strBuf + " (" + type + ")");
		}
	}

	private static void NewSection(String str)
	{
		System.out.println("");
		System.out.println("<!--  " + str + "  -->\n");
	}
}
