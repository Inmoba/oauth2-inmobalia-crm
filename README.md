# Inmobalia CRM Provider for OAuth 2.0 Client

This package provides Inmobalia CRM OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/inmoba/oauth2-inmobalia-crm).

## Installation

To install, use composer:

```
composer require inmoba/oauth2-inmobalia-crm
```

## Usage

Usage is the same as The League's OAuth client, using `\Inmobalia\OAuth2\Client\Provider\InmobaliaCrm` as the provider.

### Authorization Code Flow

```php
$provider = new Inmobalia\OAuth2\Client\Provider\InmobaliaCrm([
    'clientId'          => '{inmobalia-client-id}',
    'clientSecret'      => '{inmobalia-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getNickname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Inmobalia authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'scope' => ['properties:read', 'users:read', 'web-leads:write'] // array or string;
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```

At the time of authoring this documentation, the [following scopes and endpoints are available](https://api-crm.inmobalia.com/docs/swagger-ui).

## License

The MIT License (MIT). Please see [License File](https://github.com/inmoba/oauth2-inmobalia-crm/blob/master/LICENSE) for more information.
