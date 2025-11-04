<?
//This file will actually be run in C# ("C Sharp"), not really PHP.
//TEST CASES for the File Renamer Application string processing.              ...MRR created on Jan. 3rd, 2019 
//Reminder:  Search and replace strings cannot contain the following characters: '<', '>', ':', '"', '/', '\', '|', '?', '*'.
//Options:   fullname – find/replace is done on filename with file extension included.
//           filename – find/replace on filename only, e.g. without file extension.
//           extension – find/replace on file extension only.
//Start index must be > 0. This indicates what character in the original string to start the search on.  1-indexed for humans, not 0-indexed like most C-based languages.

//Format for test cases....
//<advanced_replace, 
//	"{search string}", 
//	"{replace string}", 
//	{# of occurrences to replace}|all,
//	{# of characters to search in}|all,
//	{start index},
//	fullname|filename|extension
//	[,regex]
//	[,case]
//	[,upper|lower|proper]
//	[,trim]>


//base declarations and versy simple test
var original = "test cases.txt";
var args = new string[] { "\"cases\"", "\"fired\"", "all", "all", "1", "fullname" };
var expected = "test fired.txt"; 
var actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::CORE/BASIC USE CASES::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//...replace the extension...successful
original = "mrr_test_file.jpeg";
args = new string[] { "\"jpeg\"", "\"jpg\"", "all", "all", "1", "extension" };
expected = "mrr_test_file.jpg";

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the extension...failed... no match (or the file no longer has a valid extension if the "." is no longer present... but included as part of the extension)
original = "mrr_test_file.jpeg";
args = new string[] { "\".jpeg\"", "\"jpg\"", "all", "all", "1", "extension" };
expected = "mrr_test_file.jpeg";			
//expected = "mrr_test_filejpg";		//----> this one should trigger an error if the extension is no longer valid... or not matter at all if the "." is not considered part of the extension.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the fullname...failed... match, but the file no longer would have a valid extension if no "." retained
original = "mrr_test_file.jpeg";
args = new string[] { "\".jpeg\"", "\"jpg\"", "all", "all", "1", "fullname" };
expected = "mrr_test_filejpg";		//----> this one should trigger an error if the extension is no longer valid... or not mater at all if the "." is not considered part of the extension.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);



//...replace the full name...good.  Two matches found and 2 replaced.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"jpg\"", "all", "all", "1", "fullname" };
expected = "mrr_jpg_file.jpg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...good.  Two matches found and 1 replaced (first one).
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"jpg\"", "1", "all", "1", "fullname" };
expected = "mrr_jpg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...good.  Two matches found and 1 replaced (after position 9).
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"jpg\"", "1", "all", "9", "fullname" };
expected = "mrr_jpeg_file.jpg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//...replace the full name...good (maybe).  Two matches found and 2 replaced.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"_jpg\"", "all", "all", "1", "fullname" };
expected = "mrr__jpg_file._jpg";		//---->  while this will technically work, this might create an invalid file type, and thus be an error.  Not sure if there should be safegaurds... at least default to filename

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the filename...good.  One match found and 1 replaced.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"_jpg\"", "all", "all", "1", "filename" };
expected = "mrr__jpg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//...replace the full name...good.  starting at position 4, look for up to five characters to replace...which looks good 
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"-jpg\"", "1", "5", "4", "fullname" };
expected = "mrr_-jpg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...works, but should fail to replace anything at all.  Starting position is past the match, and the extension is out of range. No replacement made.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"-jpg\"", "1", "5", "6", "fullname" };
expected = "mrr_jpeg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...Fail. Search string is larger than the amount of characters that can be operated on... "jpeg" > 3 characters.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"-jpg\"", "1", "3", "5", "fullname" };
expected = "mrr_jpeg_file.jpeg";		//----> No replacements made... Error message should display to tell them WHY it failed.	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...Good. Same as above, but this time it should have replaced the string since "jpg" == 3. :)
original = "mrr_jpg_file.jpeg";
args = new string[] { "\"jpg\"", "\"-jpg\"", "1", "3", "5", "fullname" };
expected = "mrr_-jpg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...Fail. Start is out of range of entire string.  Or, this could be considered "good", but nothing would happen with this file.
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"jpeg\"", "\"-jpg\"", "1", "3", "20", "fullname" };
expected = "mrr_jpeg_file.jpeg";		//----> No replacements made... Error message should display to tell them WHY it failed.	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::OPTIONAL CASES:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//...replace the full name...Fail. Case sensitive, so no match found
original = "mrr_jpeg_file.jpeg";
args = new string[] { "\"JPEG\"", "\"-jpg\"", "1", "3", "4", "fullname", "case" };
expected = "mrr_jpeg_file.jpeg";		//----> No replacements made.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case sensitive, so found and replaced 1
original = "mrr_JPEG_file.JPG";
args = new string[] { "\"JPEG\"", "\"-jpg\"", "all", "all", "1", "fullname", "case" };
expected = "mrr_-jpg_file.JPG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case sensitive, found and replaced 2 (all)
original = "mrr_JPEG_file.JPEG";
args = new string[] { "\"JPEG\"", "\"jpg\"", "all", "all", "1", "fullname", "case" };
expected = "mrr_jpg_file.jpg";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case sensitive, found one after position 10 and replaced it.
original = "mrr_JPEG_file.JPEG";
args = new string[] { "\"JPEG\"", "\"jpg\"", "all", "all", "10", "fullname", "case" };
expected = "mrr_JPEG_file.jpg";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case sensitive, found 2, but only replaced 1
original = "mrr_JPEG_file.JPEG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "1", "all", "1", "fullname", "case" };
expected = "mrr_zzz_file.JPEG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//...replace the full name...GOOD. Case sensitive, found 4, but only 3 after starting position... replaced first 2 after position 10.
original = "mrr_JPEG_jpeg_JPEG_jpeg_JPEG_file.JPEG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "2", "all", "10", "fullname", "case" };
expected = "mrr_JPEG_jpeg_zzz_jpeg_zzz_file.JPEG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case insensitive, found 6, but only 5 after starting position... replaced first 2 after position 10.
original = "mrr_JPEG_jpeg_JPEG_jpeg_JPEG_file.JPEG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "2", "all", "10", "fullname" };
expected = "mrr_JPEG_zzz_zzz_jpeg_JPEG_file.JPEG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);



//...replace the full name...GOOD. Case sensitive, found and replaced 1, then converted to lowercase.
original = "mrr_JPEG_file.JPG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "1", "all", "1", "fullname", "case", "lower" };
expected = "mrr_zzz_file.jpg";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Case sensitive, found and replaced 1, then converted to uppercase.  This should also prove that the conversion is happening AFTER the replacement... unless the "zzz" part is still lowercase in hte result. (?)
original = "mrr_JPEG_file.JPG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "1", "all", "1", "fullname", "upper" };
expected = "MRR_ZZZ_FILE.JPG";	
//expected = "MRR_zzz_FILE.JPG";			//----> if this is the return string, then the Uppercase conversion is happening BEFORE the replace.  In this example the find would work, and so would the replace.  The only question is if it is "ZZZ" or "zzz" in it.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);



//...replace the full name...GOOD. Case sensitive, found and replaced 1, then converted to lowercase, then trimmed white space around the FULL NAME.  Note that there is still a space before the extension.
original = " mrr_JPEG_file .JPG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "1", "all", "1", "fullname", "case", "lower" ,"trim"};
expected = "mrr_zzz_file .jpg";	
//expected = "mrr_jpeg_file .jpg";			//Just in case, if this is the result, then the lowercase conversion may be happening first, so the find/replace never happened.  Based on your notes, I'd say this result should not happen in favor of the last one.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the file name...GOOD. Case sensitive, found and replaced 1, then converted to uppercase, then trimmed white space around the FILENAME.  Should have "ZZZ" in the result, and not "zzz", or the uppercase conversion is happening BEFORE the replacement.
original = " mrr_JPEG_file .JPG";
args = new string[] { "\"JPEG\"", "\"zzz\"", "1", "all", "1", "filename", "case", "upper" ,"trim"};
expected = "MRR_ZZZ_FILE.JPG";	
//expected = "MRR_zzz_FILE.JPG";			//----> if this is the return string, then the Uppercase conversion is happening BEFORE the replace.  In this example the find would work, and so would the replace.  The only question is if it is "ZZZ" or "zzz" in it.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//...replace the file name...GOOD. Case sensitive, found and replaced 1 (with space), then converted to uppercase, then trimmed white space around the FILENAME.  Should have "ZZZ " in the result, and not "zzz ", or the uppercase conversion is happening BEFORE the replacement.
original = " mrr_JPEG_file .JPG";
args = new string[] { "\"JPEG\"", "\"zzz \"", "1", "all", "1", "filename", "case", "upper" ,"trim"};
expected = "MRR_ZZZ _FILE.JPG";	
//expected = "MRR_zzz _FILE.JPG";			//----> if this is the return string, then the Uppercase conversion is happening BEFORE the replace.  In this example the find would work, and so would the replace.  The only question is if it is "ZZZ " or "zzz " in it.

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//...replace the file name...GOOD. Case sensitive, found and replaced 1 (with leading white space), then converted to uppercase, then trimmed white space around the FILENAME.  Should have "MRR-" in the result, and not "mrr_", or the uppercase conversion is happening BEFORE the replacement.
original = " mrr_JPEG_file .JPG";
args = new string[] { "\"mrr_\"", "\" mrr-\"", "all", "all", "1", "filename", "case", "upper" ,"trim"};
expected = "MRR-JPEG_FILE.JPG";	
//expected = "MRR_JPEG_FILE.JPG";			//----> If filename has "MRR_" instead of "MRR-", find/replace did NOT work...  Uppercase conversion might have happened BEFORE the find/replace could match it. 
//expected = " MRR-JPEG_FILE.JPG";			//----> If leading space is still in the filename, then the TRIM is happening BEFORE the find/replace

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::REGEX CASES::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//Example Input string...  "/content/alternate-1.aspx"
//Example regex use C#...  "content/([A-Za-z0-9\-]+)\.aspx$"                 	Output would be  "alternate-1" for the match.

//Reg Expression meanings/pattern details from REGEX usage above:
//	content/        The group must follow this string.
//	[A-Za-z0-9\-]+  One or more alphanumeric characters.
//	(...)           A separate group.
//	\.aspx          This must come after the group.
//	$               Matches the end of the string.

//Example Input string...  "4 and 5"
//Example regex use C#...  "\d"										Output would be "4" for the first match, and "5" for the second.

//Reg Expression meanings/pattern details from REGEX usage above:
//	\d        	 each digit (as a separate match)

//Example Input string...  "Dot 55 Perls"
//Example regex use C#...  "\d+"										Output would be "55" for the match

//Reg Expression meanings/pattern details from REGEX usage above:
//	\d+        	 digits

//...replace the full name...GOOD. Replaced prefix and extension on file.
original = "mrr_JPEG000file.JPG";
args = new string[] { "\"mrr_([A-Za-z0-9\\-]+)\\.JPG$\"", "\"new_$1.jpeg\"", "all", "all", "1", "fullname", "regex"};
expected = "new_JPEG000file.jpeg";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Replaced number/digit block with x
original = "mrr_JPEG789file.JPG";
args = new string[] { "\"(\d+)\"", "\"x\"", "all", "all", "1", "fullname", "regex"};
expected = "mrr_JPEGxfile.JPG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...GOOD. Replaced EACH number/digit with x
original = "mrr_JPEG789file.JPG";
args = new string[] { "\"(\d)\"", "\"x\"", "all", "all", "1", "fullname", "regex"};
expected = "mrr_JPEGxxxfile.JPG";	

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::SPECIAL CASES::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//...replace the filename...CONCERN.  One match found for space and replaced with bad character.  REPLACE was a [TAB].  
//Question: will the input form use the TAB key to move to another field or use the TAB space in the box for the REPLACE text... or replaces it with a "\t"?  Same issue if the ENTER key can be used to create a "\r" or "\n" (or both).
original = "mrr jpeg_file.jpeg";
args = new string[] { "\" \"", "\"	\"", "all", "all", "1", "filename" };
expected = "mrr\tjpeg_file.jpeg";		//----->Should give an error...assuming that it recognizes that the REPLACE character is a TAB in the first place.  

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the filename...Good.  One match found for space and replaced with nothing.  
original = "mrr jpeg_file.jpeg";
args = new string[] { "\" \"", "\"\"", "all", "all", "1", "filename" };
expected = "mrrjpeg_file.jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);


////...replace the filename...????.  Depends on how much error checking you do in the code, but this case may match every file and name it the same thing...or try, or do nothing at all.  
//original = "mrr jpeg_file.jpeg";
//args = new string[] { "\"\"", "\" \"", "all", "all", "1", "filename" };
//expected = ".jpeg";		//-------> Unless the REPLACE string is bad, it won't matter what the value is... spaces, a character, a text str, etc.  Might be a major problem.  OR, it will do nothing at all.  :)
//
//actual = ADM.AdvancedReplace(original, args);
//Assert.AreEqual(expected, actual);
//
////...replace the filename...????.  Depends on how much error checking you do in the code, but this case may match every file and name it the same thing...or try, or do nothing at all.   
//original = "mrr jpeg_file.jpeg";
//args = new string[] { "\"\"", "\"999\"", "all", "all", "1", "filename" };
//expected = "999.jpeg";		//-------> Unless the REPLACE string is bad, it won't matter what the value is... spaces, a character, a text str, etc.  Might be a major problem.  OR, it will do nothing at all.  :)
//
//actual = ADM.AdvancedReplace(original, args);
//Assert.AreEqual(expected, actual);
//
////...replace the filename...????.  Depends on how much error checking you do in the code, but this case may match every file and name it the same thing...or try, or do nothing at all. 
//original = "mrr jpeg_file.jpeg";
//args = new string[] { "\"\"", "\"\"", "all", "all", "1", "filename" };
//expected = ".jpeg";		
//
//actual = ADM.AdvancedReplace(original, args);
//Assert.AreEqual(expected, actual);

//...replace the filename...????.  Depends on how much error checking you do in the code, but this case may allow a file to effectively be blanked out with nothing left to name it... which now looks like a system file. (Ex: .htaccess)
original = "a.jpeg";
args = new string[] { "\"a\"", "\"\"", "all", "all", "1", "filename" };
expected = ".jpeg";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);

//...replace the full name...????.  Depends on how much error checking you do in the code, but this case may allow a file to effectively become a file it is not 
original = "styles.css";
args = new string[] { "\"styles.c\"", "\".htacce\"", "all", "all", "1", "fullname" };
expected = ".htaccess";		

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);





/*
//
original = "";
args = new string[] { "\"\"", "\"\"", "all", "all", "1", "filename" };
expected = "";

actual = ADM.AdvancedReplace(original, args);
Assert.AreEqual(expected, actual);
*/





/*
//base declarations and very simple tests... only need the first four lines here if declaration is not already included.
var original = "txt";
var args = new string[] { "" };
var expected = "Txt"; 
var actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);
*/


//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Random Number Generator::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

var nums = new List<int>();
var rand = new Random(1);
var preGeneratedNumbers = new List<int>();


//basic example with smaller range...
for (var i = 0; i < 10; i++)
{
 	preGeneratedNumbers.Add(rand.Next(1, 100));
}

rand = new Random(1);

for (int i = 0; i < 10; i++)
{
	var args = new string[] { "1", "100", "\"000\"" };
     string expected = preGeneratedNumbers[i].ToString().PadLeft(3, '0');
     string actual = ADM.RandomNum(ref nums, args, ref rand);
     Assert.AreEqual(expected, actual); 
}


//more advanced example with larger range and unique numbers generated,
for (var i = 0; i < 100; i++)
{
 	preGeneratedNumbers.Add(rand.Next(1, 1000));
}

rand = new Random(1);

for (int i = 0; i < 100; i++)
{
	var args = new string[] { "1", "1000", "\"0000\"", "unique" };
     string expected = preGeneratedNumbers[i].ToString().PadLeft(3, '0');
     string actual = ADM.RandomNum(ref nums, args, ref rand);
     Assert.AreEqual(expected, actual); 
}

//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::












//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::OverWrite::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//replace string from postition 7.
original = "myfilenamer";
args = new string[] { "7", "\"nombre\"" };
expected = "myfilenombre";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

//take name from above and replace at position 3
args = new string[] { "3", "\"home\"" };
expected = "myhomenombre";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

//take name from above and replace at position 3
args = new string[] { "3", "\"homesweethome\"" };
expected = "myhomesweethome";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

//replace string from postition 7.
original = "DARTHvader";
args = new string[] { "6", "\"vader\"", "upper"};
expected = "DARTHVADER";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

args = new string[] { "1", "\"sith \"", "proper"};
expected = "Sith Vader";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

args = new string[] { "5", "\"lord vader    \"", "proper", "trim"};
expected = "Sith Lord Vader";	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

args = new string[] { "5", "\"happens\"", "proper", "trim"};
expected = "Sith Happensder";	
//expected = "Sith Happens";  			//Alternate: if the overwritten string also includes the end of the string. 	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

args = new string[] { "13", "\"!!!\"", "proper", "trim"};
expected = "Sith Happens!!!";				//possible error is the previous one stops the string before position 13	
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);

args = new string[] { "20", "\"!!!\"", "proper", "trim"};
expected = "Sith Happens!!!    !!!";		//Possible ERROR is the previous one stops the string before position 13.  Or if this will add to the string if shorter than the position, this will be the output.
actual = ADM.Overwrite(original, args);
Assert.AreEqual(expected, actual);


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Ext Tests:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base extension
original = "jpg";
args = new string[] { "upper" };
expected = "JPG";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//base extension with the "."
original = ".jpg";
args = new string[] { "upper" };
expected = ".JPG";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//base extension
original = "jpeg";
args = new string[] { "proper" };
expected = "Jpeg";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//base extension with the "."
original = ".jpeg";
args = new string[] { "proper" };
expected = ".Jpeg";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//base extension
original = "PNG";
args = new string[] { "lower" };
expected = "png";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//base extension with the "."
original = ".PNG";
args = new string[] { "lower" };
expected = ".png";
actual = ADM.Ext(original, args);
Assert.AreEqual(expected, actual);

//====================================================================================GENERAL FILENAME OPERATIONS=================================================================

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::ChangeCase::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::


//base case
var update = true;
var original = "My File";
var args = new string[] { "upper" };
var expected = "MY FILE";
var actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//basic string replace
original = "sith lord";
args = new string[] { "lower" };
expected = "sith lord";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//variant of simple replace with trim
original = " sith lord ";
args = new string[] { "proper", "trim" };
expected = "Sith Lord";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//no change... no args
original = "Dark Lord of the Sith    ";
args = new string[0];
expected = "Dark Lord of the Sith    ";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//using same string as before...args now used, but update is false... no change.
update = false;
args = new string[] { "proper", "trim" };
expected = "Dark Lord of the Sith";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//using same string as before...args now used, update allowed.
update = true;
args = new string[] { "proper", "trim" };
expected = "Dark Lord Of The Sith";
//expected = "Dark Lord of the Sith";			//ALTERNATE if it replaces the "of", "a", "the", and similar 
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//using above string, this time allow the uppercase change.
args = new string[] { "upper", "trim" };
expected = "DARK LORD OF THE SITH";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//using previous result... go back to proper.
args = new string[] { "trim", "proper" };
expected = "Dark Lord Of The Sith";
actual = ADM.ChangeCase(original, args, update);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Insert Text::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base case
var original = "Darth Vader";
var args = new string[] { "5", "\"_blah_\"" };
var expected = "Dart_blah_h Vader";
var actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//same thing with trim and bigger string inserted.
original = "Darth Vader";
args = new string[] { "7", "\"Maul and Darth \"", "trim" };
expected = "Darth Maul and Darth Vader";
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//converted case and trimmed after string inserted at position 7
original = "Darth Vader";
args = new string[] { "7", "\"Sidious rules over Darth \"", "lower", "trim" };
expected = "darth sidious rules over darth vader";
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//converted case and trimmed after string inserted at the end of filename
original = "Darth Vader";
args = new string[] { "end", "\" ruled by Darth Sidious\"", "lower", "trim" };
expected = "darth vader ruled by darth sidious";
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test if the filename contains a period... which should be omitted from the filename.  But if the filename has a period in it for some reason, or the new string does, will it kick an error or show it anyway?
original = "vader.jpg";
args = new string[] { "end", "\".png\"", "trim" };
expected = "vader.png";				//but actually this would be "vader.png.jpg" and a JPEG file format. Or, this should trigger an Error for the "." 
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//same as before, but the filename is not an issue to start with. But if the new string does, will it kick an error or show it anyway?
original = "vader";
args = new string[] { "end", "\".png\"", "trim" };
expected = "vader.png";				//Might change the file format, or create a file named "vader.png.jpg" (which should still remain a JPEG file). Or, this could trigger an Error for the "." in the insert text.
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//same as before, but the insert may create an issue if this text happens to be create a file type/extension. 
original = "peg";
args = new string[] { "end", "\"mrr.j\"", "trim" };
expected = "mrr.jpeg";				//Might change the file format depending on what "peg" file was, or create a file named "mrr.jpeg.jpg" (which should still remain a JPEG file format). Or, this could trigger an Error for the "." in the insert text.
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//should be no change, but space added to the end, and then trimmed back out... net gain of nothing.
original = "vader";
args = new string[] { "end", "\" \"", "trim" };
expected = "vader";			
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//almost the same as the last one (with added case change)... only this tiem, space is retained.
original = "VADER";
args = new string[] { "end", "\" \"", "lower" };
expected = "vader ";				//Might trigger error, or create the new name as "vader .jpg" {NOTE the space in the end fullname).  For file operations, maybe trim is part of the default
actual = ADM.Insert(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::LEFT Text::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base case
var original = "Darth Vader";
var args = new string[] { "5", "upper" };
var expected = "DARTH";
var actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "100"};
expected = "mrr_test_file";
//expected = "mrr_test_file.php";		//Alternate to see if it dropped the extension or not.  example file could be "mrr_test_file.php" or "mrr_test_file.php.bk".  If latter, then the ".php" part might still be present.
actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "100", "proper" };
expected = "Mrr_test_file";
//expected = "Mrr_Test_File";			//Alternate depending on if the "_" character is treated like a " " to determining the "proper" case.
actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Makes sure that it can take the first X chars if X > string length.
original = "mrr_test_file.php";
args = new string[] { "100", "upper" };
expected = "MRR_TEST_FILE";
actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Takes first 8 characters, but truncates the space to make it 7 characters.
original = "This Is Only A Test";
args = new string[] { "8", "lower", "trim" };
expected = "this is";
actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//back to normal
original = "mrr_test_file";
args = new string[] { "9", "upper" };
expected = "MRR_TEST_";
actual = ADM.Left(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::RIGHT Text::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base case...
var original = "Darth Vader";
var args = new string[] { "5", "upper" };
var expected = "VADER";
var actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "100"};
expected = "mrr_test_file";
//expected = "mrr_test_file.php";		//Alternate to see if it dropped the extension or not.  example file could be "mrr_test_file.php" or "mrr_test_file.php.bk".  If latter, then the ".php" part might still be present.
actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "100", "proper" };
expected = "Mrr_test_file";
//expected = "Mrr_Test_File";			//Alternate depending on if the "_" character is treated like a " " to determining the "proper" case.
actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Makes sure that it can take the last X chars if X > string length.
original = "mrr_test_file.php";
args = new string[] { "100", "upper" };
expected = "MRR_TEST_FILE";
actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Takes last 8 characters, but truncates the space to make it 12 characters. Trim removes the leading space after.
original = "This Is Only A Test";
args = new string[] { "12", "lower", "trim" };
expected = "only a test";
actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//back to normal
original = "mrr_test_file";
args = new string[] { "9", "upper" };
expected = "TEST_FILE";
actual = ADM.Right(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::MID Text::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base case
var original = "file name";
var args = new string[] { "6", "4" };
var expected = "name";
var actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//same because the string is less is only 4 chars.  Trim removed the space.
original = "file name";
args = new string[] { "5", "10", "trim" };
expected = "name";
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "1", "20", "trim" };
expected = "mrr_test_file";
//expected = "mrr_test_file.php";		//Alternate to see if it dropped the extension or not.  example file could be "mrr_test_file.php" or "mrr_test_file.php.bk".  If latter, then the ".php" part might still be present.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

original = "mrr_test_file.php";
args = new string[] { "5", "9", "proper" };
expected = "Test_file";
//expected = "Test_File";			//Alternate depending on if the "_" character is treated like a " " to determining the "proper" case.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Makes sure that it can take the last 8 chars starting at position 4
original = "mrr_test_file.php";
args = new string[] { "4", "8", "upper" };
expected = "_TEST_FI";
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//Takes 8 characters starting at position 8, but truncates the space to remove the leading space on each side.
original = "This Is Only A Test";
args = new string[] { "8", "8", "trim", "lower" };
expected = "only a";
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//back to normal
original = "mrr_test_file";
args = new string[] { "5", "6", "trim", "upper" };
expected = "TEST_F";
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 index...
original = "mrr_test_file";
args = new string[] { "0", "6", "trim", "upper" };
expected = "MRR_TE";				//Might trigger Error instead since the position should start at one, not zero.  If it corrects it to be the beginning, then it will be the string expected.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 length...
original = "mrr_test_file";
args = new string[] { "6", "0", "trim", "upper" };
expected = "";						//Might trigger Error instead since the length is zero.  If it corrects itself or allows it anyway, then it will be the (empty) string expected.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 index AND length...
original = "mrr_test_file";
args = new string[] { "0", "0", "trim", "upper" };
expected = "";						//Might trigger Error instead since the length is zero AND the index starts at zero instead of one.  If it corrects itself or allows it anyway, then it will be the (empty) string expected.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test X for index that is > than the length.
original = "mrr_test_file";
args = new string[] { "20", "6", "trim", "upper" };
expected = "";						//Might trigger Error instead since the position is larger than the string.  If it corrects itself (a.k.a. "ignores that"), then it will be the (empty) string expected.
actual = ADM.Mid(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Remove Text::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//base case
var original = "filename";
var args = new string[] { "4", "2" };
var expected = "filame";
var actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//same as above, but the lenght exceeds the filename text length.
args = new string[] { "4", "10", "proper" };
expected = "Fil";
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//normal case
original = "Yes My Lord";
args = new string[] { "4", "3", "upper" };
expected = "YES LORD";
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//using above string, and removing space
args = new string[] { "5", "10", "lower","trim" };
expected = "yes";
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 index...
original = "mrr_test_file";
args = new string[] { "0", "6", "trim", "upper" };
expected = "ST_FILE";				//Might trigger Error instead since the position should start at one, not zero.  If it corrects it to be the beginning, then it will be the string expected.
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 length...
original = "mrr_test_file";
args = new string[] { "6", "0", "trim", "upper" };
expected = "MRR_TEST_FILE";			//Might trigger Error instead since the length is zero.  If it corrects itself or allows it anyway, nothing removed... just uppercase and trimming.
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test 0 index AND length...
original = "mrr_test_file";
args = new string[] { "0", "0", "trim", "lower" };
expected = "mrr_test_file";			//Might trigger Error instead since the length is zero.  If it corrects itself or allows it anyway, nothing removed... just lowercase and trimming.
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//test X for index that is > than the length.
original = "mrr_test_file";
args = new string[] { "20", "6", "trim", "upper" };
expected = "MRR_TEST_FILE";			//Might trigger Error instead since the position is larger than the string.  If it corrects itself (a.k.a. "ignores that"), then nothing is removed... just uppercase and trimming.
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");

//testing to retain file exstension if included or not.
original = "mrr_test_file.php";
args = new string[] { "4", "3", "upper" };
expected = "MRRST_FILE";
//expected = "MRRST_FILE.PHP";		//ALTERNATE - if the included fiel extension is dropped... or if the filename has something that includes a ".", this may trigger and error or potentially change the file type.  
actual = ADM.Remove(original, args);
Assert.AreEqual(expected, actual, $"{original}, {string.Join(",", args)}");



//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Unique Alpha::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
string alpha = null;
//var args = new string[] { "a", "\"----\"", "1" };
var update = true;
//var expected = "041816";


//starts with a, using the formatting mask, and increments the letter to b... 
args = new string[] { "a", "\"----\"", "1" };
update = true;
expected = "---b";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//uses the above and increments it again.
update = true;
expected = "---c";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with a, using the formatting mask, and increments the letter 5 times
args = new string[] { "a", "\"----\"", "5" };
update = true;
expected = "---f";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with z, using the formatting mask, and increments the letter 5 times...to uppercase.
args = new string[] { "z", "\"----\"", "5", "upper" };
update = true;
expected = "--AE";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with ae, using the formatting mask, and decrements the letter 5 times...to uppercase.
args = new string[] { "ae", "\"----\"", "5", "upper", "desc" };
update = true;
expected = "---Z";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with a, using the formatting mask, and decrements the letter 5 times...to uppercase.  May give an error... too many steps back.  Might also give altrernate or reset to  all "----".
args = new string[] { "a", "\"----\"", "5", "upper", "desc" };
update = true;
expected = "----";
//expected = "ZZZX";			//if "----" does not count as zero
//expected = "ZZZW";			//if "----" does count as zero
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with a, using NO formatting mask, and increments the letter to b... lowercase
args = new string[] { "AA", "\"\"", "6", "lower"};
update = true;
expected = "ag";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//use above to get the next 6 letter increment... no mask.
update = true;
expected = "am";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);

//starts with a, using NO formatting mask, and decrements the letter to b... uppercase
args = new string[] { "am", "\"\"", "6", "upper", "desc"};
update = true;
expected = "AG";
var actual = ADM.UniqueAlpha(ref alpha, args, ref update);
Assert.AreEqual(expected, actual);


//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::Unique Number:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
int number = int.MinValue;
//var args = new string[] { "1", "\"----\"", "4" };
//var update = true;
//var expected = "---5";

//increment four past 1 with mask
args = new string[] { "1", "\"----\"", "4" };
update = true;
expected = "---5";
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//using the above, add four more to with mask
update = true;
expected = "---9";
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//increment ten past 10 with mask
args = new string[] { "10", "\"000000\"", "10" };
update = true;
expected = "000020";
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//using the above, add ten more to with mask
update = true;
expected = "000030";
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//decrement 10 from 1000 and use mask
args = new string[] { "1000", "\"000000\"", "10", "desc" };
update = true;
expected = "000990";
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//decrement 10 from 1000 and use mask... mask is more "creative".  May cause ERROR or use the mask funny.
args = new string[] { "1000", "\"0#0#0#0#\"", "10", "desc" };
update = true;
expected = "0#0#0990";					//assumes the mask is 8 characters...
//expected = "0#990";					//Alternate if the mask is using "0#" for each digit... (4 characters instead of 8 in final string)
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);

//increment 10 from 1000 and use mask... mask is more "creative".  May cause ERROR or use the mask funny.
args = new string[] { "1000", "\"0#0#0#0#\"", "10", "desc" };
update = true;
expected = "0#0#1010";					//assumes the mask is 8 characters...
//expected = "1010";					//Alternate if the mask is using "0#" for each digit... (4 characters instead of 8 in final string)
actual = ADM.UniqueNum(ref number, args, ref update);
Assert.AreEqual(expected, actual);


//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::DateTime Format and Retrieval::::::::::::::::::::::::::::::::::::::::::::::::::::::

//:::::Unique Dates:::::://
var dt = DateTime.MinValue;

//increment date from start date
update = true;
args = new string[] { "4/14/2019", "\"MMddyy\"", "d", "1" };
expected = "041519";
//var actual = ADM.UniqueDate(ref dt, args, ref update);
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//increment date again...use last result
update = true;
expected = "041619";
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//decrement 2 days from start date... but only show the day part of the date ("01" format)
dt = DateTime.MinValue;
update = true;
args = new string[] { "4/14/2019", "\"dd\"", "d", "2", "dec" };
expected = "12";
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//go one month back and show month name... but since update=false, no month increment happens on the month.  Shown in uppercase.
dt = DateTime.MinValue;
update = false;
args = new string[] { "4/14/2019", "\"MMMM\"", "m", "1", "upper" };
expected = "APRIL";
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//take last date and only the day for output like before, and use the next month... since update=true.
update = true;
expected = "MAY";
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//use the new start date, take 3rd month from it and and display it in lowercase letters.  Update is true, so increment happens. 
dt = DateTime.MinValue;
update = true;
args = new string[] { "4/14/2019", "\"MMMM\"", "m", "3", "lower" };
expected = "july";
actual = ADM.UniqueDate(ref dt, args, ref update);
Assert.AreEqual(expected, actual);

//:::::::::::Date fromatting test:::::::::

DateTime testDate = DateTime.Parse("4/14/2019 2:30:05 PM");
//DateTime testDate = DateTime.Parse("4/14/2019 02:30:05 PM");
string[] input = { "MM-dd-yyyy" };
//string expected = "02-21-2009";
//string actual = ADM.Datetime(testDate, input);
expected = "04-14-2019";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMMM d, yyyy", "upper" };
expected = "APRIL 14, 2019";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMMM d, yyyy", "proper" };
expected = "April 14, 2019";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMMM d, YYYY", "upper" };
expected = "APRIL 14, YYYY";							//Note that the Year "y" and "Y" are not a match, so the cpatial "Y" letters stay as is.  Only the lowercase "y" letters should be swapped out for the year format. If it works, check m/M and h/H just in case. 
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMM", "lower" };
expected = "apr";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMM", "upper" };
expected = "APR";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "MMM", "proper" };
expected = "Apr";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "t", "lower" };
expected = "p";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "tt", "upper" };
expected = "PM";
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "The time is hh:mm tt"};
expected = "T2e pi0e i0 02:30 PM";							//Should replace some of the text in "The time is " part with parts of the date/time
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "The time is hh:mm tt", "lower" };
expected = "t2e pi0e i0 02:30 pm";							//Should replace some of the text in "The time is " part with parts of the date/time.  Note that the captial "T" did not match a formatting string, so it stayed, but was made lowercase by the setting. 
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "T\he \ti\me i\s hh:mm tt"};
expected = "The time is 02:30 PM";							//Escaped format characters in "The time is " string, so it should remain as the user probably wanted the string to show wiht the text before the time.
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "hhh mm ss" };
expected = "202 30 05";									//Format string test... with multiple string matches that could be matched more than one way.  Testing priority of matches in formatting string.
//expected = "022 30 05";								//ALTERNATE: Format string test... with multiple string matches that could be matched more than one way.  Testing priority of matches in formatting string.
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "HHH mm ss" };
expected = "1414 30 05";									//Format string test... with multiple string matches that could be matched more than one way.  Testing priority of matches in formatting string.
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);

input = new string[] { "HH mm sss" };
expected = "14 30 505";									//Format string test... with multiple string matches that could be matched more than one way.  Testing priority of matches in formatting string.
//expected = "14 30 055";								//ALTERNATE: Format string test... with multiple string matches that could be matched more than one way.  Testing priority of matches in formatting string.
actual = ADM.Datetime(testDate, input);
Assert.AreEqual(expected, actual);
?>