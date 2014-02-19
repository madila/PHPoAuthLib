<?php

/**
 * Example of retrieving an authentication token of the Freshbooks service
 *
 * PHP version 5.4
 *
 * @author     Ruben Madila <contact@rubenmadila.com>
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Freshbooks;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['freshbooks']['key'],
    $servicesCredentials['freshbooks']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Freshbooks service using the credentials, http client and storage mechanism for the token
/** @var $freshbooksService Freshbooks */
$freshbooksService = $serviceFactory->createService('freshbooks', $credentials, $storage);

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken('Freshbooks');

    // This was a callback request from freshbooks, get the token
    $freshbooksService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );
	
	// This is the request to Freshbooks, including the xml body. 
	// $freshbooks->request( $path = null, $method = 'POST', $body =  'The xml body as per Freshbooks documentation' );
	$result = $freshbooks->request( null, 'POST', '<?xml version="1.0" encoding="utf-8" ?><request method="staff.current"></request>' );

	$result= new \SimpleXmlElement($result);

    // Show some of the resultant data
    echo $result->staff->username . ' has authorised this website to see their Freshbooks account information.';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {

	$token = $freshbooks->requestRequestToken();

	$url = $freshbooks->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
	header('Location: ' . $url);

} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Freshbooks!</a>";
}