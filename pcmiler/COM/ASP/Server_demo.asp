<%@ LANGUAGE = "VBSCRIPT" %>
<% Option Explicit %>

<% 

' *******************
'   VB Init
' *******************

' VARIABLES:
Dim errorstring 	' catch errors to display
Dim command			' which button was pressed
Dim trip			' trip object from server 
Dim i				' counter
Dim picklist		' picklist for geocoding results
Dim location		' place to geocode or add
Dim numstops		' stops in the trip (sent via the querystring + new stops)
Dim stops(100)		' hopefully, the user of this demo will have fewer!
Dim stopadded 		' the name of the stop added- the location
					' field gets cleared out when the stop is added
					' to the trip so we need to keep the name around
					' to display it


' no errors to start
errorstring = ""

' no location was yet geocoded
Set picklist=Nothing

' which button was pressed???
command = Request("SubmitButton")

' get the location
If command = "Clear" Then
	location = ""
Else
	location = Request("Location")
End If


' no stops added so far
stopadded = ""

Err.Clear
On Error Resume Next

Dim tips
tips = 	"1. Check that PC*MILER was properly installed.<br>" & _
		"2. Check that the global.asa file creates the object.<br>" & _ 
		"3. Make sure the internet server was restarted after the addition of this demo.<br>" & _
		"4. Check that IIS properties for this project have proper permissions.<br>" & _
		"5. It is recommended that this application run in a separate memory space."

' check that the server object exists
' it is created in the global.asa on application start
If (application("g_pcmsrv").ID <= 0) Then
	errorstring = "Error: pcmserver object not found.<br><br>" & tips
Else
	' recreate the trip
	Set trip = application("g_pcmsrv").NewTrip("NA")
	If (trip.ID <= 0) Then
		errorstring = "Failed to create trip.<br><br>" & tips
	Else
		' add each stop starting with "s0"
		i = 0
		Dim stopname
		Do
			' get the stop
			stopname = Request.QueryString("s" & i)
			If stopname = "" Then
				Exit Do
			End If
		
			Err.Clear
			On Error Resume Next

			' add the stop to the trip
			trip.AddStop(stopname)
		
			i = i + 1
		Loop

		' the number of stops was the number of times
		' AddStop was called (iterations of the loop)
		numstops = i

		' check if we are adding a stop
		If command = "" Or command = "Add Stop" Then
			If location = "" Then
				If command = "Add Stop" Then
					errorstring = "Please type a location to add."
				End If
			Else
				trip.AddStop(location)
				If Err Then
					errorstring = "Error adding the stop: " & location & ".  Please check the location and try again."
				Else
					numstops = numstops + 1
					stopadded = location
					' clear the field- successful
					location = ""
				End If
			End If
		End If

		' create the stop structure
		Dim curstop 
		For i = 0 to numstops-1
			If i = numstops - 1 And stopadded <> "" Then
				curstop = stopadded
			Else
				curstop = Request.QueryString("s" & i)
			End If

			' expand the name by geocoding
			Dim tmppicklist
			Set tmppicklist = application("g_pcmsrv").GetPickList(curstop, "NA", 0)
			stops(i) = tmppicklist.Entry(0)
			Set tmppicklist = Nothing	 
		Next
				
		call ProcessForm()
	End If
End If


' *******************
'   VB ProcessForm()
' *******************
Sub ProcessForm()
	
	Err.Clear
	On Error Resume Next
	
	If command = "Clear Trip" Then
		trip.ClearStops	
		numstops = 0		
	End If

	If command = "Lookup" Then

		If location = "" Then
			errorstring = "Please type a place to lookup."
			Exit Sub
		End If

		Err.Clear
		On Error Resume Next
	
		' Geocode the location- the results are returned in a picklist
		Set picklist = application("g_pcmsrv").GetPickList(location, "NA", 0)
	
		If Err Then
			' get the error string from PC*MILER Server
			errorstring = "Error geocoding " & location & ". " & application("g_pcmsrv").ErrorString(200)	
			Set picklist = Nothing	
		ElseIf picklist.count = 0 Then
			errorstring = "No matches found for " & location & "." 
			Set picklist = Nothing
		ElseIf picklist.count = 1 Then
			' keep textfield for only one entry, not picklist
			location = picklist.Entry(0)
			Set picklist = Nothing
		End If

	End If

	' Run the route
	' make sure the options are set to the latest
	Dim options
	Set options = trip.GetOptions

	If Request("RoutingType") = "Shortest" Then
		options.RouteType = 1
	Else
		' routing type is practical by default
		options.RouteType = 0
	End If

	' run the route
	traveldist = 	trip.TravelDistance()
End Sub

' *****************************************************************
'   VB CreateURL()
'
'	This function constructs the query string that includes
'	all the stops in the trip so they can be kept around.
' *****************************************************************
Function CreateURL()

	' append the stops to 's' and return it
	Dim s
	s = ""

	For i = 0 to (numstops - 1)
		If s <> "" Then
			s = s & "&"
		End If

		s = s & "s" & i & "=" & stops(i)
	Next

	CreateURL = s
End Function
%>

<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html;CHARSET=iso-8859-1">
	<TITLE>PC*MILER PCMServer Demo</TITLE>
</HEAD>

<BODY>

<P>
<TABLE BORDER="0" WIDTH="100%" BGCOLOR="#FFCC00">
	<TR>
		<TD WIDTH="100%">
			<CENTER>
			<P>
<HR ALIGN="CENTER" noshade>
<B><FONT SIZE="5">PC*MILER </FONT><FONT SIZE="5">PCMServer</FONT><FONT SIZE="5"> Demo</FONT></B><FONT SIZE="5">
<HR ALIGN="CENTER" noshade>
</FONT>
</CENTER>
		</TD>
	</TR>
</TABLE>


<BLOCKQUOTE>
	<P>This page allows the user to manipulate a trip by looking up and adding stops, clearing stops, changing routing
	options, calculating mileage and viewing reports.
</BLOCKQUOTE>

<BLOCKQUOTE>
	<p><font size="-1"><b><font color="#BD1821">
<%
' IF AN ERROR OCCURED, WRITE IT OUT
If errorString <> "" Then
	Response.Write errorString
End If
%>
	</font></b></font></p>
</BLOCKQUOTE>

<FORM NAME="serverDemo" ACTION="server_demo.asp?<%=CreateUrl()%>" METHOD="POST" ENCTYPE="application/x-www-form-urlencoded">

  <BLOCKQUOTE ALIGN="CENTER"> 
    <P align="left"><B><BR>
      <u><font size="3">Stop Information</font></u></B></P>
    <P align="left"><B><font size="3"> 

<% 
	' <location> can be either a field or a picklist returned from Lookup

	' If there is a picklist, display the contents in a select
	Err.Clear
	On Error Resume Next

	Dim numentries, newentry
	numentries = picklist.Count

	If Err Then
		Response.Write "<INPUT TYPE=" & Chr(34) & "TEXT" & Chr(34) & " NAME=" &Chr(34)
		Response.Write "Location" & Chr(34) & " VALUE=" & Chr(34)
    	Response.Write location
		Response.Write Chr(34) & " SIZE=35>"
	Else	
	'	Response.Write picklist.Entry(0) & " <br>"

		Response.Write "<SELECT NAME=" & " Location>"
	    For i = 0 to (numentries- 1)
			If i = 0 Then
				Response.Write "<OPTION SELECTED>"  & picklist.Entry(0) & "</OPTION>"
			Else
				Response.Write "<OPTION>"  & picklist.Entry(i) & "</OPTION>"
			End If
		Next
		Response.Write "</SELECT>"
	End If
%>

        <input type="SUBMIT" name="SubmitButton" value="Add Stop">
		<input type="SUBMIT" name="SubmitButton" value="Lookup">
		<input type="SUBMIT" name="SubmitButton" value="Clear">


      <br>
      </font></B></P>
    <table width="90%" border="0">
      <tr> 
        <td width="50%" nowrap colspan="2"> 
          <table width="70%" border="0">
            <tr bgcolor="#FFCF00"> 
              <td><b><u><font size="2">Stop</font></u></b></td>
              <td nowrap><b><u><font size="2">City</font></u></b></td>
           <!--   <td> 
                <div align="right"><b><u><font size="2">Miles</font></u></b></div>
              </td>
              <td> 
                <div align="right"><b><u><font size="2">Total</font></u></b></div>
              </td>
              <td> 
                <div align="right"><b><u><font size="2">Hours</font></u></b></div>
              </td> -->
            </tr>
            <%
''' FILL IN THE ROWS OF THIS TABLE WITH THE STOPS 
For i = 0 to (numstops - 1) 
%>
	<tr> 
    	<!-- STOP NUMBER -->
		<td><b><font size="2">
<%
			If i = 0 Then
				Response.Write("Orig ")
			ElseIf i = trip.NumStops-1 Then
				Response.Write("Dest")
			Else
				Response.Write("Stop " & i)
			End If
%>
		</font></b></td>
        
		<!-- STOP NAME -->
		<td nowrap><b><font size="2">
<%
			Response.Write(stops(i))	
%>
		</font></b></td>
        
	</tr>
<%
' PROCESS NEXT STOP
Next
%>

	<tr nowrap bgcolor="#FFFFFF">
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
            </tr>
            </tr>

		    </table>
        </td>
      </tr>
      <tr valign="top"> 
        <td width="92%" nowrap colspan="2"><i> 
		<% 	If numstops > 1 Then %>	
				The distance of this route is <b><%=trip.TravelDistance / 10.0%></b> miles.<br>
				The travel time is <b><%=FormatNumber(trip.TravelTime / 60.0,2)%></b> hours. <br>
		<%  End If %>
		  
			</i> 
          <hr>
           
          <input type="SUBMIT" name="SubmitButton" value="Clear Trip">
		  
          <b><font size="3">Routing Type: 
          <select name="RoutingType">
<% If Request("RoutingType") = "Shortest" Then %>
			  <option>Practical</option>
    	      <option selected>Shortest</option>
<% Else %>
			  <option selected>Practical</option>
    	      <option>Shortest</option>

<% End If %>
          </select>
			<input type="SUBMIT" name="SubmitButton" value="Run">

          </font></b> 
          <div align="left"><b><font size="3"> </font></b></div>
        </td>
      </tr>
    </table>
    <p><br>
    </p>
    <P align="left"><b><u><font size="3">View Reports</font></u></b> </P>
    <P align="left"> 
      <input type="submit" name="SubmitButton" value="State Report">
      <input type="submit" name="SubmitButton" value="Detailed Driving Directions">
      <br>
      <br>
<%
' IF THE USER WANTED TO VIEW A REPORT- PRINT IT HERE

Dim reportObj
Set reportObj = Nothing

' get the report the user asked for
Err.Clear
On Error Resume Next

If command = "State Report" Or command = "Detailed Driving Directions" Then
	If numstops <= 1 Then
%>
	<p><font size="-1"><b><font color="#BD1821"> 
<%
		Response.Write "The route must contain at least 2 stops."
%>
	</font></b></font></p>
<%
	ElseIf command = "State Report" Then 
		Set reportObj = trip.GetHTMLReport(1)
	Else
		Set reportObj = trip.GetHTMLReport(0)
	End If

	If Err Then
%>
	<p><font size="-1"><b><font color="#BD1821"> 
<%
		Response.Write "Error reading the report."
%>
	</font></b></font></p>
<%
	Else
		If reportObj <> Nothing Then
			Response.Write reportObj.Text
		End If
	End If
	
End If
%>      

</P>
    <P align="left"><b><u> </u></b></P>
</BLOCKQUOTE>


<BLOCKQUOTE>
	<P><BR>
	</P>
</BLOCKQUOTE>


<BLOCKQUOTE ALIGN="CENTER">
	<P>
</BLOCKQUOTE>

</FORM>
</BODY>

</HTML>