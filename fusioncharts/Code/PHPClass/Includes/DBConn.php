<?php
// In this page, we open the connection to the Database
// In this page, we open the connection to the Database
// Our MySQL database (blueprintdb) for the Blueprint Application
// Function to connect to the DB
function connectToDB() {
    // These four parameters must be changed dependent on your MySQL settings
    $hostdb = 'HostURL';   // MySQl host
    $userdb = 'username';    // MySQL username
    $passdb = 'password';    // MySQL password
    $namedb = 'factorydb'; // MySQL database name

    //$link = mysql_connect ("localhost:3306", "username", "password");
	//$link = mysql_connect ($hostdb, $userdb, $passdb);
	$link = mysqli_connect ();

    if (!$link) {
        // we should have connected, but if any of the above parameters
        // are incorrect or we can't access the DB for some reason,
        // then we will stop execution here
        die('Could not connect: ' . mysqli_error());
    }

    $db_selected = mysqli_select_db($namedb);
    if (!$db_selected) {
        die ('Can\'t use database : ' . mysqli_error());
    }
    return $link;
}
?>