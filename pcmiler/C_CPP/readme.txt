C_CPP
-----
Contains source for accessing Server DLL from C/C++.  Visual C++ users should
typically refer to files in this folder for compile-time linking to Server DLL
(using PCMSRV32.LIB).  Users of other compilers should refer to the Wrapper 
folder for an example of run-time loading (using LoadLibrary). 

C_CPP Directory Contents
------------------------
PCMSTrip.h    - DLL C-interface header file (for use with PCMSRV32.LIB).
PCMSDefs.h    - Common defines and types (included by PCMSTrip.h).
PCMSInit.h    - DLL initialization header file (included by PCMSTrip.h).
PCMSTest.cpp  - Example usage of Server DLL.
Wrapper <DIR> - Contains source for run-time loading of Server DLL.

