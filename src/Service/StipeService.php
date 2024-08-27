<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    private $stripeSecretKey;

    // Constructor to initialize the service with Stripe secret key
    public function __construct(string $stripeSecretKey)
    {
        $this->stripeSecretKey = $stripeSecretKey;
        
        // Set the Stripe API key to authenticate requests to Stripe's API
        Stripe::setApiKey($this->stripeSecretKey);
    }

    // Method to create a Stripe Checkout session
    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl): Session
    {
        // Create and return a new Stripe Checkout session
        return Session::create([
            'payment_method_types' => ['card'],  
            'line_items' => $lineItems,        
            'mode' => 'payment',                 
            'success_url' => $successUrl,       
            'cancel_url' => $cancelUrl,        
        ]);
    }
}
