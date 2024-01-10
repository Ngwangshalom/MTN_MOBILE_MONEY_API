<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WGOrganisationMomoService
{
    public function initiatePayment($amount, $phoneNumber, $externalId, $payerMessage, $payeeNote, $apiUserId, $tokenResponse)
    {
        try {
            // // Step 1: Generate UUID
            // $apiUserId = $this->generateUuid();

            // // Step 2: Create User
            // $userResult = $this->createUser($apiUserId);
            // if (!$userResult) {
            //     // Handle the case where creating the user fails
            //     return null;
            // }

            // // Step 3: Get User Information
            // $userId = $userResult['uuid'];
            // $userInformation = $this->requestUser($userId);
            // if (!$userInformation) {
            //     // Handle the case where getting user information fails
            //     return null;
            // }

            // // Step 4: Generate API Key
            // $apiKeyResult = $this->generateApiKey($userId);
            // if (!$apiKeyResult) {
            //     // Handle the case where generating API key fails
            //     return null;
            // }

            // // Step 5: Generate Token
            // $tokenResult = $this->generateToken();
            // if (!$tokenResult) {
            //     // Handle the case where generating token fails
            //     return null;
            // }

            // Step 6: Initiate Payment
            $paymentResponse = $this->makePaymentRequest($apiUserId, $amount, $phoneNumber, $externalId, $payerMessage, $payeeNote, $tokenResponse);
            
            // Check if the request was successful and return the response
            return $paymentResponse->successful() ? $paymentResponse->json() : null;
        } catch (\Exception $exception) {
            // Handle the exception as needed
            return null;
        }
    }


    // ... other methods ...

    private function makePaymentRequest($apiUserId, $amount, $phoneNumber, $externalId, $payerMessage, $payeeNote, $tokenResult)
    {
        $paymentUrl = config('app.momo_collection_request_to_pay_url');

        // Make a request to initiate payment
        return Http::post('https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay', [
            'headers' => [
                'X-Reference-Id' => $apiUserId,
                'X-Target-Environment' => config('app.momo_collection_target_environment'),
                'Ocp-Apim-Subscription-Key' => config('app.ocp_apim_subscription_key'),
                'Authorization' => 'Bearer ' . $tokenResult,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'amount' => $amount,
                'currency' => 'EUR',
                'externalId' => $externalId,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $phoneNumber,
                ],
                'payerMessage' => $payerMessage,
                'payeeNote' => $payeeNote,
            ],
        ]);
    }


    public function createUser($apiUserId)
    {
        try {
            // Get the Ocp-Apim-Subscription-Key from the configuration
            $ocpSubscriptionKey = config('app.ocp_apim_subscription_key');
    
            // Prepare the request body
            $requestBody = [
                'providerCallbackHost' => 'https://studio.bayamquik.com',
            ];
    
            // Make a request to create a user
            $response = Http::withHeaders([
                'X-Reference-Id' => $apiUserId,
                'Ocp-Apim-Subscription-Key' => $ocpSubscriptionKey,
                'Content-Type' => 'application/json',
            ])->post('https://sandbox.momodeveloper.mtn.com/v1_0/apiuser', $requestBody);
    
            // Log the complete response
            Log::info('Create User API Response: ' . $response->body());
    
            if ($response->successful()) {
                // Return both UUID and API response
                return ['uuid' => $apiUserId, 'response' => $response->json()];
            } elseif ($response->json('code') === 'RESOURCE_ALREADY_EXIST') {
                // Handle the case where the user already exists
                Log::info('User with reference id already exists.');
    
                // You can choose to return some information or null based on your logic
                return ['uuid'=> $apiUserId, 'message' => 'User already registered'];
            } else {
                // Return null if there's an error other than duplicate reference id
                return null;
            }
        } catch (\Exception $exception) {
            // Log the exception
            Log::error('Create User Exception: ' . $exception->getMessage());
    
            // Return null in case of an exception
            return null;
        }
    }
    
   public function generateUuid()
    {
        try {
            // Generate a UUID using the custom function
            $uuid = $this->guidv4();
    
            // Return the generated UUID
            return $uuid;
        } catch (\Exception $exception) {
            // Handle the exception as needed
            return 'default-uuid';
        }
    }
    
    // Add the custom guidv4 function to your class
    private function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
  


    public function requestUser($userId)
    {
        try {
            // Make a request to get user information
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => config('app.ocp_apim_subscription_key'),
            ])->get("https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$userId}");
    
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $exception) {
            // Return null in case of an exception
            return null;
        }
    }
    

   public function generateApiKey($userId)
    {
        try {
           
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => config('app.ocp_apim_subscription_key'),
            ])->post("https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$userId}/apikey");
    
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $exception) {
            // Return null in case of an exception
            return null;
        }
    }
    

    public function generateToken()
    {
        try {
            $apiKey = "4436056d1ecb44499cc8c570df7809ad"; //api key gotton from generating it
            $apiUserId = "a81a3a82-1a63-4bdb-a34e-4ad6ad5e0064";
    
            // Make a request to generate token
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("$apiUserId:$apiKey"),
                'Ocp-Apim-Subscription-Key' => config('app.ocp_apim_subscription_key'),
            ])->post(config('app.momo_collection_token_url'));
    
            return $response ? $response->json() : null;
        } catch (\Exception $exception) {
            // Handle the exception as needed
            return null;
        }
    }
    
}
