<?php

/*
 * This library is free software, and it is part of the Active Collab SDK project. Check LICENSE for details.
 *
 * (c) A51 doo <info@activecollab.com>
 */

/*require_once __DIR__ . '/vendor/autoload.php';*/

$authenticator = new \ActiveCollab\SDK\Authenticator\Cloud('Utthunga', 'My Awesome Application', 'praveen.c@utthunga.com', 'praveen@6670');

// Show all Active Collab 5 and up account that this user has access to
//print_r($authenticator->getAccounts());

// Show user details (first name, last name and avatar URL)
//print_r($authenticator->getUser());

// Issue a token for account #123456789
$token = $authenticator->issueToken(147461);

if ($token instanceof \ActiveCollab\SDK\TokenInterface) {
   // print $token->getUrl() . "\n";
    //print $token->getToken() . "\n";
} else {
    print "Invalid response\n";
    die();
}

// Create a client instance
$client = new \ActiveCollab\SDK\Client($token);

// Make a request
$response = $client->get('projects')->getJson();
/*print_r($response[1]);
print_r($response[2]['name']);*/