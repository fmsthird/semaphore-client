<?php

namespace Semaphore;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

/**
 * Class SemaphoreClient
 * @package Semaphore
 */
class SemaphoreClient
{
    const API_BASE = 'https://api.semaphore.co/api/v4/';

    public $apiKey;
    public $senderName = null;
    protected $client;

    /**
     * SemaphoreClient constructor.
     * @param $apiKey
     * @param array $options ( e.g. senderName, apiBase )
     */
    public function __construct($apiKey, array $options)
    {
        $this->apiKey = $apiKey;

        $this->senderName = 'SEMAPHORE';
        if (isset($options['senderName'])) $this->senderName = $options['senderName'];

        $apiBase = SemaphoreClient::API_BASE;
        if (isset( $options['apiBase'] )) $apiBase = $options['apiBase'];
        $this->client = new Client(['base_uri' => $apiBase, 'query' => [ 'apikey' => $this->apiKey ]]);
    }

    /**
     * Check the balance of your account
     *
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function balance()
    {
        $response = $this->client->get('account');
        return $response->getBody();
    }

    /**
     * Send SMS message(s)
     *
     * @param $recipient
     * @param $message - The message you want to send
     * @param null $senderName
     * @return StreamInterface
     * @throws GuzzleException
     * @throws Exception
     * @internal param $number - The recipient phone number(s)
     * @internal param null $senderId - Optional Sender ID (defaults to initialized value or SEMAPHORE)
     * @internal param bool|false $bulk - Optional send as bulk
     */
    public function send($recipient, $message)
    {
        $recipients = explode( ',', $recipient);
        if(count($recipients) > 1000) throw new Exception('API is limited to sending to 1000 recipients at a time');

        $params = [
            'form_params' => [
                'apikey' =>  $this->apiKey,
                'message' => $message,
                'number' => $recipient,
                'sendername' => $this->senderName
            ]
        ];

        $response = $this->client->post('messages', $params );
        return $response->getBody();
    }

    /**
     * Retrieves data about a specific message
     *
     * @param $messageId - The encoded ID of the message
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function message($messageId)
    {
        $params = ['query' => [ 'apikey' =>  $this->apiKey ]];
        $response = $this->client->get( 'messages/' . $messageId, $params );
        return $response->getBody();
    }

    /**
     * Retrieves up to 100 messages, offset by page
     * @param array $options ( e.g. limit, page, startDate, endDate, status, network, senderName )
     * @return StreamInterface
     * @throws GuzzleException
     * @internal param null $page - Optional page for results past the initial 100
     */
    public function messages($options)
    {
        $params = [
            'query' => [
                'apikey' =>  $this->apiKey,
                'limit' => 100,
                'page' => 1
            ]
        ];

        // Set optional parameters
        if(array_key_exists( 'limit', $options ))
            $params['query']['limit'] = $options['limit'];

        if(array_key_exists( 'page', $options ))
            $params['query']['page'] = $options['page'];

        if(array_key_exists( 'startDate', $options ))
            $params['query']['startDate'] = $options['startDate'];

        if(array_key_exists( 'endDate', $options ))
            $params['query']['endDate'] = $options['endDate'];

        if(array_key_exists( 'status', $options ))
            $params['query']['status'] = $options['status'];

        if(array_key_exists( 'network', $options ))
            $params['query']['network'] = $options['network'];

        if(array_key_exists( 'senderName', $options ))
            $params['query']['sendername'] = $options['senderName'];

        $response = $this->client->get( 'messages', $params );
        return $response->getBody();
    }

    /**
     * Get account details
     *
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function account()
    {
        $response = $this->client->get( 'account' );
        return $response->getBody();
    }

    /**
     * Get users associated with the account
     *
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function users()
    {
        $response = $this->client->get( 'account/users' );
        return $response->getBody();
    }

    /**
     * Get sender names associated with the account
     *
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function senderNames()
    {
        $response = $this->client->get( 'account/sendernames' );
        return $response->getBody();

    }

    /**
     * Get transactions associated with the account
     *
     * @return StreamInterface
     * @throws GuzzleException
     */
    public function transactions()
    {
        $response = $this->client->get( 'account/transactions' );
        return $response->getBody();
    }
}