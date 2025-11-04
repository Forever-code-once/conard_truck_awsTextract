unit Unit1;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  StdCtrls, comobj;

type
  TForm1 = class(TForm)
    Button1: TButton;
    Result: TEdit;
    Origin: TEdit;
    Destination: TEdit;
    OriginLbl: TLabel;
    Label1: TLabel;
    procedure TestClick(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  Form1: TForm1;

implementation

{$R *.DFM}

procedure TForm1.TestClick(Sender: TObject);
var
   srv: OleVariant;

   trip: OleVariant;
   miles: Extended;
begin
   result.Text:= '';
   srv:= CreateOleObject('PCMServer.PCMServer');

   if (srv.ID <= 0) then
   begin
        Application.MessageBox('Server Error', 'Error', IDOK);
        Exit;
   end;

   trip:= srv.NewTrip('NA');
   if (trip.ID <= 0) then
   begin
        Application.MessageBox('Trip Error', 'Error', IDOK);
        Exit;
   end;
   trip.AddStop (Origin.Text);
   trip.AddStop (Destination.Text);

   miles:= trip.TravelDistance / 10.0;

   result.Text:= FloatToStr(miles) + ' miles';
end;

end.
