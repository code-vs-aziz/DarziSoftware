; Darzibaba Desktop Installer — compile after running scripts\prepare-installer.ps1
#define MyAppName "Darzibaba"
#define MyAppVersion "1.0"
#define MyAppPublisher "Darzi Baba Tailors"
#define MyAppExeName "Darzibaba.exe"
#define StagingRel "staging"

; Compile from installer\ folder after prepare-installer.ps1 (staging\ must exist).
[Setup]
AppId={{A7B3C9D1-4E2F-5A6B-8C0D-1E2F3A4B5C6D}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
DefaultDirName={autopf}\Darzibaba
DefaultGroupName={#MyAppName}
DisableProgramGroupPage=yes
OutputDir=output
OutputBaseFilename=Darzibaba-Setup
SetupIconFile=
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=admin
ArchitecturesInstallIn64BitMode=x64
UninstallDisplayIcon={app}\{#MyAppExeName}

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Files]
Source: "{#StagingRel}\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: "install.cfg"
Source: "scripts\*"; DestDir: "{app}\installer-scripts"; Flags: ignoreversion recursesubdirs; AfterInstall: EnsureScriptPerms

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\start-darzibaba.bat"; WorkingDir: "{app}"; IconFilename: "{app}\{#MyAppExeName}"
Name: "{group}\Stop {#MyAppName}"; Filename: "{app}\stop-darzibaba.bat"; WorkingDir: "{app}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\start-darzibaba.bat"; Tasks: desktopicon; WorkingDir: "{app}"; IconFilename: "{app}\{#MyAppExeName}"

[Run]
Filename: "{app}\start-darzibaba.bat"; Description: "Launch {#MyAppName}"; Flags: nowait postinstall skipifsilent

[UninstallDelete]
Type: filesandordirs; Name: "{app}"

[Code]
var
  ConfigPage: TInputQueryWizardPage;
  XamppPage: TInputQueryWizardPage;

function ConfigDir(Param: string): string;
begin
  Result := ExpandConstant('{app}');
end;

procedure EnsureScriptPerms();
begin
end;

function IsXamppRoot(const Path: string): Boolean;
begin
  Result := DirExists(Path) and FileExists(AddBackslash(Path) + 'mysql\bin\mysqld.exe');
end;

function AutoDetectXampp: string;
begin
  if IsXamppRoot('C:\xampp') then
    Result := 'C:\xampp'
  else if IsXamppRoot('D:\xampp') then
    Result := 'D:\xampp'
  else
    Result := 'C:\xampp';
end;

procedure InitializeWizard();
begin
  XamppPage := CreateInputQueryPage(wpSelectDir,
    'XAMPP location (MySQL only)',
    'Select the folder where XAMPP is installed',
    'Apache is not used. Darzibaba uses PHP Desktop for the app window and XAMPP only for MySQL.' + #13#10 +
    'Your database must already exist (installer does not import data).');

  XamppPage.Add('XAMPP root folder:', False);
  XamppPage.Values[0] := AutoDetectXampp;

  ConfigPage := CreateInputQueryPage(XamppPage.ID,
    'MySQL connection',
    'Enter settings matching your XAMPP MySQL',
    'The MySQL service port in XAMPP my.ini must match the port you enter here.');

  ConfigPage.Add('MySQL port:', False);
  ConfigPage.Add('Hostname:', False);
  ConfigPage.Add('Database name:', False);
  ConfigPage.Add('Username:', False);
  ConfigPage.Add('Password (optional):', True);

  ConfigPage.Values[0] := '3310';
  ConfigPage.Values[1] := 'localhost';
  ConfigPage.Values[2] := 'alibaba';
  ConfigPage.Values[3] := 'root';
  ConfigPage.Values[4] := '';
end;

function NextButtonClick(CurPageID: Integer): Boolean;
begin
  Result := True;
  if CurPageID = XamppPage.ID then
  begin
    if not IsXamppRoot(XamppPage.Values[0]) then
    begin
      MsgBox('Invalid XAMPP folder. Expected mysql\bin\mysqld.exe under:' + #13#10 + XamppPage.Values[0], mbError, MB_OK);
      Result := False;
    end;
  end;
  if CurPageID = ConfigPage.ID then
  begin
    if Trim(ConfigPage.Values[0]) = '' then
    begin
      MsgBox('MySQL port is required.', mbError, MB_OK);
      Result := False;
    end;
  end;
end;

procedure WriteInstallConfig();
var
  CfgPath, AppDir, Xampp, Port, Host, DbName, DbUser, DbPass: string;
  ResultCode: Integer;
  Ps1, Args: string;
begin
  AppDir := ExpandConstant('{app}');
  CfgPath := AppDir + '\install.cfg';
  Xampp := XamppPage.Values[0];
  Port := ConfigPage.Values[0];
  Host := ConfigPage.Values[1];
  DbName := ConfigPage.Values[2];
  DbUser := ConfigPage.Values[3];
  DbPass := ConfigPage.Values[4];

  Ps1 := AppDir + '\installer-scripts\write-install-cfg.ps1';
  Args := '-ExecutionPolicy Bypass -NoProfile -File "' + Ps1 + '" -CfgPath "' + CfgPath + '" -XamppRoot "' + Xampp + '" -Hostname "' + Host + '" -Port "' + Port + '" -Username "' + DbUser + '" -Password "' + DbPass + '" -Database "' + DbName + '" -AppDir "' + AppDir + '"';

  if not Exec('powershell.exe', Args, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then
    MsgBox('Could not write install.cfg (code ' + IntToStr(ResultCode) + ').', mbError, MB_OK);
end;

procedure PatchDatabasePhp();
var
  DbPhp, Ps1, Args: string;
  ResultCode: Integer;
begin
  DbPhp := ExpandConstant('{app}') + '\www\application\config\database.php';
  Ps1 := ExpandConstant('{app}') + '\installer-scripts\patch-database.ps1';
  Args := '-ExecutionPolicy Bypass -NoProfile -File "' + Ps1 + '" -DatabasePhpPath "' + DbPhp + '" -Hostname "' + ConfigPage.Values[1] + '" -Port "' + ConfigPage.Values[0] + '" -Username "' + ConfigPage.Values[3] + '" -Password "' + ConfigPage.Values[4] + '" -Database "' + ConfigPage.Values[2] + '"';

  if not Exec('powershell.exe', Args, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then
    MsgBox('Could not update database.php (code ' + IntToStr(ResultCode) + '). Edit it manually if needed.', mbError, MB_OK);
end;

procedure CurStepChanged(CurStep: TSetupStep);
begin
  if CurStep = ssPostInstall then
  begin
    WriteInstallConfig();
    PatchDatabasePhp();
  end;
end;
