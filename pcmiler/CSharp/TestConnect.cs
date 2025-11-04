using System;
using System.Drawing;
using System.Collections;
using System.ComponentModel;
using System.Windows.Forms;
using System.Data;
using System.Text;
using PCMDLLINT;

namespace WindowsApplication1
{
	/// <summary>
	/// Summary description for Form1.
	/// </summary>
	public class Form1 : System.Windows.Forms.Form
	{
		private System.Windows.Forms.ListBox listBox1;
		private System.Windows.Forms.RichTextBox richTextBox1;
		/// <summary>
		/// Required designer variable.
		/// </summary>
		private System.ComponentModel.Container components = null;

		public Form1()
		{
			//
			// Required for Windows Form Designer support
			//
			InitializeComponent();

			short Server = PCMDLLINT.PCM.PCMSOpenServer(0,0);
			this.listBox1.Items.Add("Server = PCMSOpenServer(0, 0)");
			this.listBox1.Items.Add("Server = " + Server);

			int tripID = PCMDLLINT.PCM.PCMSNewTrip(Server);
			this.listBox1.Items.Add("tripID = PCMSNewTrip(Server)");
			this.listBox1.Items.Add("tripID = " + tripID);

			int matches = PCMDLLINT.PCM.PCMSLookup(tripID, "Princeton,NJ;1000 herrontown road", 0);
			this.listBox1.Items.Add("matches = PCMSLookup(tripID, Princeton,NJ;1000 herrontown road, 0)");
			this.listBox1.Items.Add("matches = " + matches);
			int ByteCount;
			int ret;
			if(matches == 1)
			{
				StringBuilder result = new StringBuilder(256);
				PCMDLLINT.PCM.PCMSGetMatch(tripID, 0, result, 256);
				this.listBox1.Items.Add("PCMSGetMatch(tripID, 0, result, 256)");
				this.listBox1.Items.Add("result = " + result);

				StringBuilder address = new StringBuilder(256);
				StringBuilder city = new StringBuilder(256);
				StringBuilder state = new StringBuilder(3);
				StringBuilder zip = new StringBuilder(10);
				StringBuilder county = new StringBuilder(256);
				ret =  PCMDLLINT.PCM.PCMSGetFmtMatch2(tripID, 0, address, 256, city, 256, state, 3, zip, 10, county, 256);
				this.listBox1.Items.Add("ret = PCMSGetFmtMatch2(tripID, 0, address, 256, city, 256, state, 3, zip, 10, county, 256)");
				this.listBox1.Items.Add("ret = " + ret);
				this.listBox1.Items.Add("address = " + address + " city = " + city + " state = " + state + " zip = " + zip + " county = " + county);
			}
		ret = PCMDLLINT.PCM.PCMSAddStop(tripID, "Princeton,NJ;1000 herrontown road");
        this.listBox1.Items.Add("ret = PCMSAddStop(tripID, Princeton,NJ;1000 herrontown road)");
        this.listBox1.Items.Add("ret = " + ret);

        ret = PCMDLLINT.PCM.PCMSAddStop(tripID, "Warminster,PA;1174 Nassau road");
        this.listBox1.Items.Add("ret = PCMSAddStop(tripID, Warminster,PA;1174 Nassau road)");
        this.listBox1.Items.Add("ret = " + ret);

        ret = PCMDLLINT.PCM.PCMSCalculate(tripID);
		this.listBox1.Items.Add("ret = PCMSCalculate(tripID)");
        this.listBox1.Items.Add("ret = " + ret);

		PCMDLLINT.PCM.PCMSSetCalcTypeEx(tripID,1,1024,0);
		this.listBox1.Items.Add("                                                           Set routing options to Practical, 53 foot, vehicle truck");
		this.listBox1.Items.Add("PCMSSetCalcTypeEx(tripID,1,1024,0)");
		
		ret = PCMDLLINT.PCM.PCMSCalculate(tripID);
		this.listBox1.Items.Add("ret = PCMSCalculate(tripID)");
		this.listBox1.Items.Add("ret = " + ret);	

        ByteCount = PCMDLLINT.PCM.PCMSNumHTMLRptBytes(tripID, 0);
        this.listBox1.Items.Add("ByteCount = PCMSNumHTMLRptBytes(tripID, 0)");
        this.listBox1.Items.Add("ByteCount = " + ByteCount);

        if(ByteCount > 0)
			{
            StringBuilder reportstring = new StringBuilder(ByteCount + 1);
            ret = PCMDLLINT.PCM.PCMSGetHTMLRpt(tripID, 0, reportstring, ByteCount + 1);
            this.listBox1.Items.Add("ret = PCMSGetHTMLRpt(tripID, 0, reportstring, ByteCount + 1)");
            this.listBox1.Items.Add("ret = " + ret);
            this.listBox1.Items.Add("                                                           See HTML report in lower Window");
            this.richTextBox1.AppendText(reportstring.ToString());
			}

        PCMDLLINT.PCM.PCMSDeleteTrip(tripID);
		this.listBox1.Items.Add("PCMSDeleteTrip(tripID)");
        ret = PCMDLLINT.PCM.PCMSCalcDistance(Server, "Boston,MA", "Dallas,TX");
        this.listBox1.Items.Add("ret = PCMSCalcDistance(Server, Boston,MA, Dallas,TX)");
        this.listBox1.Items.Add("ret = " + ret);

		
        ret = PCMDLLINT.PCM.PCMSCloseServer(Server);
		this.listBox1.Items.Add("ret = PCMSCloseServer(Server)");
        this.listBox1.Items.Add("ret = " + ret);
		PCMDLLINT.PCM.PCMSDeleteTrip(tripID);

			//
			// TODO: Add any constructor code after InitializeComponent call
			//
		}

		/// <summary>
		/// Clean up any resources being used.
		/// </summary>
		protected override void Dispose( bool disposing )
		{
			if( disposing )
			{
				if (components != null) 
				{
					components.Dispose();
				}
			}
			base.Dispose( disposing );
		}

		#region Windows Form Designer generated code
		/// <summary>
		/// Required method for Designer support - do not modify
		/// the contents of this method with the code editor.
		/// </summary>
		private void InitializeComponent()
		{
			this.listBox1 = new System.Windows.Forms.ListBox();
			this.richTextBox1 = new System.Windows.Forms.RichTextBox();
			this.SuspendLayout();
			// 
			// listBox1
			// 
			this.listBox1.Location = new System.Drawing.Point(8, 8);
			this.listBox1.Name = "listBox1";
			this.listBox1.Size = new System.Drawing.Size(776, 446);
			this.listBox1.TabIndex = 0;
			// 
			// richTextBox1
			// 
			this.richTextBox1.Location = new System.Drawing.Point(8, 464);
			this.richTextBox1.Name = "richTextBox1";
			this.richTextBox1.Size = new System.Drawing.Size(776, 288);
			this.richTextBox1.TabIndex = 1;
			this.richTextBox1.Text = "";
			// 
			// Form1
			// 
			this.AutoScaleBaseSize = new System.Drawing.Size(5, 13);
			this.ClientSize = new System.Drawing.Size(808, 770);
			this.Controls.Add(this.richTextBox1);
			this.Controls.Add(this.listBox1);
			this.Name = "Form1";
			this.Text = "C#.Net Test PCM Functions";
			this.ResumeLayout(false);

		}
		#endregion

		/// <summary>
		/// The main entry point for the application.
		/// </summary>
		[STAThread]
		static void Main() 
		{
			Application.Run(new Form1());
		}
	}
}
