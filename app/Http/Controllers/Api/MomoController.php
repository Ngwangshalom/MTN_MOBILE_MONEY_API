<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WGOrganisationMomoService;
use Illuminate\Http\Request;

class MomoController extends Controller
{
    protected $momoService;

    public function __construct(WGOrganisationMomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    public function initiatePayment(Request $request)
{
    try {
        // Your payment logic here
        $amount = $request->input('amount');
        $phoneNumber = $request->input('phone_number');
        $externalId = $request->input('external_id');
        $payerMessage = $request->input('payer_message');
        $payeeNote = $request->input('payee_note');

        // Generate API user UUID
        $apiUserId = $this->momoService->generateUuid();

        // Create a new user
        $userCreationResponse = $this->momoService->createUser($apiUserId);

        if ($userCreationResponse) {
            // User created successfully, get the user ID
            $apiUserId = $userCreationResponse['uuid'];
            config(['app.momo_api_user_id' => $apiUserId]);
        } else {
            return response()->json(['message' => 'Failed to create user'], 500);
        }

        // Generate API key for the user
        $apiKeyResponse = $this->momoService->generateApiKey($apiUserId);
        if (!$apiKeyResponse) {
            return response()->json(['message' => 'Failed to generate API key'], 500);
        }

        // Generate token for the payment
        $tokenResponse = $this->momoService->generateToken();
        if (!$tokenResponse) {
            return response()->json(['message' => 'Failed to generate token'], 500);
        }

        // Initiate payment
        $paymentResponse = $this->momoService->initiatePayment($amount, $phoneNumber, $externalId, $payerMessage, $payeeNote, $apiUserId, $tokenResponse['access_token']);

        return response()->json(['message' => 'Payment initiated successfully', 'payment_response' => $paymentResponse]);
    } catch (\Exception $exception) {
        // Handle the exception as needed
        return response()->json(['message' => 'Error initiating payment', 'error' => $exception->getMessage()], 500);
    }
}


    //was for testing purposes no longer needed, as all modules work fine!!
    public function testUuid()
    {
        $uuid = $this->momoService->generateUuid();
        return response()->json(['uuid' => $uuid]);
    }

    public function testCreateUser()
    {
        $user = $this->momoService->createUser();
        return response()->json(['user' => $user]);
    }

    public function testRequestUser($userId)
    {
        $user = $this->momoService->requestUser($userId);
        return response()->json(['user' => $user]);
    }

    public function testGenerateApiKey($userId)
    {
        $apiKey = $this->momoService->generateApiKey($userId);
        return response()->json(['api_key' => $apiKey]);
    }

   public function generateToken()
    {
        // Get the $apiUserId from the request or any other source
        // $apiUserId = $request->input('apiUserId');

        // Call the generateToken method with the correct argument
        $token = $this->momoService->generateToken();

        // Use the token as needed in your logic

        return response()->json(['token' => $token]);
    }

    public function testInitiatePayment(Request $request)
    {
        // Extract necessary parameters from the request
        $amount = $request->input('amount');
        $phoneNumber = $request->input('phone_number');
        // ... (extract other parameters as needed)

        // Call the initiatePayment method
        $result = $this->momoService->initiatePayment($amount, $phoneNumber, /* other parameters */);

        return response()->json(['result' => $result]);
    }
}
