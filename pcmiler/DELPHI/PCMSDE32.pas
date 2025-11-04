unit PCMSDE32;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  StdCtrls, PCMSTRIP;

type
  TForm1 = class(TForm)
    Label1: TLabel;
    Edit1: TEdit;
    Label2: TLabel;
    Button1: TButton;
    Label3: TLabel;
    Label4: TLabel;
    Button2: TButton;
    ListBox1: TListBox;
    procedure CalcTripClick(Sender: TObject);
    procedure FormLoad(Sender: TObject);
    procedure FormUnload(Sender: TObject);
    procedure TestClick(Sender: TObject);
    procedure Edit1Click(Sender: TObject);

  private
    { Private declarations }
    serverID: Integer;
  public
    { Public declarations }
  end;

var
  Form1: TForm1;

implementation

{$R *.DFM}

function SearchAndReplace(sSrc, sLookFor, sReplaceWith : string ): string;
var
   nPos,
   nLenLookFor : integer;
begin
         nPos        := Pos( sLookFor, sSrc );
         nLenLookFor := Length( sLookFor );
         while(nPos > 0)do
         begin
                Delete( sSrc, nPos, nLenLookFor );
                Insert( sReplaceWith, sSrc, nPos );
                nPos := Pos( sLookFor, sSrc );
         end;
        Result := sSrc;
 end;


procedure TForm1.CalcTripClick(Sender: TObject);
var
   hours: LongInt;
   miles: Extended;

begin
   miles:= PCMSCalcDistance3(serverID, 'Princeton, NJ', 'Chicago, IL', CALC_PRACTICAL, hours) / 10.0;
   Label3.Caption:= FloatToStr(miles) + ' miles';
   Label4.Caption:= IntToStr(hours) + ' minutes';
end;

procedure TForm1.FormLoad(Sender: TObject);
var
   l: Integer;
   buffer: PChar;
   bufStr: String;
begin
   GetMem(buffer, 256);
   serverID := PCMSOpenServer(0, 0);
   if (0 = serverID) then
   begin
       l:= PCMSGetErrorString(PCMSGetError(), buffer, 256);
       bufStr:= StrPas(buffer);
       ShowMessage(bufStr);
   end;
   FreeMem(buffer, 256);
end;

procedure TForm1.FormUnload(Sender: TObject);
var
   retCode: Integer;
begin
     if (0 <> serverID) then
     begin
          retCode:= PCMSCloseServer(serverID);
          serverID:= 0;
     end;
end;

procedure TForm1.TestClick(Sender: TObject);
var
   miles: Extended;
   bufLen: Integer;
   buffer: PChar;
   Testplace1: String;
   Testplace2: String;
   Testplace3: String;
   Testplace4: String;
   Testplace5: String;
   Testplace6: String;
   Testplace7: String;
   Testplace8: String;
   Testplace9: String;
   Testplace10: String;
   Testplace11: String;
   Testplace12: String;
   Testplace13: String;
   lookupplace: String;
   borderstop1: String;
   borderstop2: String;
   ferrystop1: String;
   ferrystop2: String;
   reseqstop1: String;
   reseqstop2: String;
   reseqstop3: String;
   reseqstop4: String;
   avoidplace: String;
   wrongplace1: String;
   wrongplace2: String;
   wrongplace3: String;
   wrongplace4: String;
   wrongplace5: String;
   wrongplace6: String;
   alias: String;
   testzip1: String;
   testzip2: String;
   latlong1: String;
   latlong2: String;
   latlong3: String;
   llplace: String;
   hazplace1: String;
   hazplace2: String;
   splc1: String;
   splc2: String;
   splccity: String;
   canpost1: String;
   canpost2: String;
   tempStr: String;
   bufStr: String;
   ret: Integer;
   shortTrip: LongInt;
   pracTrip: LongInt;
   airTrip: LongInt;
   airTrip1: LongInt;
   i: Integer;
   numStops: Integer;
   sNum: Integer;
   trip: LongInt;
   matches: Integer;
   numRegions: Integer;
   hours: LongInt;

begin
   GetMem(buffer, 1081);
   bufLen := 1081;
   Testplace1:= 'Edmonton, AB';
   Testplace2:= 'Calgary, AB';
   Testplace3:= 'Princeton, NJ';
   Testplace4:= 'Chicago, IL';
   Testplace5:= 'Trenton, NJ';
   Testplace6:= 'San Diego, CA';
   Testplace7:= 'Portland, OR';
   Testplace8:= 'Seattle, WA';
   Testplace9:= 'Denver, CO';
   Testplace10:= 'Aiea, HI';
   Testplace11:= 'Akona, HI';
   Testplace12:= 'Santa Ana, PR';
   Testplace13:= 'San Juan, PR';
   lookupplace := 'PRI*, NJ';
   borderstop1:= 'Detroit, MI';
   borderstop2:= 'Buffalo, NY';
   ferrystop1:= 'Boston, MA';
   ferrystop2:= 'Nantucket, MA';
   reseqstop1:= 'Princeton, NJ';
   reseqstop2:= 'Boston, MA';
   reseqstop3:= 'Hartford, CT';
   reseqstop4:= 'Manchester, NH';
   avoidplace:= 'ALK, NJ';
   wrongplace1:= 'Princeton, NV';
   wrongplace2:= 'Princeto, NJ';
   wrongplace3:= 'Princeto, zz';
   wrongplace4:= 'Pri*, zz';
   wrongplace5:= 'abrakadabra, nj';
   wrongplace6:= '99999';
   alias:= 'Makaka';
   testzip1:= '92014';
   testzip2:= '92020';
   latlong1:= '0402515n,0743340w';
   latlong2:= '40.421n,74.561w';
   latlong3:= '52.5n,92.5w';
   llplace:= 'Princeton, NJ';
   hazplace1:= '59758';
   hazplace2:= 'Bozeman Hot Springs, MT';
   splc1:= 'SPLC568110000';
   splc2:= 'SPLC874430251';
   splccity:= 'SPLCBoston, MA';
   canpost1:= 'M5S 1A1';
   canpost2:= 'A0A 1A0';
   miles := PCMSCalcDistance2(serverID, pChar(testplace3), pChar(testplace4), CALC_AIR) / 10.0;
   tempStr:= 'Air distance from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcDistance3(serverID, pChar(testplace1), pChar(testplace2), CALC_PRACTICAL, hours) / 10.0;
   tempStr:= 'Distance (P) from ';
   tempStr:= tempStr + testplace1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace2;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + IntToStr(hours);
   tempStr:= tempStr + ' minutes';
   ListBox1.Items.Add(tempStr);
   miles := PCMSCalcDistance(serverID, pChar(alias), pChar(testplace4)) / 10.0;
   tempStr:= 'Distance from (alias) ';
   tempStr:= tempStr + alias;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles := PCMSCalcDistance(serverID, pChar(testzip1), pChar(testzip2)) / 10.0;
   tempStr:= 'Distance from ';
   tempStr:= tempStr + testzip1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testzip2;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcDistance(serverID, pChar(testplace3), pChar(testplace4)) / 10.0;
   tempStr:= 'Distance from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcDistance2(serverID, pChar(testplace3), pChar(testplace4), CALC_SHORTEST) / 10.0;
   tempStr:= 'Shortest distance from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcDistance3(serverID, pChar(testplace3), pChar(testplace4), CALC_AVOIDTOLL, hours) / 10.0;
   tempStr:= 'Distance (toll) and hours from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + IntToStr(hours);
   tempStr:= tempStr + ' minutes';
   ListBox1.Items.Add(tempStr);
   ret:= PCMSCityToLatLong(serverID, pChar(llplace), buffer, bufLen);
   bufStr:= StrPas(buffer);
   tempStr:= 'LatLong for ';
   tempStr:= tempStr + llplace;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr +  bufStr;
   ListBox1.Items.Add(tempStr);
   ret:= PCMSLatLongToCity(serverID, pChar(latlong1), buffer, bufLen);
   tempStr:= 'Placename for ';
   tempStr:= tempStr + latlong1;
   tempStr:= tempStr + ' : ';
   bufStr:= StrPas(buffer);
   tempStr:= tempStr + bufStr;
   ListBox1.Items.Add(tempStr);
   shortTrip:= PCMSNewTrip(serverID);
   if (shortTrip = 0) then
   begin
       ret:= PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
       bufStr:= StrPas(buffer);
       tempStr:= tempStr + bufStr;
       ListBox1.Items.Add(tempStr);
   end;
   PCMSSetCalcType(shortTrip, CALC_SHORTEST);
   ret:= PCMSGetCalcType(shortTrip);
   tempStr:= 'Route type: ';
   tempStr:= tempStr + IntToStr(ret);
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcTrip(shortTrip, pChar(testplace3), pChar(testplace6)) / 10.0;
   tempStr:= 'Distance (S) from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace6;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   miles:= PCMSCalcTrip(shortTrip, pChar(canpost1), PChar(canpost2)) / 10.0;
   tempStr:= 'Distance (S) from ';
   tempStr:= tempStr + canpost1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + canpost2;
   tempStr:= tempStr + ' : ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   PCMSSetCalcType(shortTrip, CALC_PRACTICAL);
   miles:= PCMSCalcTrip(shortTrip, pChar(hazplace1), pChar(hazplace2)) / 10.0;
   tempStr:= 'Distance (P) from ';
   tempStr:= tempStr + hazplace1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + hazplace2;
   tempStr:= tempStr + ' (no haz): ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   pracTrip:= PCMSNewTrip(serverID);
   if (pracTrip = 0) then
   begin
        PCMSDeleteTrip(shortTrip);
        tempStr:= 'could not create trip: ';
        PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
        bufStr:= StrPas(buffer);
        tempStr:= tempStr + bufStr;
        ListBox1.Items.Add(tempStr);
   end;
   PCMSSetCalcType(pracTrip, CALC_PRACTICAL);
   PCMSSetKilometers(pracTrip);
   PCMSAddStop(pracTrip, pChar(testplace3));
   PCMSAddStop(pracTrip, pChar(testplace4));
   PCMSAddStop(pracTrip, pChar(testplace6));
   miles:= PCMSCalculate(pracTrip) / 10.0;
   tempStr:= 'Practical route (';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + testplace6;
   tempStr:= tempStr + ') in km: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSSetMiles(pracTrip);
   PCMSSetCost(pracTrip, 10);
   PCMSCalculate(pracTrip);
   miles:= PCMSGetCost(pracTrip)/10.0;
   tempStr:= 'Cost: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   airTrip:= PCMSNewTrip(serverID);
   PCMSSetCalcType(airTrip, CALC_PRACTICAL);
   PCMSSetOnRoad(airTrip, false);
   miles:= PCMSCalcTrip(airTrip, pChar(latlong1), pChar(latlong3))/ 10.0;
   tempStr:= 'Practical route (';
   tempStr:= tempStr+ latlong1;
   tempStr:= tempStr + '), (';
   tempStr:= tempStr + latlong3;
   tempStr:= tempStr + ' (off road): ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   airTrip1:= PCMSNewTrip(serverID);
   PCMSSetCalcType(airTrip1, CALC_PRACTICAL);
   PCMSSetOnRoad(airTrip1, true);
   miles:= PCMSCalcTrip(airTrip1, pChar(latlong1), pChar(latlong3)) / 10.0;
   tempStr:= 'Practical route(';
   tempStr:= tempStr+ latlong1;
   tempStr:= tempStr + '), (';
   tempStr:= tempStr + latlong3;
   tempStr:= tempStr + '), ';
   tempStr:= tempStr + ' (on road): ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   tempStr:= '**********************************';
   ListBox1.Items.Add(tempStr);
   ret:= PCMSNumRptLines(pracTrip, RPT_DETAIL);
   if (0 < ret) then
   begin
       for i:= 0 to (ret - 1) do
       begin
           miles:= PCMSGetRptLine(pracTrip, RPT_DETAIL, i, buffer, bufLen);
           tempStr:=StrPas(buffer);
           tempStr:= SearchAndReplace(tempStr, #9, ' ');
           ListBox1.Items.Add(tempStr);
       end;
   end;
   PCMSSetAlphaOrder(pracTrip, false);
   tempStr:= '**********************************';
   ListBox1.Items.Add(tempStr);
   ret:= PCMSNumRptLines(pracTrip, RPT_STATE);
   if (0 < ret) then
   begin
       for i:= 0 to (ret - 1) do
       begin
           miles:= PCMSGetRptLine(pracTrip, RPT_STATE, i, buffer, bufLen);
           tempStr:= StrPas(buffer);
           tempStr:= SearchAndReplace(tempStr, #9, ' ');
           ListBox1.Items.Add(tempStr);
       end;
   end;
   PCMSSetAlphaOrder(pracTrip, true);
   tempStr:= '**********************************';
   ListBox1.Items.Add(tempStr);
   ret:= PCMSNumRptLines(pracTrip, RPT_STATE);
   if (0 < ret) then
   begin
       for i:= 0 to (ret - 1) do
       begin
           miles:= PCMSGetRptLine(pracTrip, RPT_STATE, i, buffer, bufLen);
           tempStr:= StrPas(buffer);
           tempStr:= SearchAndReplace(tempStr, #9, ' ');
           ListBox1.Items.Add(tempStr);
       end;
   end;
   tempStr:= '************************************';
   ListBox1.Items.Add(tempStr);
   ret:= PCMSNumRptLines(pracTrip, RPT_MILEAGE);
   if (0 < ret) then
   begin
       for i:= 0 to (ret - 1) do
       begin
           miles:= PCMSGetRptLine(pracTrip, RPT_MILEAGE, i, buffer, bufLen);
           tempStr:= StrPas(buffer);
           tempStr:= SearchAndReplace(tempStr, #9, ' ');
           ListBox1.Items.Add(tempStr);
       end;
   end;
   miles:= PCMSCalcDistance(serverID, pChar(testplace10), pChar(testplace11)) / 10.0;
   tempStr:= 'distance from ';
   tempStr:= tempStr + testplace10;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr +  testplace11;
   tempStr:= tempStr + ': ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   if (miles < 0) then
   begin
       PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   PCMSSetCalcType(shortTrip, CALC_SHORTEST);
   miles:= PCMSCalcTrip (shortTrip, pChar(testplace3), pChar(testplace12)) / 10.0;
   tempStr:= 'distance from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace12;
   tempStr:= tempStr + ': ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   if (miles < 0) then
   begin
       PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   miles:= PCMSCalcTrip(shortTrip, pChar(testplace3), pChar(testplace4))/ 10.0;
   tempStr:= 'distance from ';
   tempStr := tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + testplace4;
   tempStr:= tempStr + ': ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   if (miles < 0) then
   begin
       PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, PCHar(testPlace7));
   PCMSAddStop(pracTrip, PChar(testplace8));
   PCMSAddStop(pracTrip, PChar(testPlace9));
   PCMSSetKilometers(pracTrip);
   miles:= PCMSCalculate(pracTrip)/ 10.0;
   tempStr:= 'Distance between ';
   tempStr:= tempStr + testplace7;
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + testplace8;
   tempStr:= tempStr + ', ';
   tempStr:= tempStr + testplace9;
   tempStr:= tempStr + ' (Practical KM): ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSSetMiles(pracTrip);
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, PChar(reseqStop1));
   PCMSAddStop(pracTrip, PCHar(reseqStop2));
   PCMSAddStop(pracTrip, PChar(reseqStop3));
   PCMSSetResequence(pracTrip, true);
   PCMSOptimize(pracTrip);
   tempStr:= '*******Optimized trip***************';
   ListBox1.Items.Add(tempStr);
   numStops:= PCMSNumStops(pracTrip);
   for sNum:= 0 to (numStops - 1) do
   begin
       miles:= PCMSGetStop(pracTrip, sNum, buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, PChar(reseqstop1));
   PCMSAddStop(pracTrip, PChar(reseqstop2));
   PCMSAddStop(pracTrip, PChar(reseqstop3));
   PCMSAddStop(pracTrip, PChar(reseqStop4));
   PCMSSetResequence(pracTrip, false);
   PCMSOptimize(pracTrip);
   tempStr:= '*********Oprimized trip**************';
   ListBox1.Items.Add(tempStr);
   numStops:= PCMSNumStops(pracTrip);
   for sNum:= 0 to (numStops - 1) do
   begin
       miles:= PCMSGetStop(pracTrip, sNum, buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   PCMSSetHubMode(pracTrip, true);
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= 'Hub Mode distance: ';
   tempStr:= tempStr + FloatToStr(miles);
   tempStr:= tempStr + ' miles';
   ListBox1.Items.Add(tempStr);
   PCMSSetHubMode(pracTrip, false);
   hours:= PCMSGetBreakHours(pracTrip);
   tempStr:= 'Break time (min): ';
   tempStr:= tempStr + IntToStr(hours);
   ListBox1.Items.Add(tempStr);
   hours:= PCMSGetBreakWaitHours(pracTrip);
   tempStr:= 'Break time (min): ';
   tempStr:= tempStr + IntToStr(hours);
   ListBox1.Items.Add(tempStr);
   PCMSCalculate(pracTrip);
   hours:= PCMSGetDuration(pracTrip);
   tempStr:= 'Trip time (min): ';
   tempStr:= tempStr + IntToStr(hours);
   ListBox1.Items.Add(tempStr);
   PCMSSetBreakHours(pracTrip, 120);
   PCMSSetBreakWaitHours(pracTrip, 120);
   PCMSCalculate(pracTrip);
   hours:= PCMSGetDuration(pracTrip);
   tempStr:= 'New trip time (min): ';
   tempStr:= tempStr + IntToStr(hours);
   tempStr:= tempStr + ' ';
   ListBox1.Items.Add(tempStr);
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, pChar(borderstop1));
   PCMSAddStop(pracTrip, pChar(borderstop2));
   PCMSSetBordersOpen(pracTrip, true);
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= borderstop1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + borderstop2;
   tempStr:= tempStr + ', borders open: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSSetBordersOpen(pracTrip, false);
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= borderstop1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + borderstop2;
   tempStr:= tempStr + ', borders closed: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, pChar(ferryStop1));
   PCMSAddStop(pracTrip, pChar(ferrystop2));
   PCMSSetShowFerryMiles(pracTrip, true);
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= ferrystop1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + ferrystop2;
   tempStr:= tempStr + ', ferry on: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSSetShowFerryMiles(pracTrip, false);
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= ferrystop1;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + ferrystop2;
   tempStr:= tempStr + ', ferry off: ';
   tempStr:= tempStr + FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   PCMSClearStops(pracTrip);
   PCMSAddStop(pracTrip, pChar(testplace3));
   PCMSAddStop(pracTrip, pChar(avoidplace));
   miles:= PCMSCalculate(pracTrip)/10.0;
   tempStr:= 'Normal distance from ';
   tempStr:= tempStr + testplace3;
   tempStr:= tempStr + ' to ';
   tempStr:= tempStr + avoidplace;
   tempStr:= tempStr + ': ';
   tempStr:= tempStr+  FloatToStr(miles);
   ListBox1.Items.Add(tempStr);
   matches:= PCMSCheckPlaceName(serverID, pChar(lookupplace));
   if (matches > 0) then
   begin
       tempStr:= lookupplace;
       tempStr:= tempStr + ' exists in the database';
       ListBox1.Items.Add(tempStr);
   end
   else
   begin
       tempStr:= lookupplace;
       tempStr:= tempStr + ' does not exist in the database';
       ListBox1.Items.Add(tempStr);
   end;
   trip:= PCMSNewTrip(serverID);
   if (trip <= 0) then
   begin
       PCMSGetErrorString(PCMSGetError(), buffer, bufLen);
       tempStr:= 'Error creating trip: ';
       tempStr:= tempStr + StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   if (trip > 0) then
   begin
       matches := PCMSLookup(trip, pChar(latlong1), 1);
       if (matches > 0) then
       begin
           PCMSGetFmtMatch(trip, 0, buffer, bufLen, 10, 20, 20);
           tempStr:= StrPas(buffer);
           ListBox1.Items.Add(tempStr);
       end;
       matches:= PCMSLookup(trip, PChar(alias), 1);
       if (matches > 0) then
       begin
           PCMSGetFmtMatch(trip, 0, buffer, bufLen, 10,20,20);
           tempStr:= StrPas(buffer);
           ListBox1.Items.Add(tempStr);
       end;
   end;
   matches:= PCMSLookup(trip, pChar(lookupplace), 1);
   tempStr:= IntToStr(matches);
   tempStr:= tempStr + ' (exactly) matching cities to ';
   tempStr:= tempStr + lookupplace;
   ListBox1.Items.Add(tempStr);
   if (matches > 0) then
   begin
       PCMSGetMatch(trip, 0, buffer, 100);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   matches:= PCMSLookup(trip, pChar(lookupplace), 2);
   tempStr:= IntToStr(matches);
   tempStr:= tempStr + ' matching (by default) cities to ';
   tempStr:= tempStr + lookupplace;
   ListBox1.Items.Add(tempStr);
   if (matches > 0) then
   begin
       PCMSGetMatch(trip, 0, buffer, 100);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
   end;
   matches:= PCMSLookup(trip, pChar(lookupplace), 0);
   tempStr:= IntToStr(matches);
   tempStr:= tempStr + ' matching (partially) cities to ';
   tempStr:= tempStr + lookupplace;
   ListBox1.Items.Add(tempStr);
   if (matches > 0) then
   begin
       for i:= 0 to (matches - 1) do
       begin
           PCMSGetMatch(trip, i, buffer, bufLen);
           tempStr:= '';
           tempStr:= tempStr + StrPas(buffer);
           tempStr:= tempStr + '';
           ListBox1.Items.Add(tempStr);
       end;
   end;
   matches:= PCMSLookup(trip, pChar(splccity), 0);
   tempStr:= IntToStr(matches);
   tempStr:= tempStr + ' matching (partially) cities to ';
   tempStr:= tempStr + splccity;
   ListBox1.Items.Add(tempStr);
   if (matches > 0) then
   begin
       for i:= 0 to (matches - 1) do
       begin
           PCMSGetMatch(trip, i, buffer, bufLen);
           tempStr:= '';
           tempStr:= tempStr + StrPas(buffer);
           tempStr:= tempStr + '';
           ListBox1.Items.Add(tempStr);
       end;
   end;
   tempStr:= 'Lookup a non-existing city ';
   ListBox1.Items.Add(tempStr);
   matches:= PCMSLookup(trip, pChar(wrongplace1), 1);
   tempStr:= wrongplace1;
   tempStr:= tempStr + ' -> ';
   tempStr:= tempStr + IntToStr(matches);
   tempStr:= tempStr + ' cities matching (exactly), ';
   matches := PCMSLookup(trip, pChar(wrongplace1), 0);
   tempStr:= tempStr + IntToStr(matches);
   tempStr:= tempStr + ' matching (partially), ';
   matches:= PCMSLookup(trip, pChar(wrongplace1), 2);
   tempStr:= tempStr + IntToStr(matches);
   tempStr:= tempStr  + ' matching (by default)';
   ListBox1.Items.Add(tempStr);
   PCMSDeleteTrip(trip);
   tempStr:= '*****************************************';
   ListBox1.Items.Add(tempStr);
   numRegions:= PCMSNumRegions(serverID);
   tempStr:= 'Number of installed regions: ';
   tempStr:= tempStr + IntToStr(numRegions);
   ListBox1.Items.Add(tempStr);
   while (numRegions <> 0) do
   begin
       PCMSGetRegionName(serverID, numRegions - 1, buffer, bufLen);
       tempStr:= StrPas(buffer);
       ListBox1.Items.Add(tempStr);
       numRegions:= numRegions - 1;
   end;
   tempStr:= ' ';
   ListBox1.Items.Add(tempStr);
   FreeMem(buffer, 1081);
end;

procedure TForm1.Edit1Click(Sender: TObject);
var
   buffer: PChar;
   temp: String;
   tripID: LongInt;
   ret: Integer;
   l: Integer;
begin
   GetMem(buffer, 256);
   tripID:= PCMSNewTrip(serverID);
   ret:= PCMSLookup(tripID, 'PRINCE*, NJ', 0); // to return partial matches
   if (0 < ret) then
   begin
       l:= PCMSGetMatch(tripID, 0, buffer, 256);
       Edit1.Text:= buffer;
   end
   else
       Edit1.Text:= 'No Match';
   PCMSDeleteTrip(tripID);
   FreeMem(buffer, 256);
end;

end.
