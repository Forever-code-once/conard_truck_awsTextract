cd alk
cd connect
cd results
rm *.html
cd ..
rm *.html
cd ..
rm *.html
cd ..

set CLASSPATH=..\src;
javadoc -sourcepath ..\src alk.connect alk.connect.results
