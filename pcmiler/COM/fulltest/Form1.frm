VERSION 5.00
Begin VB.Form Form1 
   Caption         =   "COM Server Tester"
   ClientHeight    =   8175
   ClientLeft      =   60
   ClientTop       =   345
   ClientWidth     =   12105
   LinkTopic       =   "Form1"
   ScaleHeight     =   8175
   ScaleWidth      =   12105
   StartUpPosition =   3  'Windows Default
   Begin VB.ListBox List1 
      Height          =   6690
      Left            =   240
      TabIndex        =   1
      Top             =   1320
      Width           =   11655
   End
   Begin VB.CommandButton test 
      Caption         =   "Test"
      Height          =   975
      Left            =   3240
      TabIndex        =   0
      Top             =   120
      Width           =   2895
   End
End
Attribute VB_Name = "Form1"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
    Dim pracTrip As Object
    Dim shortTrip As Object
    Dim endTrip As Object
    Dim LegReport As Object
    Dim ml As Boolean
    Dim alpha As Boolean
    Dim hubMode As Boolean
    Dim Options As Object
    Dim Report As Object
    Dim pcmsrv As Object

Private Sub Form_Load()
Set pcmsrv = CreateObject("PCMServer.PCMServer")
    
If pcmsrv.ID <= 0 Then
    MsgBox ("Error: Creating pcmserver object")
    Exit Sub
End If

End Sub
Private Sub Form_Unload(Cancel As Integer)
Set pcmsrv = Nothing
End Sub

Private Sub test_Click()
    Dim buflen As Integer
    buflen = 800
    Dim distance
    Dim buffer As String * 1080
    Dim divider As String
    divider = "*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************"
    Dim Testplace1 As String
    Testplace1 = "Edmonton, AB"
    Dim Testplace2 As String
    Testplace2 = "Calgary, AB"
    Dim Testplace3 As String
    Testplace3 = "Princeton, NJ"
    Dim Testplace4 As String
    Testplace4 = "Chicago, IL"
    Dim Testplace5 As String
    Testplace5 = "Trenton, NJ"
    Dim Testplace6 As String
    Testplace6 = "San Diego, CA"
    Dim Testplace7 As String
    Testplace7 = "Portland, OR"
    Dim Testplace8 As String
    Testplace8 = "Seattle, WA"
    Dim Testplace9 As String
    Testplace9 = "Denver, CO"
    Dim Testplace10 As String
    Testplace10 = "Aiea, HI"
    Dim Testplace11 As String
    Testplace11 = "Akona, HI"
    Dim Testplace12 As String
    Testplace12 = "Santa Ana, PR"
    Dim Testplace13 As String
    Testplace13 = "San Juan, PR"
    Dim lookupplace As String
    lookupplace = "PRI*, NJ"
    Dim alias1 As String
    alias1 = "Home"
    Dim alias2 As String
    alias2 = "Name13"
    Dim alias3 As String
    alias3 = "Name14"
    Dim testzip1 As String
    testzip1 = "92014"
    Dim testzip2 As String
    testzip2 = "92020"
    Dim latlong1 As String
    latlong1 = "0402515n,0743340w"
    Dim latlong2 As String
    latlong2 = "40.421n,74.561w"
    Dim latlong3 As String
    latlong3 = "52.5n,92.5w"
    Dim llplace As String
    llplace = "Princeton, NJ"
    Dim hazplace1 As String
    hazplace1 = "59758"
    Dim hazplace2 As String
    hazplace2 = "Bozeman Hot Springs, MT"
    Dim splc1 As String
    splc1 = "SPLC568110000"
    Dim splc2 As String
    splc2 = "SPLC874430251"
    Dim splccity As String
    splccity = "SPLCBoston, MA"
    Dim canpost1 As String
    canpost1 = "M5S 1A1"
    Dim canpost2 As String
    canpost2 = "A0A 1A0"
    Dim ferrystart As String
    ferrystart = "Green Creek, NJ"
    Dim ferryend As String
    ferryend = "Lewes, DE"
    Dim tempStr As String '* 1000
    Dim errorStr As String
    Dim time As Long
    Dim regionName As String
    regionName = "NA"
    Dim reportNum As Long
    
    ' Routing calculation types
    Dim CALC_PRACTICAL As Integer
    CALC_PRACTICAL = 0
    Dim CALC_SHORTEST As Integer
    CALC_SHORTEST = 1
    Dim CALC_AVOIDTOLL As Integer
    CALC_AVOIDTOLL = 3
    Dim CALC_AIR As Integer
    CALC_AIR = 4
    
    ' Report types
    Dim RPT_DETAIL As Integer
    RPT_DETAIL = 0
    Dim RPT_STATE As Integer
    RPT_STATE = 1
    Dim RPT_MILEAGE As Integer
    RPT_MILEAGE = 2

    '****************************************************************
    'Start Section 1
    '****************************************************************
    tempStr = "**Start Section 1**"
    List1.AddItem (tempStr)
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '********************
    distance = -1
    '********************
    
    '********************
    'Get Debug Level:
    Dim serverDebugLevel As Integer

    Err.Clear
    On Error Resume Next
    
    serverDebugLevel = pcmsrv.DebugLevel
    tempStr = "Server Debug Level: "
    tempStr = tempStr + CStr(serverDebugLevel)
    
    If Err Then
    errorStr = "Error :pcmsrv.DebugLevel: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If

    '********************
    'Set Debug Level:
    Err.Clear
    On Error Resume Next
    serverDebugLevel = CStr("1")
    pcmsrv.DebugLevel = serverDebugLevel
    tempStr = "set pcmsrv.DebugLevel "

    If Err Then
    errorStr = "Error setting pcmsrv.DebugLevel: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If
    
    '*********************
    'Get Default Region
    Dim defRegion
    
    Err.Clear
    On Error Resume Next
    defRegion = pcmsrv.DefaultRegion
    tempStr = "Default Region: "
    tempStr = tempStr + CStr(defRegion)
    
    If Err Then
    errorStr = "Error pcmsrv.DefaultRegion: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If

    '*********************
    'Set Default Region
    Err.Clear
    On Error Resume Next
    pcmsrv.DefaultRegion = defRegion
    tempStr = "Set Default Region "
    If Err Then
    errorStr = "Error pcmsrv.DefaultRegion: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If
    
    '**********************
    'get Number of Regions
    
    Dim Regions As Integer
    
    Err.Clear
    On Error Resume Next
    Regions = pcmsrv.NumRegions()
    tempStr = "Number of Regions: "
    tempStr = tempStr + CStr(Regions)
    
    If Err Then
    errorStr = "Error pcmsrv.Regions(): "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get product version
    Dim prodVersion
    Err.Clear
    On Error Resume Next
    prodVersion = pcmsrv.ProductVersion()
    tempStr = prodVersion
    tempStr = "ProductVersion " + prodVersion
    
    If Err Then
    errorStr = prodVersion
    errorStr = errorStr + " ERROR in ProductVersion"
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
       
    '*********************
    'look up a place in the database
    Dim testplace
    testplace = "Princeton, NJ"
    
    Err.Clear
    On Error Resume Next
    ret = 0
    ret = pcmsrv.CheckPlaceName(testplace)
    tempStr = "Result of CheckPlaceName for " & testplace & " : number of matching places = " & ret
    If Err Then
    errorStr = testplace + " does not exist in the database: ERROR in CheckPlaceName "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If

    'look up a place in the database
    testplace = "PRI*, NJ"
    Err.Clear

    'On Error Resume Next
    ret = 0
    ret = pcmsrv.CheckPlaceName(testplace)
    tempStr = "Result of CheckPlaceName for " & testplace & " : number of matching places = " & ret
    If Err Then
    errorStr = testplace + " does not exist in the database: ERROR in CheckPlaceName "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If

    'look up a place in the database
    testplace = "ddddd, NJ"
    
    Err.Clear
    On Error Resume Next
    ret = 0
    ret = pcmsrv.CheckPlaceName(testplace)
    tempStr = "Result of CheckPlaceName for " & testplace & " : number of matching places = " & ret
    If Err Then
    errorStr = testplace + " does not exist in the database: ERROR in CheckPlaceName "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    Else
    List1.AddItem (tempStr)
    End If
         
    '*****************************
    'Run some simple routes, returning distance:
    '*****************************
     
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(Testplace1, Testplace3) / 10#
   
    tempStr = "Distance from "
    tempStr = tempStr + Testplace1
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    'if error:
    If Err Then
    errorStr = "CalcDistance(Testplace1, Testplace3): error "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    'otherwise add the results of the calculation
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(alias1, Testplace3) / 10#
    tempStr = "Distance from "
    tempStr = tempStr + alias1
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(alias1, Testplace3): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    Err.Clear
    On Error Resume Next
    List1.AddItem (" CalcDistance " & testzip1 & "  " & testzip2)

    distance = pcmsrv.CalcDistance(testzip1, testzip2) / 10#
    tempStr = "Distance from "
    tempStr = tempStr + testzip1
    tempStr = tempStr + " to "
    tempStr = tempStr + testzip2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(testzip1, testzip2): error "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(Testplace5, Testplace3) / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace5
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "PCMSCalcDistance(Testplace5, Testplace3): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'run a simple trip using the default routing calculation type
    '(note that the distance is returned in 10ths of miles)
    '*****************************
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(Testplace3, Testplace4) / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "PCMSCalcDistance(Testplace3, Testplace4): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'run a simple trip using the shortest distance calculation
    '*****************************
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance2(Testplace3, Testplace4, CALC_SHORTEST) / 10#
    tempStr = "Shortest distance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance2(Testplace3, Testplace4, CALC_SHORTEST): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'run a trip avoiding tolls, and output duration of trip in minutes
    '*****************************
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance3(Testplace3, Testplace4, CALC_AVOIDTOLL, time) / 10#
    tempStr = "Distance (avoid toll) and hours from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    tempStr = tempStr + ", "
    tempStr = tempStr + CStr(time)
    tempStr = tempStr + " minutes"
    
    If Err Then
    errorStr = "CalcDistance3(Testplace3, Testplace4, CALC_AVOIDTOLL, time): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'run a trip using practical calculation, and output duration of trip in minutes
    '*****************************
    
    Err.Clear
    On Error Resume Next

    distance = pcmsrv.CalcDistance3(Testplace1, Testplace2, CALC_PRACTICAL, time) / 10#
    tempStr = "Distance (P) and hours from "
    tempStr = tempStr + Testplace1
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    tempStr = tempStr + ", "
    tempStr = tempStr + CStr(time)
    tempStr = tempStr + " minutes"
    
    If Err Then
    errorStr = "CalcDistance3(Testplace1, Testplace2, CALC_PRACTICAL, time): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'return latitute and longitude for a place name
    '*****************************
    
    Err.Clear
    On Error Resume Next

    ret = pcmsrv.CityToLatLong(llplace, buflen)
    tempStr = "CityToLatLong for "
    tempStr = tempStr + llplace
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    errorStr = "CityToLatLong(llplace, buflen): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    
    Err.Clear
    On Error Resume Next
    ret = pcmsrv.CityToLatLong(alias1, buflen)
    
    tempStr = "CityToLatLong for "
    tempStr = tempStr + alias1
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(buffer)
    
    If Err Then
    errorStr = "CityToLatLong(" & alias1 & "," & buflen & "): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    'convert latitude and longitude coordinates into a place name
    '*****************************
    
    Err.Clear
    On Error Resume Next
    ret = pcmsrv.LatLongToCity(latlong1, buflen)
    tempStr = "Placename for "
    tempStr = tempStr + latlong1
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    errorStr = "LatLongToCity(" & latlong1 & ", " & buflen & "): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    ret = pcmsrv.LatLongToCity(latlong2, buflen)
    tempStr = "Placename for "
    tempStr = tempStr + latlong2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(ret)
    
    
    If Err Then
    errorStr = "LatLongToCity(latlong2, buflen): error  "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    List1.AddItem (divider)
    '****************************************************************
    'Start Section 2
    '****************************************************************
    tempStr = "**Start Section 2**"
    List1.AddItem (tempStr)
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If

    
    '*****************************
    'nothing in section 2
    '*****************************
    
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 3
    '****************************************************************
    
    tempStr = "**Start Section 3**"
    List1.AddItem (tempStr)
    
    '*****************************
    'create a new trip
    '*****************************
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '*******************
    'test Distance To Route
    Set shortTrip = pcmsrv.NewTrip("NA")
    shortTrip.Addstop ("Denver,CO")
    shortTrip.Addstop ("Dallas,TX")
    Dim dist As Long
    
    Err.Clear
    On Error Resume Next
    dist = shortTrip.DistanceToRoute(Testplace11)
    tempStr = "Distance from "
    tempStr = tempStr + Testplace11
    tempStr = tempStr + " to route: "
    tempStr = tempStr + CStr(dist)
    
    If Err Then
    errorStr = "error DistanceToRoute(Testplace11): "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    End If
    
    '*******************
    'try again this time call TravelDistance first
    Dim tripDist As Long
    tripDist = shortTrip.TravelDistance
    
    Err.Clear
    On Error Resume Next
    dist = shortTrip.DistanceToRoute(Testplace11)
    tempStr = "Distance from "
    tempStr = tempStr + Testplace11
    tempStr = tempStr + " to route: "
    tempStr = tempStr + CStr(dist)
    
    If Err Then
    errorStr = "error DistanceToRoute(Testplace11): "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    End If
     
    'clear the stops
    shortTrip.ClearStops
    
    '*******************
    shortTrip.Addstop (ferrystart)
    shortTrip.Addstop (ferryend)
    
    '*****************************
    Set Options = shortTrip.GetOptions
    '*****************************
    
    '*********************
    'show Ferry Miles
    Dim ferryMode As Boolean
    ferryMode = True
    
    Err.Clear
    On Error Resume Next
    Options.ShowFerryMiles = ferryMode
    tempStr = "Show Ferry Miles: "
    tempStr = tempStr + CStr(ferryMode)
    
    If Err Then
    errorStr = "error: Options.ShowFerryMiles:"
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    '**********************
    'test with borders open
    '*****************************
    Options.BordersOpen
    '*****************************
    
    Err.Clear
    On Error Resume Next
    distance = shortTrip.TravelDistance / 10#
    tempStr = "Distance from "
    tempStr = tempStr + ferrystart
    tempStr = tempStr + " to "
    tempStr = tempStr + ferryend
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'set custom mode
    Dim custom As Boolean
    custom = False
    Err.Clear
    On Error Resume Next
    shortTrip.CustomMode (custom)
    
    If Err Then
    errorStr = "Error: shortTrip.CustomMode: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If

    '*****************************
    'get the State Report
    '*****************************
  
    Set Report = shortTrip.GetReport(RPT_STATE)
    
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '*****************************
    'display the report line by line
    '*****************************
    
    Dim i As Integer
    Dim repLine As String ' * 1000
    
    Err.Clear
    On Error Resume Next
    For i% = 0 To (reportNum - 1)
    repLine = ""
    repLine = Report.Line(i)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i
    
    If Err Then
    
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
    List1.AddItem (divider)
    
    '**********************
    'Set Options = shortTrip.GetOptions
    
    'return cost per loaded mile:
    Err.Clear
    On Error Resume Next
    ret = Options.CostPerLoadedMile
    tempStr = "CostPerLoadedMile: "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    
    errorStr = "Error CostPerLoadedMile: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'set cost per loaded mile
    
    Err.Clear
    On Error Resume Next
    ret = Options.CostPerLoadedMile + 2
    tempStr = "CostPerLoadedMile: "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    
    errorStr = "Error CostPerLoadedMile: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'get cost per loaded mile
    
    Err.Clear
    On Error Resume Next
    Options.CostPerLoadedMile = ret
    tempStr = "CostPerLoadedMile: "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    
    errorStr = "Error CostPerLoadedMile: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'return number of bytes in the Report:
    
    Err.Clear
    On Error Resume Next
    ret = Report.NumBytes
    tempStr = "Bytes in Report for shortTrip: "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    
    errorStr = "Error NumBytes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'Get HTML Report
    Dim HTMLReport As Object
    
    Err.Clear
    On Error Resume Next
    Set HTMLReport = shortTrip.GetHTMLReport(RPT_STATE)
    
    If Err Then
    errorStr = "Error shortTrip.GetHTMLReport(RPT_STATE): "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    End If
    
    '**********************
    'return the number of bytes in the HTML report
    Dim num As Long
    
    Err.Clear
    On Error Resume Next
    num = HTMLReport.NumBytes
    tempStr = "Number of bytes in HTML Report: "
    tempStr = tempStr + CStr(num)
    
    If Err Then
    errorStr = "Error HTMLReport.NumBytes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'get the HTML Report Text
    Dim htmlText As String
    
    Err.Clear
    On Error Resume Next
    htmlText = HTMLReport.Text
    tempStr = CStr(htmlText)
    
    If Err Then
    errorStr = "Error HTMLReport.Text: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 4
    '****************************************************************
    tempStr = "**Start Section 4**"
    List1.AddItem (tempStr)
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    
    '**********************
    'return Route Type, see functions declarations for types
    
    Err.Clear
    On Error Resume Next
    ret = Options.RouteType
    tempStr = "RouteType: "
    tempStr = tempStr + CStr(ret)
    
    If Err Then
    
    errorStr = "Error RouteType: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    'clear all stops
    shortTrip.ClearStops
    
    '*********************
    'run a simple route using set routing
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(Testplace3, Testplace6) / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace6
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(Testplace3, Testplace6): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'run a simple route using SPLC
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(splc1, splc2) / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + splc1
    tempStr = tempStr + " to "
    tempStr = tempStr + splc2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(splc1, splc2): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'run a route using Canadian Zip Codes
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(canpost1, canpost2) / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + canpost1
    tempStr = tempStr + " to "
    tempStr = tempStr + canpost2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(canpost1, canpost1): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'run a route using Hazplaces
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(hazplace1, hazplace2) / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + hazplace1
    tempStr = tempStr + " to "
    tempStr = tempStr + hazplace2
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(hazplace1, hazplace2): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'run a route using Aliases
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(alias2, alias3) / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + alias2
    tempStr = tempStr + " to "
    tempStr = tempStr + alias3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(alias2, alias3): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'set Route Type to Shortest
    Options.RouteType = CALC_SHORTEST
    Options.RouteType = 1
    tempStr = "RouteType: "
    tempStr = tempStr + CStr(CALC_SHORTEST)
    List1.AddItem (tempStr)
    
    '*********************
    'run a route using Aliases
    
    shortTrip.Addstop (alias2)
    shortTrip.Addstop (alias3)
    
    Err.Clear
    On Error Resume Next
    distance = shortTrip.TravelDistance / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + alias2
    tempStr = tempStr + " to "
    tempStr = tempStr + alias3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "TravelDistance(alias2, alias3): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'set Route Type to Practical
    Options.RouteType = CALC_PRACTICAL
    Options.RouteType = 0
    tempStr = "RouteType: "
    tempStr = tempStr + CStr(CALC_PRACTICAL)
    List1.AddItem (tempStr)
    
    '*********************
    'run a route using Aliases
    
    Err.Clear
    On Error Resume Next
    distance = shortTrip.TravelDistance / 10#
    tempStr = "CalcDistance from "
    tempStr = tempStr + alias2
    tempStr = tempStr + " to "
    tempStr = tempStr + alias3
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "TravelDistance(alias2, alias3): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    List1.AddItem (divider)

    
    '****************************************************************
    'Start Section 5
    '****************************************************************
    
    tempStr = "**Start Section 5**"
    List1.AddItem (tempStr)
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '*********************
    'create a new trip
    
    Err.Clear
    On Error Resume Next
    Set pracTrip = pcmsrv.NewTrip(regionName)
    
    If Err Then
    errorStr = "error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
    'add stops:
    pracTrip.Addstop (Testplace3)
    pracTrip.Addstop (Testplace6)
    pracTrip.Addstop (Testplace4)
    
    '*********************
    Set Options = pracTrip.GetOptions
    '*********************
    Options.RouteType = CALC_PRACTICAL
    Options.RouteType = 0
    tempStr = "RouteType: "
    tempStr = tempStr + CStr(CALC_PRACTICAL)
    List1.AddItem (tempStr)
    '*********************
    
    'calculate Travel Distance for trip
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace6
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'set kilometers
    
    ml = False
    
    Options.Miles = ml
    tempStr = "Options.Miles = False: SetKilometers(pracTrip)"
    List1.AddItem (tempStr)
    
    '**********************
    'run the route calculation
    
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Practical route ("
    tempStr = tempStr + Testplace3
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace6
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ") in KM: "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance(testplace3, 6, 4): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*****************************
    Set Report = pracTrip.GetReport(RPT_DETAIL)
    '*****************************
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
            
    Err.Clear
    On Error Resume Next
    ret = reportNum - 1
    For i% = 0 To (ret)
    repLine = ""
    repLine = Report.Line(i%)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i%
   
    If Err Then
   
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
   
    End If
      
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 6
    '****************************************************************
    
    tempStr = "**Start Section 6**"
    List1.AddItem (tempStr)
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '**********************
    'Show the trip's state by state mileage breakdown, in driving order
    tempStr = "State Report...:"
    List1.AddItem (tempStr)
    
    '*********************
    'set Alpha Order
    Err.Clear
    On Error Resume Next
    
    ml = False
    Options.AlphaOrder = ml
    tempStr = "AlphaOrder = false"
    
    If Err Then
    
    errorStr = "Error AlphaOrder: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
    '*********************
    'get the report
    
    Set Report = pracTrip.GetReport(RPT_STATE)
    
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'display the report line by line
    Err.Clear
    On Error Resume Next
    ret = reportNum - 1
    For i% = 0 To (ret)
    repLine = ""
    repLine = Report.Line(i%)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i%
    
    If Err Then
    
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
    tempStr = "*********************"
    List1.AddItem (tempStr)
    
    '**********************
    Dim reqLine As String
    Dim which As Long
    which = reportNum / 2
    reqLine = Report.Line(which - 1)
    tempStr = "Requested Line, #"
    tempStr = tempStr + CStr(which)
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(reqLine)
    List1.AddItem (tempStr)
       
    '*****************************
    Dim ReportData As Object
    
    Err.Clear
    On Error Resume Next
    Set ReportData = pracTrip.GetReportData()
    
    If Err Then
    
    errorStr = "Error GetReportData: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)

    Else
        
    '*****************************
    'Here are the functions for the cost, mileage, and time of a
    'particular Leg of the Report
    '*****************************
    
    Err.Clear
    On Error Resume Next
    which = 1
    Set LegReport = ReportData.ReportLeg(which)

    If Err Then
    
    errorStr = "Error creating Leg Report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    
    '*********************
        
    Dim LMiles As Long
    Dim TMiles As Long
    
    Err.Clear
    On Error Resume Next
    LMiles = LegReport.LegMiles
    TMiles = LegReport.TotMiles
    tempStr = "Leg 1: LegMiles: "
    tempStr = tempStr + CStr(LMiles)
    tempStr = tempStr + ", TotalMiles: "
    tempStr = tempStr + CStr(TMiles)
    
    If Err Then
    
    errorStr = "Error Leg/Total Miles: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    End If
    
    
    '*********************
    Dim LCost As Long
    Dim TCost As Long
    
    Err.Clear
    On Error Resume Next
    LCost = LegReport.LegCost
    TCost = LegReport.TotCost
    tempStr = "Leg 1: LegCost: "
    tempStr = tempStr + CStr(LCost)
    tempStr = tempStr + ", TotalCost: "
    tempStr = tempStr + CStr(TCost)
    
    If Err Then
    
    errorStr = "Error Leg/Total Cost: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    End If
    
    '*********************
    Dim LMinutes As Long
    Dim TMinutes As Long
    
    Err.Clear
    On Error Resume Next
    LMinutes = LegReport.LegMinutes
    TMinutes = LegReport.TotMinutes
    tempStr = "Leg 1: LegMinutes: "
    tempStr = tempStr + CStr(LMinutes)
    tempStr = tempStr + ", TotalMinutes: "
    tempStr = tempStr + CStr(TMinutes)
    
    If Err Then
    
    errorStr = "Error Leg/Total minutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    '**********************
        
    End If 'Report Leg
    End If ' Report Data
    
    tempStr = "***************"
    List1.AddItem (tempStr)
    
    '***********************
    'return the number of segments in the report
    Err.Clear
    On Error Resume Next
    num = ReportData.NumSegments
    tempStr = "Number of segments in trip: "
    tempStr = tempStr + CStr(num)
    
    If Err Then
    errorStr = "Error NumSegments: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Dim RepSegment As Object
    Dim segmentNum As Integer
    segmentNum = 22
    Err.Clear
    On Error Resume Next
    
    Set RepSegment = ReportData.Segment(segmentNum)
    tempStr = "Segment Number: "
    tempStr = tempStr + CStr(segmentNum)
    
    If Err Then
    errorStr = "Error Report.Segment: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    
    '**********************
    List1.AddItem (tempStr)
    Err.Clear
    On Error Resume Next
    Dim segString As String
    segString = RepSegment.State
    tempStr = "State: "
    tempStr = tempStr + CStr(segString)
    
    If Err Then
    errorStr = "Error RepSegment.State: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    segString = RepSegment.Dir
    tempStr = "Direction: "
    tempStr = tempStr + CStr(segString)
    
    If Err Then
    errorStr = "Error RepSegment.Dir: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    segString = RepSegment.Route
    tempStr = "Route Name: "
    tempStr = tempStr + CStr(segString)
    
    If Err Then
    errorStr = "Error Repsegment.Route: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    Dim segNum As Integer
    
    Err.Clear
    On Error Resume Next
    segNum = RepSegment.Miles
    tempStr = "Segment Miles: "
    tempStr = tempStr + CStr(segNum)
    
    If Err Then
    errorStr = "Error RepSegment.Miles: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    Err.Clear
    On Error Resume Next
    segNum = RepSegment.Minutes
    tempStr = "Segment Minutes: "
    tempStr = tempStr + CStr(segNum)
    
    If Err Then
    errorStr = "Error RepSegment.Minutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    Err.Clear
    On Error Resume Next
    segNum = RepSegment.Toll
    tempStr = "Segment Tolls: "
    tempStr = tempStr + CStr(segNum)
    
    If Err Then
    errorStr = "Error RepSegment.Toll: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    Dim seqInterChange
    
    seqInterChange = RepSegment.Interchange
    tempStr = "Segment Interchange: "
    tempStr = tempStr + CStr(seqInterChange)
    
    If Err Then
    errorStr = "Error RepSegment.Interchange: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    End If 'Segment

    List1.AddItem (divider)
   
    '****************************************************************
    'Start Section 7
    '****************************************************************
    tempStr = "**Start Section 7**"
    List1.AddItem (tempStr)
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    tempStr = "Mileage Report...:"
    List1.AddItem (tempStr)
    
    '*********************
    'get the report
    Set Report = pracTrip.GetReport(RPT_MILEAGE)
    
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'display the report line by line
    Err.Clear
    On Error Resume Next
    ret = reportNum - 1
    For i% = 0 To (ret)
    repLine = ""
    repLine = Report.Line(i%)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i%
    
    If Err Then
    
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 8
    '****************************************************************
    tempStr = "**Start Section 8**"
    List1.AddItem (tempStr)
    
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    '********************
    'set Hub mode
    Err.Clear
    On Error Resume Next
    hubMode = False
    Options.Hub = hubMode
    If Err Then
    errorStr = "error HubMode: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    End If
    
    '**********************
    'run the route calculation
    
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Hub Mode ("
    tempStr = tempStr + Testplace3
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace6
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ") in KM: "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance(testplace3, 6, 4): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    '*********************
    'get the report
    Set Report = pracTrip.GetReport(RPT_DETAIL)
        
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'display the report line by line
    Err.Clear
    On Error Resume Next
    ret = reportNum - 1
    For i% = 0 To (ret)
    repLine = ""
    repLine = Report.Line(i%)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i%
    
    If Err Then
    
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
   
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 9
    '****************************************************************
    tempStr = "**Start Section 9**"
    List1.AddItem (tempStr)
    '****************************************************************
    'Check if server is OK
    '****************************************************************

    If pcmsrv.ID <= 0 Then
        MsgBox ("Error: Creating pcmserver object")
        Exit Sub
    Else
        tempStr = " pcmserver object OK "
        List1.AddItem (tempStr)
    End If
    
    '*******************
    'set hub mode
    Err.Clear
    On Error Resume Next
    hubMode = True
    Options.Hub = hubMode
    If Err Then
    errorStr = "error HubMode: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    End If
    '*******************
    'set Break Hours
    Err.Clear
    On Error Resume Next
    breakHrs = 240
    Options.BreakHours = breakHrs
    If Err Then
    errorStr = "error BreakHours: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    End If
    '*******************
    'set Break WaitHours
    Err.Clear
    On Error Resume Next
    breakWaitHrs = 15
    Options.BreakWaitHours = breakWaitHrs
    If Err Then
    errorStr = "error BreakHours: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    End If
    '*******************
    'run the route calculation
    
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Practical route ("
    tempStr = tempStr + Testplace3
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace6
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ") in KM: "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance(testplace3, 6, 4): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    Err.Clear
    On Error Resume Next
    tempStr = "Break Hours: "
    tempStr = tempStr + CStr(Options.BreakHours)
    
    If Err Then
    errorStr = "Error Break Hours: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'get the report
    Set Report = pracTrip.GetReport(RPT_DETAIL)
    
    Err.Clear
    On Error Resume Next
    reportNum = Report.NumLines
    tempStr = "Number of lines in report: "
    tempStr = tempStr + CStr(reportNum)
    
    If Err Then
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'display the report line by line
    Err.Clear
    On Error Resume Next
    ret = reportNum - 1
    For i% = 0 To (ret)
    repLine = ""
    repLine = Report.Line(i%)
    tempStr = CStr(repLine)
    List1.AddItem (tempStr)
    Next i%
    
    If Err Then
    
    errorStr = "Error creating report: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    End If
    
       
    List1.AddItem (divider)
    
    '****************************************************************
    'Start Section 10
    '****************************************************************
    tempStr = "**Start Section 10**"
    List1.AddItem (tempStr)
    
    'clear all stops
    pracTrip.ClearStops
    
    '*********************
    'using current settings run a different trip
    
    Err.Clear
    On Error Resume Next
    distance = pcmsrv.CalcDistance(Testplace10, Testplace11) / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace10
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace11
    tempStr = tempStr + " : "
    tempStr = tempStr + CStr(distance)
    tempStr = tempStr + " miles"
    
    If Err Then
    errorStr = "CalcDistance(Testplace10, Testplace11): error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'set Route Type
    Options.RouteType = CALC_SHORTEST
    Options.RouteType = 1
    tempStr = "RouteType: "
    tempStr = tempStr + CStr(CALC_SHORTEST)
    List1.AddItem (tempStr)
    
    '**********************
    'clear stops
    pracTrip.ClearStops
    'add new stops
    pracTrip.Addstop (Testplace3)
    pracTrip.Addstop (Testplace13)
    
    '**********************
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace13
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'clear stops
    pracTrip.ClearStops
    'add stops
    pracTrip.Addstop (Testplace3)
    pracTrip.Addstop (Testplace4)
    
    'run another trip
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'clear stops
    pracTrip.ClearStops
    'add stops
    pracTrip.Addstop (Testplace4)
    pracTrip.Addstop (Testplace3)
    pracTrip.Addstop (Testplace4)
    
    'run another trip
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Distance from "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace3
    tempStr = tempStr + " to "
    tempStr = tempStr + Testplace4
    tempStr = tempStr + ": "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get location at miles
    
    Dim strSize As Integer
    strSize = 35
    Dim location As String * 35
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMiles(2000, strSize)
    tempStr = "LocationAtMiles (200):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMiles: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get location at miles
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMiles(7400, strSize)
    tempStr = "LocationAtMiles (740):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMiles: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get location at minutes
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMinutes(50, strSize)
    tempStr = "LocationAtMinutes (50):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMinutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get location at minutes
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMinutes(125, strSize)
    tempStr = "LocationAtMinutes (125):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMinutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get LatLong at Miles
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LatLongAtMiles(500, strSize)
    tempStr = "LatLongAtMiles (50):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LatLongAtMiles: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'get LatLong at Minutes
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LatLongAtMinutes(125, strSize)
    tempStr = "LatLongAtMinutes (125):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LatLongAtMinutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'turn Shape Points Off
    Dim useShapePts As Boolean
    useShapePts = False
    ret = pracTrip.LatLongsEnRoute(useShapePts)
    tempStr = "Shape Points: "
    tempStr = tempStr + CStr(useShapePts)
    List1.AddItem (tempStr)
    '*********************
    'get location at minutes
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMinutes(200, strSize)
    tempStr = "LocationAtMinutes (200):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMinutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'turn Shape Points On
    useShapePts = True
    ret = pracTrip.LatLongsEnRoute(useShapePts)
    tempStr = "Shape Points: "
    tempStr = tempStr + CStr(useShapePts)
    List1.AddItem (tempStr)
    '*********************
    'get location at minutes
    
    Err.Clear
    On Error Resume Next
    location = pracTrip.LocationAtMinutes(200, strSize)
    tempStr = "LocationAtMinutes (200):"
    tempStr = tempStr + CStr(location)
    
    If Err Then
    errorStr = "Error: LocationAtMinutes: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
     
    List1.AddItem (divider)
    '****************************************************************
    'Start Section 11
    '****************************************************************
    tempStr = "**Start Section 11**"
    List1.AddItem (tempStr)
    
    '**********************
    Set Options = pracTrip.GetOptions
    '**********************
    'set default options
    pracTrip.SetDefaultOptions
    '**********************
    Set Options = pracTrip.GetOptions
    '**********************
    pracTrip.ClearStops
    
    'add stops
    pracTrip.Addstop (Testplace7)
    pracTrip.Addstop (Testplace8)
    pracTrip.Addstop (Testplace9)
    
    tempStr = "Add Stops: ("
    tempStr = tempStr + Testplace7
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace8
    tempStr = tempStr + ", "
    tempStr = tempStr + Testplace9
    tempStr = tempStr + ")"
    List1.AddItem (tempStr)
    
    '**********************
    'return number of stops in trip
    Dim tripNumStops As Integer
    Err.Clear
    On Error Resume Next
    tripNumStops = pracTrip.NumStops
    tempStr = "NumStops: "
    tempStr = tempStr + CStr(tripNumStops)
    
    If Err Then
    errorStr = "Error: NumStops: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'get the name of a given stop
    Dim tripStop As String
    Err.Clear
    On Error Resume Next
    tripStop = pracTrip.GetStop(1, buflen)
    tempStr = "GetStop: Stop #1: "
    tempStr = tempStr + CStr(tripStop)
    
    If Err Then
    errorStr = "Error: GetStop: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'calculate the mileage for the trip
    Err.Clear
    On Error Resume Next
    distance = pracTrip.TravelDistance / 10#
    tempStr = "Distance: "
    tempStr = tempStr + CStr(distance)
    
    If Err Then
    errorStr = "TravelDistance: error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'get the time for the trip
    Err.Clear
    On Error Resume Next
    time = pracTrip.TravelTime
    tempStr = "TravelTime: "
    tempStr = tempStr + CStr(time)
    
    If Err Then
    errorStr = "Error: TravelTime: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'do not reorder the last stop
    
    Dim fixDest As Integer
    fixDest = 1
    Err.Clear
    On Error Resume Next
    pracTrip.Optimize (fixDest)
    
    If Err Then
        errorStr = "Error: "
        errorStr = errorStr + CStr(Err.Description)
        List1.AddItem (errorStr)
    
    Else
        '*******
        distance = pracTrip.TravelDistance / 10#
        tempStr = "Distance: "
        tempStr = tempStr + CStr(distance)
        List1.AddItem (tempStr)
        '*******
        time = pracTrip.TravelTime
        tempStr = "TravelTime: "
        tempStr = tempStr + CStr(time)
        List1.AddItem (tempStr)
        '*******
        tripStop = pracTrip.GetStop(1, buflen)
        tempStr = "GetStop: Stop #1: "
        tempStr = tempStr + CStr(tripStop)
        List1.AddItem (tempStr)
    
    End If
    
    '**********************
    'optimize trip (should be error, only 3 stops)
    fixDest = 0
    Err.Clear
    On Error Resume Next
    pracTrip.Optimize (fixDest)
    
    If Err Then
        errorStr = "Error: "
        errorStr = errorStr + CStr(Err.Description)
        List1.AddItem (errorStr)
    
    Else
        '*******
        distance = pracTrip.TravelDistance / 10#
        tempStr = "Distance: "
        tempStr = tempStr + CStr(distance)
        List1.AddItem (tempStr)
        '*******
        time = pracTrip.TravelTime
        tempStr = "TravelTime: "
        tempStr = tempStr + CStr(time)
        List1.AddItem (tempStr)
        '*******
        tripStop = pracTrip.GetStop(1, buflen)
        tempStr = "GetStop: Stop #1: "
        tempStr = tempStr + CStr(tripStop)
        List1.AddItem (tempStr)
    
    End If
    List1.AddItem (divider)
    '****************************************************************
    'Start Section 12
    '****************************************************************
    tempStr = "**Start Section 12**"
    List1.AddItem (tempStr)
    
    'free up the trips used; you can have up to 8 trips open at once
    pracTrip.ClearStops
    shortTrip.ClearStops
    
    '*********************
    tempStr = "Lookup Functions...:"
    List1.AddItem (tempStr)
    
    '*********************
    'look up a place in the database
    Err.Clear
    On Error Resume Next
    pcmsrv.CheckPlaceName (lookupplace)
    tempStr = lookupplace
    tempStr = tempStr + " exists in the database"
    
    If Err Then
    errorStr = lookupplace
    errorStr = errorStr + " does not exist in the database:"
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else
    List1.AddItem (tempStr)
    
    End If
    
    '*********************
    'create a new trip
    Err.Clear
    On Error Resume Next
    Set endTrip = pcmsrv.NewTrip(regionName)
    
    If Err Then
    errorStr = "error creating the trip: "
    errorStr = errorStr + CStr(Err.Description)
    List1.AddItem (errorStr)
    
    Else ':
    
    
    '********************
    Dim zipLen As Integer
    zipLen = 10
    Dim cityLen As Integer
    cityLen = 20
    Dim countyLen As Integer
    countyLen = 20
    Dim match As String
    '********************
    Dim pickList As Object
    
        '********************
        'lookup all the cities that match: i is the number of matches
        Err.Clear
        On Error Resume Next
        Set pickList = pcmsrv.GetPickList(latlong1, regionName, 1)
    
            If Err Then
            errorStr = "Error GetPickList: "
            errorStr = errorStr + CStr(Err.Description)
            List1.AddItem (errorStr)
    
        Else
    
            Set pickList = pcmsrv.GetFmtPickList(latlong1, zipLen, cityLen, countyLen)
            num = pickList.Count
            tempStr = CStr(num)
            tempStr = tempStr + " matching to: "
            tempStr = tempStr + latlong1
            List1.AddItem (tempStr)
    
    
            For i% = 0 To (num - 1)
            match = pickList.Entry(i%)
            tempStr = CStr(match)
            List1.AddItem (tempStr)
            Next i%
    
        End If
    
        '**********************
        Err.Clear
        On Error Resume Next
        Set pickList = pcmsrv.GetPickList(Testplace5, regionName, 1)
    
        If Err Then
            errorStr = "Error GetPickList: "
            errorStr = errorStr + CStr(Err.Description)
            List1.AddItem (errorStr)
    
        Else
    
            Set pickList = pcmsrv.GetFmtPickList(Testplace5, zipLen, cityLen, countyLen)
            num = pickList.Count
            tempStr = CStr(num)
            tempStr = tempStr + " matching to: "
            tempStr = tempStr + Testplace5
            List1.AddItem (tempStr)
    
            For i% = 0 To (num - 1)
            match = pickList.Entry(i%)
            tempStr = CStr(match)
            List1.AddItem (tempStr)
            Next i%
    
        End If
    
        '**********************
        'try for an exact match:
        
        Err.Clear
        On Error Resume Next
        Set pickList = pcmsrv.GetPickList(lookupplace, regionName, 2)
    
        If Err Then
            errorStr = "Error GetPickList: "
            errorStr = errorStr + CStr(Err.Description)
            List1.AddItem (errorStr)
    
        Else
    
            Set pickList = pcmsrv.GetFmtPickList(lookupplace, zipLen, cityLen, countyLen)
            num = pickList.Count
            tempStr = CStr(num)
            tempStr = tempStr + " matching exactly to: "
            tempStr = tempStr + lookupplace
            List1.AddItem (tempStr)
    
    
            For i% = 0 To (num - 1)
            match = pickList.Entry(i%)
            tempStr = CStr(match)
            List1.AddItem (tempStr)
            Next i%
    
        End If
    
        '**********************
        'lookup all cities that match
        Err.Clear
        On Error Resume Next
        Set pickList = pcmsrv.GetPickList(lookupplace, regionName, 0)
        
        If Err Then
            errorStr = "Error GetPickList: "
            errorStr = errorStr + CStr(Err.Description)
            List1.AddItem (errorStr)
        
        Else
    
            Set pickList = pcmsrv.GetFmtPickList(lookupplace, zipLen, cityLen, countyLen)
            num = pickList.Count
            tempStr = CStr(num)
            tempStr = tempStr + " matching to: "
            tempStr = tempStr + lookupplace
            List1.AddItem (tempStr)
        
        
            For i% = 0 To (num - 1)
            match = pickList.Entry(i%)
            tempStr = CStr(match)
            List1.AddItem (tempStr)
            Next i%
            
                Err.Clear
                On Error Resume Next
                which = 3
                Dim matchPick As String
                matchPick = pickList.Entry(which - 1)
                tempStr = "match #"
                tempStr = tempStr + CStr(which)
                tempStr = tempStr + ": "
                tempStr = tempStr + CStr(matchPick)
                
                If Err Then
                errorStr = "Error pickList.Entry(3): "
                errorStr = errorStr + CStr(Err.Description)
                List1.AddItem (errorStr)
                Else
                List1.AddItem (tempStr)
                End If
                 
        End If
    
        '**********************
        'lookup all cities that match
        Err.Clear
        On Error Resume Next
        Set pickList = pcmsrv.GetPickList(splccity, regionName, 0)
        
        If Err Then
            errorStr = "Error GetPickList: "
            errorStr = errorStr + CStr(Err.Description)
            List1.AddItem (errorStr)
        
        Else
        
            Set pickList = pcmsrv.GetFmtPickList(splccity, zipLen, cityLen, countyLen)
            num = pickList.Count
            tempStr = CStr(num)
            tempStr = tempStr + " matching to: "
            tempStr = tempStr + splccity
            List1.AddItem (tempStr)
        
            For i% = 0 To (num - 1)
            match = pickList.Entry(i%)
            tempStr = CStr(match)
            List1.AddItem (tempStr)
            Next i%
        
        End If
    
    
    '*********************
    endTrip.ClearStops
    '*********************
    End If
    
    tempStr = "************************ END ******************************"
    List1.AddItem (tempStr)
    
End Sub
