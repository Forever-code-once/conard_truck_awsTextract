ASP Directory Contents
----------------------
server_demo.asp - Sample IIS application using the Server COM interface.
global.asa      - Required file for sample IIS application.

PC*MILER server ASP Demo:
--------------------------

Create a new web site. (ex: c:\\inetpub\wwwroot\serverdemo)
Copy the files to the directory.

Create the object "PCMServer.PCMServer" in the global.asa. 
This needs to be created only once.

Example:
Sub Application_OnStart
set g_pcmsrv=Server.CreateObject("PCMServer.PCMServer")
set application("g_pcmsrv") = g_pcmsrv
End Sub

Then access this object from any asp page.

Example ( create a new trip object ):

Set tripObj = application("g_pcmsrv").NewTrip("NA")
