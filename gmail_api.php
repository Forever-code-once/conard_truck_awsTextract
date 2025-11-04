<?
require 'vendor/autoload.php';

session_start();

//$service_account = 'availabletrucks@trucksavailable-160822.iam.gserviceaccount.com';
$app_name = 'availabletrucks';


$userid = 'truckavailable@conardlogistics.com';
$http = new GuzzleHttp\Client(['verify' => false]);

$client = new Google_Client();
$client->setHttpClient($http);

$client->setApplicationName($app_name);
//$client->setAuthConfig('service_account.json');
//$client->setAuthConfig('client_secret_101001145148148952019.json');
$client->setAuthConfig('trucksavailable-24f74fbfbb5d.json');


$client->setScopes(array(
	'https://mail.google.com/',
));
$client->setSubject($userid);

$gmail = new Google_Service_Gmail($client);

// Get a list of the messages
$messages = array();
$opt_param = array();

$messages_response = $gmail->users_messages->listUsersMessages($userid, $opt_param);

echo '<pre>';
var_dump($messages_response);
echo '</pre>';

?>