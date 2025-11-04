Imports System.Text
Public Class Form1
    Inherits System.Windows.Forms.Form

#Region " Windows Form Designer generated code "

    Public Sub New()
        MyBase.New()

        'This call is required by the Windows Form Designer.
        InitializeComponent()
        Dim Server As Short = PCMDLLINT.PCM.PCMSOpenServer(0, 0)
        Me.ListBox1.Items.Add("Server = PCMSOpenServer(0, 0)")
        Me.ListBox1.Items.Add("Server = " + Server.ToString)

        Dim tripID As Integer = PCMDLLINT.PCM.PCMSNewTrip(Server)
        Me.ListBox1.Items.Add("tripID = PCMSNewTrip(Server)")
        Me.ListBox1.Items.Add("tripID = " + tripID.ToString)

        Dim ret As Integer
        Dim ByteCount As Integer

        Dim matches As Integer = PCMDLLINT.PCM.PCMSLookup(tripID, "Princeton,NJ;1000 herrontown road", 0)
        Me.ListBox1.Items.Add("matches = PCMSLookup(tripID, ""Princeton,NJ;1000 herrontown road"", 0)")
        Me.ListBox1.Items.Add("matches = " + matches.ToString)
        If (matches = 1) Then
            Dim result As StringBuilder = New StringBuilder(256)
            PCMDLLINT.PCM.PCMSGetMatch(tripID, 0, result, 256)
            Me.ListBox1.Items.Add("PCMSGetMatch(tripID, 0, result, 256)")
            Me.ListBox1.Items.Add("result = " + result.ToString)

            Dim address As StringBuilder = New StringBuilder(256)
            Dim city As StringBuilder = New StringBuilder(256)
            Dim state As StringBuilder = New StringBuilder(3)
            Dim zip As StringBuilder = New StringBuilder(10)
            Dim county As StringBuilder = New StringBuilder(256)
            ret = PCMDLLINT.PCM.PCMSGetFmtMatch2(tripID, 0, address, 256, city, 256, state, 3, zip, 10, county, 256)
            Me.ListBox1.Items.Add("ret = PCMSGetFmtMatch2(tripID, 0, address, 256, city, 256, state, 3, zip, 10, county, 256)")
            Me.ListBox1.Items.Add("ret = " + ret.ToString)
            Me.ListBox1.Items.Add("address = " + address.ToString + " city = " + city.ToString + " state = " + state.ToString + " zip = " + zip.ToString + " county = " + county.ToString)
        End If

        ret = PCMDLLINT.PCM.PCMSAddStop(tripID, "Princeton,NJ;1000 herrontown road")
        Me.ListBox1.Items.Add("ret = PCMSAddStop(tripID, ""Princeton,NJ;1000 herrontown road"")")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        ret = PCMDLLINT.PCM.PCMSAddStop(tripID, "Warminster,PA;1174 Nassau road")
        Me.ListBox1.Items.Add("ret = PCMSAddStop(tripID, ""Warminster,PA;1174 Nassau road"")")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        ret = PCMDLLINT.PCM.PCMSCalculate(tripID)
        Me.ListBox1.Items.Add("ret = PCMSCalculate(tripID)")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        PCMDLLINT.PCM.PCMSSetCalcTypeEx(tripID, 1, 1024, 0)
        Me.ListBox1.Items.Add("                                                           Set routing options to Practical, 53 foot, vehicle truck")
        Me.ListBox1.Items.Add("PCMSSetCalcTypeEx(tripID,1,1024,0)")

        ret = PCMDLLINT.PCM.PCMSCalculate(tripID)
        Me.ListBox1.Items.Add("ret = PCMSCalculate(tripID)")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        ByteCount = PCMDLLINT.PCM.PCMSNumHTMLRptBytes(tripID, 0)
        Me.ListBox1.Items.Add("ByteCount = PCMSNumHTMLRptBytes(tripID, 0)")
        Me.ListBox1.Items.Add("ByteCount = " + ByteCount.ToString)

        If ByteCount > 0 Then
            Dim reportstring As StringBuilder = New StringBuilder(ByteCount + 1)
            ret = PCMDLLINT.PCM.PCMSGetHTMLRpt(tripID, 0, reportstring, ByteCount + 1)
            Me.ListBox1.Items.Add("ret = PCMSGetHTMLRpt(tripID, 0, reportstring, ByteCount + 1)")
            Me.ListBox1.Items.Add("ret = " + ret.ToString)
            Me.ListBox1.Items.Add("                                                           See HTML report in lower Window")
            Me.RichTextBox1.AppendText(reportstring.ToString)
        End If

        PCMDLLINT.PCM.PCMSDeleteTrip(tripID)
        Me.ListBox1.Items.Add("PCMSDeleteTrip(tripID)")

        ret = PCMDLLINT.PCM.PCMSCalcDistance(Server, "Boston,MA", "Dallas,TX")
        Me.ListBox1.Items.Add("ret = PCMSCalcDistance(Server, ""Boston,MA"", ""Dallas,TX"")")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        'ret = PCMDLLINT.PCM.PCMSSetCalcTypeEx

        ret = PCMDLLINT.PCM.PCMSCloseServer(Server)
        Me.ListBox1.Items.Add("ret = PCMSCloseServer(Server)")
        Me.ListBox1.Items.Add("ret = " + ret.ToString)

        
    End Sub

    'Form overrides dispose to clean up the component list.
    Protected Overloads Overrides Sub Dispose(ByVal disposing As Boolean)
        If disposing Then
            If Not (components Is Nothing) Then
                components.Dispose()
            End If
        End If
        MyBase.Dispose(disposing)
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    Friend WithEvents ListBox1 As System.Windows.Forms.ListBox
    Friend WithEvents RichTextBox1 As System.Windows.Forms.RichTextBox
    <System.Diagnostics.DebuggerStepThrough()> Private Sub InitializeComponent()
        Me.ListBox1 = New System.Windows.Forms.ListBox
        Me.RichTextBox1 = New System.Windows.Forms.RichTextBox
        Me.SuspendLayout()
        '
        'ListBox1
        '
        Me.ListBox1.Location = New System.Drawing.Point(0, 0)
        Me.ListBox1.Name = "ListBox1"
        Me.ListBox1.Size = New System.Drawing.Size(856, 485)
        Me.ListBox1.TabIndex = 0
        '
        'RichTextBox1
        '
        Me.RichTextBox1.Location = New System.Drawing.Point(0, 496)
        Me.RichTextBox1.Name = "RichTextBox1"
        Me.RichTextBox1.Size = New System.Drawing.Size(856, 280)
        Me.RichTextBox1.TabIndex = 1
        Me.RichTextBox1.Text = ""
        '
        'Form1
        '
        Me.AutoScaleBaseSize = New System.Drawing.Size(5, 13)
        Me.ClientSize = New System.Drawing.Size(864, 778)
        Me.Controls.Add(Me.RichTextBox1)
        Me.Controls.Add(Me.ListBox1)
        Me.Name = "Form1"
        Me.Text = "VB.Net Test PCM Functions"
        Me.ResumeLayout(False)

    End Sub

#End Region

End Class
