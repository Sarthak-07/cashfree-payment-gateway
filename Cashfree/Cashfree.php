<?php

namespace App\Extensions\Gateways\Cashfree;

use App\Classes\Extensions\Gateway;
use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;

class Cashfree extends Gateway
{
    /**
     * Get the extension metadata
     * 
     * @return array
     */
    public function getMetadata()
    {
        return [
            'display_name' => 'Cashfree',
            'version' => '1.0.0',
            'author' => 'Sarthak',
            'website' => 'https://stellarhost.tech',
        ];
    }

    /**
     * Get all the configuration for the extension
     * 
     * @return array
     */
    public function getConfig()
    {
        return [
            [
                'name' => 'order_id_prefix',
                'friendlyName' => 'Order ID Prefix',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'cashfree_api_key',
                'friendlyName' => 'Cashfree APP ID',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'cashfree_api_secret',
                'friendlyName' => 'Cashfree Secret Key',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'test_mode',
                'friendlyName' => 'Test Mode',
                'type' => 'boolean',
                'required' => false,
            ],
            [
                'name' => 'test_api_key',
                'friendlyName' => 'Test APP ID',
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'test_api_secret',
                'friendlyName' => 'Test Secret Key',
                'type' => 'text',
                'required' => false,
            ],
        ];
    }
    
    /**
     * 
     * @param int $total
     * @param array $products
     * @param int $invoiceId
     * @return string
     */
    public function pay($total, $products, $invoiceId)
    {
        $apiKey = ExtensionHelper::getConfig('Cashfree', 'test_mode') ? ExtensionHelper::getConfig('Cashfree', 'test_api_key') : ExtensionHelper::getConfig('Cashfree', 'cashfree_api_key');
        $secretKey = ExtensionHelper::getConfig('Cashfree', 'test_mode') ? ExtensionHelper::getConfig('Cashfree', 'test_api_secret') : ExtensionHelper::getConfig('Cashfree', 'cashfree_api_secret');        
        $orderId = ExtensionHelper::getConfig('Cashfree', 'order_id_prefix') . $invoiceId;
        
        if (ExtensionHelper::getConfig('Cashfree', 'test_mode')) {
            $getUrl = "https://sandbox.cashfree.com/pg/orders/{$orderId}";
        } else {
            $getUrl = "https://api.cashfree.com/pg/orders/{$orderId}";
        }
    
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $getUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-version: 2023-08-01',
                'x-client-id: ' . $apiKey,
                'x-client-secret: ' . $secretKey,
            ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            return 'cURL Error #:' . $err;
        } else {
            $orderData = json_decode($response, true);
    
            if (isset($orderData['order_status'])) {
                if ($orderData['order_status'] === 'ACTIVE') {
                    $payment_session_id = $orderData['payment_session_id'];
                    return route('cashfree.payment', ['invoiceId' => $invoiceId, 'payment_session_id' => $payment_session_id]);
                } elseif ($orderData['order_status'] === 'PAID') {
                    ExtensionHelper::paymentDone($invoiceId);
                    return route('clients.invoice.show', $invoiceId);
                }
            }
        }
        
        $order_amount = $total;
        $orderId = ExtensionHelper::getConfig('Cashfree', 'order_id_prefix') . $invoiceId;
        $order_note = $products[0]->name;
        $customerId = 'customer_' . $invoiceId;
        $customer_name = auth()->user()->name;
        $customer_email = auth()->user()->email;
        $customer_phone = '1234567890';
        
        if (ExtensionHelper::getConfig('Cashfree', 'test_mode')) {
            $url = "https://sandbox.cashfree.com/pg/orders";
        } else {
            $url = "https://api.cashfree.com/pg/orders";
        }

        $headers = [
            'Content-Type: application/json',
            'x-api-version: 2023-08-01',
            'x-client-id: ' . $apiKey,
            'x-client-secret: ' . $secretKey,
        ];

        $data = [
            "order_id" =>  $orderId,
            "order_amount" => $order_amount,
            "order_currency" => "INR",
            "order_note" => $order_note,
            "customer_details" => [
                "customer_id" => $customerId,
                "customer_name" => $customer_name,
                "customer_email" => $customer_email,
                "customer_phone" => $customer_phone,
            ],
            "order_meta" => [
                "return_url" => route('clients.invoice.show', $invoiceId),
                "notify_url" => route('cashfree.webhook'),
            ]
        ];

        $curl = curl_init($url);
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($curl);
    
        curl_close($curl);

        $response_object = json_decode($response);
    
        if (isset($response_object->payment_session_id)) {
            $payment_session_id = $response_object->payment_session_id;
            return route('cashfree.payment', ['invoiceId' => $invoiceId, 'payment_session_id' => $payment_session_id]);
        } else {
            return 'Unexpected response format';
        }
    }
    /**
     * Handle Cashfree webhook request.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        $secretKey = ExtensionHelper::getConfig('Cashfree', 'test_mode') ? ExtensionHelper::getConfig('Cashfree', 'test_api_secret') : ExtensionHelper::getConfig('Cashfree', 'cashfree_api_secret');        
        $payload = $request->getContent();

        $timestamp = $request->header('x-webhook-timestamp');
        $signature = $request->header('x-webhook-signature');

        if (!$timestamp || !$signature || !$payload) {
            return response('Bad Request', 400);
        }

        $signedPayload = $timestamp . $payload;
        $expectedSignature = base64_encode(hash_hmac('sha256', $signedPayload, $secretKey, true));

        if ($signature !== $expectedSignature) {
            return response('Signature verification failed', 401);
        }

        $data = json_decode($payload, true);

        $orderId = $data['data']['order']['order_id'];
        $invoiceId = $this->extractInvoiceId($orderId);

        if ($data['type'] === 'PAYMENT_SUCCESS_WEBHOOK') {
            if ($invoiceId) {
                ExtensionHelper::paymentDone($invoiceId);
            }
        }
        return response('Webhook received and processed successfully');
    }

    /**
     * Extract invoice ID from order ID
     *
     * @param string $orderId
     * @return int|null
     */
    private function extractInvoiceId($orderId)
    {
        $numericPart = str_replace(ExtensionHelper::getConfig('Cashfree', 'order_id_prefix'), '', $orderId);
        return (int)$numericPart;
    }
}
