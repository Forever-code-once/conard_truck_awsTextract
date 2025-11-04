program ConnectDemo;

uses
  Forms,
  PCMSTRIP in 'PCMSTRIP.PAS',
  PCMSDE32 in 'PCMSDE32.pas' {Form1};

{$R *.RES}

begin
  Application.Initialize;
  Application.CreateForm(TForm1, Form1);
  Application.Run;
end.
