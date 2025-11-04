VERSION 5.00
Begin VB.Form Form1 
   Caption         =   "Form1"
   ClientHeight    =   2535
   ClientLeft      =   60
   ClientTop       =   345
   ClientWidth     =   4680
   LinkTopic       =   "Form1"
   ScaleHeight     =   2535
   ScaleWidth      =   4680
   StartUpPosition =   3  'Windows Default
   Begin VB.TextBox result 
      Height          =   375
      Left            =   2400
      TabIndex        =   5
      Top             =   1320
      Width           =   1575
   End
   Begin VB.TextBox Dest 
      Height          =   375
      Left            =   2280
      TabIndex        =   2
      Text            =   "Dallas,TX"
      Top             =   480
      Width           =   1575
   End
   Begin VB.TextBox Origin 
      Height          =   375
      Left            =   360
      TabIndex        =   1
      Text            =   "Denver,CO"
      Top             =   480
      Width           =   1575
   End
   Begin VB.CommandButton ServerTest 
      Caption         =   "Calculate"
      Height          =   375
      Left            =   360
      TabIndex        =   0
      Top             =   1320
      Width           =   1695
   End
   Begin VB.Label Label2 
      Caption         =   "Destination:"
      Height          =   255
      Left            =   2280
      TabIndex        =   4
      Top             =   120
      Width           =   975
   End
   Begin VB.Label Label1 
      Caption         =   "Origin:"
      Height          =   255
      Left            =   360
      TabIndex        =   3
      Top             =   120
      Width           =   615
   End
End
Attribute VB_Name = "Form1"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Function test()
        Dim srv As Object
        Dim trip As Object
            
        result.Text = ""
        
        Set srv = CreateObject("PCMServer.PCMServer")
        If srv.ID <= 0 Then
            MsgBox ("Server Error")
            Exit Function
        End If
        
        Set trip = srv.NewTrip("NA")
        
        If trip.ID <= 0 Then
            MsgBox ("Trip Error")
            Exit Function
        End If
        
        trip.AddStop (Origin.Text)
        trip.AddStop (Dest.Text)
        
        Dim miles
        Dim div As Single
        div = 10#
        miles = trip.TravelDistance()
        miles = miles / div
        result.Text = CStr(miles) & " " & "miles"
                
        Set trip = Nothing
        Set srv = Nothing

End Function




Private Sub ServerTest_Click()
    Call test
End Sub
