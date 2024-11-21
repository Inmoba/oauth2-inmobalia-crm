<?php

require_once "vendor/autoload.php";

use GuzzleHttp\Exception\RequestException;
use Inmobalia\OAuth2\Client\Provider\Exception\InmobaliaCrmIdentityProviderException;
use Inmobalia\OAuth2\Client\Provider\InmobaliaCrm;

$provider = new InmobaliaCrm([
    'clientId' => getenv('INMOBALIA_CLIENT_ID'),    // The client ID assigned to you by the provider
    'clientSecret' => getenv('INMOBALIA_CLIENT_SECRET'),    // The client password assigned to you by the provider
    'redirectUri' => getenv('INMOBALIA_REDIRECT_URI'),
]);

session_start();

/**
 * @var \League\OAuth2\Client\Token\AccessTokenInterface | null $apiToken
 */
$apiToken = $_SESSION["token"] ?? null;

if (empty($apiToken)) {
    if (!isset($_GET['code'])) {
        // If we don't have an authorization code then get one
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => getenv('INMOBALIA_SCOPES'),
        ]);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
        // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
        session_destroy();
        exit('Invalid state');
    } else {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        $_SESSION["token"] = $token;

        header('Location: /');
        exit;
    }
} else {
    if ($apiToken->hasExpired()) {
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $apiToken->getRefreshToken()
        ]);
        // Purge old access token and store new access token to your data store.
        $_SESSION["token"] = $token;
        $apiToken = $token;
        echo "<h2>The token has been updated</h2>";
    }

    // We have an access token, which we may use in authenticated
    // requests against the service provider's API.
    echo '<pre>';
    echo 'Access Token: ' . $apiToken->getToken() . "<br>";
    echo 'Refresh Token: ' . $apiToken->getRefreshToken() . "<br>";
    echo 'Expired at: ' . date('r', $apiToken->getExpires()) . "<br>";
    echo 'now: ' . date('r', time()) . "<br>";
    echo 'Already expired? ' . ($apiToken->hasExpired() ? 'expired' : 'not expired') . "<br>";
    echo '</pre>';

    // Optional: Now you have a token you can look up a users profile data
    try {
        $user = $provider->getResourceOwner($apiToken);
        echo "<h1>Hello " . $user->getUsername() . "!</h1>";
    } catch (InmobaliaCrmIdentityProviderException $e) {
        echo "<h2>Error request</h2>";
        echo "<pre>" . \GuzzleHttp\Psr7\Message::toString($e->getResponse()) . "</pre>";
    }


    $request = $provider->getAuthenticatedRequest('GET', 'https://api-crm.inmobalia.com/v1/properties', $apiToken);
    echo "<h2>Request</h2>";
    echo "<pre>" . GuzzleHttp\Psr7\Message::toString($request) . "</pre>";

    try {
        $response = $provider->getHttpClient()->send($request);

        $statusCode = $response->getStatusCode();
        $responseStr = GuzzleHttp\Psr7\Message::toString($response);
        GuzzleHttp\Psr7\Message::rewindBody($response);
        if ($statusCode === 200) {
            echo "<h2>Response</h2>";
            echo "<pre>" . $responseStr . "</pre>";
            echo "<h2>Properties</h2>";
            echo "<pre>" . json_encode(json_decode($response->getBody()), JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<h2>Response</h2>";
            echo "<pre>" . $responseStr . "</pre>";
        }
    } catch (RequestException $e) {
        echo "<h2>Error request</h2>";
        echo "<pre>" . GuzzleHttp\Psr7\Message::toString($e->getRequest()) . "</pre>";
        echo "<pre>" . GuzzleHttp\Psr7\Message::toString($e->getResponse()) . "</pre>";
    }
}
