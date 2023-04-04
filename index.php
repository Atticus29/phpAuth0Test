<?php 
// Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// Instantiate the Auth0 class with our configuration:
$auth0 = new \Auth0\SDK\Auth0([
	'domain' => $_ENV['AUTH0_DOMAIN'],
	'clientId' => $_ENV['AUTH0_CLIENT_ID'],
	'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
	'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
]);

// Import our router library:
use Steampixel\Route;

//Define route constants:
define('ROUTE_URL_INDEX', rtrim($_ENV['AUTH0_BASE_URL'], '/'));
define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/login');
define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/callback');
define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/logout');

Route::add('/', function() use ($auth0) {
	$session = $auth0->getCredentials();

	// Not logged in
	if ($session === null) {
		echo '<p>Please <a href="/login">log in</a>.</p>';
		return;
	}

	// Logged in
	echo '<pre>';
	print_r($session->user);
	echo '</pre>';

	echo '<p>You can now <a href="/logout">log out</a>.</p>';

	// $emailAddress = isset($session->user['email']) ? $session->user['email'] : 'Unknown';
	// $backupName = isset($session->user['nickname']) ? $session->user['nickname'] : $emailAddress;
	// $name = isset($session-> user['name']) ? $session->user['name'] : $backupName; // @TODO use this somehow
});

	Route::add('/login', function() use ($auth0) {
		// Reset the session to avoid "invalid state" errors
		$auth0->clear();

		// set up local application session and redirect the user to Auth0 Universal Login Page
		header("Location: " . $auth0->login(ROUTE_URL_CALLBACK));
		exit;
	});

	Route::add('/callback', function() use ($auth0) {
		// complete the authentication flow
		$auth0->exchange(ROUTE_URL_CALLBACK);

		//redirect back to the index route
		header("Location: " . ROUTE_URL_INDEX);
		exit;
	});

	Route::add('/logout', function() use ($auth0) {
		// clear the user's local session and redirect them to the logout endpoint
		header("Location: " . $auth0->logout(ROUTE_URL_INDEX));
		exit;
	});

	Route::run('/');
?>