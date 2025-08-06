<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config; // Make sure this is imported if you use it elsewhere
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App; // To check environment

class SportsController extends Controller
{
    public function sports() {

        if (!Auth::check()) {
            return Redirect::to('/home');
        }


        // --- Option 1: Disable sports page specifically for 'development' or 'local' environment ---
        // You can check your APP_ENV value in your .env file for dev.tradabets.com
        // Common values are 'local', 'development', 'staging', 'production'.
        // Let's assume your dev environment is 'development' or 'local'.
        // if (App::environment(['local', 'development'])) { // Adjust environment names as needed
        //     // You can either redirect to another page:
        //     // return Redirect::to('/home')->with('info_message', 'The sports page is currently unavailable in this environment.');

        //     // Or, you can show a specific view indicating it's unavailable:
        //     // First, create a simple Blade view, e.g., resources/views/menu-pages/sports-unavailable.blade.php
        //     // with a message like "Sports functionality is currently disabled on this server."
        //     // return view('menu-pages.sports-unavailable');

        //     // Or, for a very simple approach, just return a message directly (less ideal for user experience but quick):
        //     return response('The sports page is currently unavailable on this development server.', 404); // Or 503 Service Unavailable
        // }

        // --- Original logic (will run if not in the specified 'local' or 'development' environment) ---
        // If you want to disable it on dev but keep it for other environments like production,
        // the code below will only be reached if App::environment() check above is false.

        // If you want to disable it entirely on this dev deployment regardless of APP_ENV for now,
        // you can just put the return statement directly:
        // return response('The sports page is currently unavailable.', 503); // Uncomment this line for a blanket disable

        // Try...catch block for robustness if you ever re-enable it partially
        try {
            $token = $this->getToken();
            if (!$token) {
                // Handle case where token couldn't be fetched for reasons other than a hard 403 if logic changes
                \Illuminate\Support\Facades\Log::error('Failed to retrieve token for sports page.');
                // Using response() helper for more control over status code
                return response('The sports service is temporarily unavailable (Error Code: ST501). Please try again later.', 503);
            }

            $iframe_url = $this->getStartSession($token);
            if (!$iframe_url) {
                 \Illuminate\Support\Facades\Log::error('Failed to retrieve iframe_url for sports page.');
                return response('The sports service is temporarily unavailable (Error Code: ST502). Please try again later.', 503);
            }

            $data = ['url' => $iframe_url];
            return view('menu-pages.sports', $data);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // This will catch the 403 Forbidden error if getToken() throws it
            \Illuminate\Support\Facades\Log::error('Sports API ClientException: ' . $e->getMessage());
            // You can customize this message for the user
            return response('The sports service is currently experiencing issues (Error Code: ST403). Please try again later.', 503);
        } catch (\Exception $e) {
            // Catch any other exceptions
            \Illuminate\Support\Facades\Log::error('General error on sports page: ' . $e->getMessage());
            return response('An unexpected error occurred while loading the sports page (Error Code: ST500). Please try again later.', 500);
        }
    }

    // Your getToken() and getStartSession() methods remain here.
    // By modifying the sports() method as shown above, getToken() might not even be called
    // if you choose to return/redirect early for the dev environment.

    public function getToken()
    {
        try {
            $guzzle = new \GuzzleHttp\Client();
            $data = [
                'username' => env('SPORTSBOOK_USERNAME', 'tradebets'),
                'password' => env('SPORTSBOOK_PASSWORD', 'ItCad_YtrtjHul31_Hh')
            ];

            $raw_response = $guzzle->post(Config::get('constants.SPORTBOOK_TOKEN_URL'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json', // Add this if API expects it
                ],
                'json' => $data, // Using 'json' instead of 'body' for automatic JSON encoding
                'http_errors' => false // To get the response even on 4xx/5xx
            ]);

            $statusCode = $raw_response->getStatusCode();
            $response = json_decode($raw_response->getBody()->getContents());

            if ($statusCode !== 200 || !isset($response->token)) {
                \Log::error('Sportsbook API Error', [
                    'status' => $statusCode,
                    'response' => $response,
                    'url' => Config::get('constants.SPORTBOOK_TOKEN_URL')
                ]);
                return null;
            }

            return $response->token;
        } catch (\Exception $e) {
            \Log::error('Sportsbook Token Request Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getStartSession($token)
    {
        // ... (your existing getStartSession code) ...
        if (!$token) { // Add a check for invalid token
            \Illuminate\Support\Facades\Log::error('getStartSession called with an invalid token.');
            return null; // Or throw an exception
        }
        $guzzle = new \GuzzleHttp\Client();
        // ... rest of the method
        // Make sure to handle cases where $raw_response or $response->url might not be set
        // For example:
        $raw_response = $guzzle->post(Config::get('constants.SPORTBOOK_SESSION_URL'), [
            'headers' => ['Content-Type' => 'application/json','Authorization' => 'Bearer ' . $token],
            'body' => json_encode([
                 "user_id" => "".Auth::user()->id."",
                 "username" => "".Auth::user()->first_name.' '.Auth::user()->last_name."",
                 "currency" => "NGN",
                 "lang" => "en"
            ]),
        ]);
        $response = $raw_response->getBody()->getContents();
        $response = json_decode($response);

        if (!isset($response->url)) {
            \Illuminate\Support\Facades\Log::error('URL not found in getStartSession API response from ' . Config::get('constants.SPORTBOOK_SESSION_URL'));
            return null;
        }
        return $response->url;
    }
}