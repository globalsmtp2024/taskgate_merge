<?php

namespace App\Services;

use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Calendar;
use Google_Service_Gmail;
use Illuminate\Support\Facades\Redirect;

class GoogleOAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes;

    public function __construct($clientId, $clientSecret, $redirectUri, $scopes = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes ?: [
            \Google_Service_Oauth2::USERINFO_EMAIL,  // Allows access to the user's email
            \Google_Service_Oauth2::USERINFO_PROFILE,  // Access to user's profile info
            \Google_Service_Calendar::CALENDAR,  // Access to Google Calendar
            'https://www.googleapis.com/auth/gmail.send',  // Required for sending emails
            'https://www.googleapis.com/auth/gmail.readonly',  // Required for reading Gmail
        ];
    }

    public function createAuthUrl()
    {
        $client = $this->initializeGoogleClient();
        return $client->createAuthUrl();
    }

    public function authenticate($code)
    {
        // Initialize the Google client
        $client = $this->initializeGoogleClient();

        try {
            // Fetch access token using the authorization code
            $token = $client->fetchAccessTokenWithAuthCode($code);

            // Check for errors in the response
            if (isset($token['error'])) {
                // Log the error for debugging
                \Log::error('Google OAuth Error: ' . json_encode($token));
                throw new \Exception('Error fetching token: ' . $token['error']);
            }

            // Set the access token
            $client->setAccessToken($token);

            // Return the authenticated client
            return $client;
        } catch (\Exception $e) {
            // Log the exception error message
            \Log::error('Error in OAuth Authentication: ' . $e->getMessage());
            throw new \Exception('Error fetching token: ' . $e->getMessage());
        }
    }

    public function getUserInfo($client)
    {
        $oauthService = new Google_Service_Oauth2($client);
        return $oauthService->userinfo->get();
    }

    public function getAccessToken($client)
    {
        return $client->getAccessToken();
    }

    protected function initializeGoogleClient()
    {
        $client = new Google_Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->redirectUri);
        $client->setScopes($this->scopes);
        $client->setAccessType('offline');  // Get offline access for long-lived tokens
        $client->setApprovalPrompt('force');  // Force approval to get a new refresh token
        return $client;
    }
}
