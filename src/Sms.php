<?php

namespace Nuwira\Smsgw;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Sms
{
    protected $base_url;
    protected $api_key;
    protected $to;
    protected $message;

    public function __construct(Client $guzzle, $locale = 'ID')
    {
        $this->guzzle = $guzzle;
        $this->setLocale($locale);
    }

    public function setLocale($locale)
    {
        $this->locale = strtoupper($locale);

        return $this;
    }

    public function auth($username, $password)
    {
        $response = $this->guzzle->post('auth', [
            'form_params' => compact('username', 'password'),
        ]);

        return $this->parseResponse($response);
    }

    public function profile()
    {
        $response = $this->guzzle->get('profile');

        return $this->parseResponse($response);
    }

    public function bulk($messages)
    {
        if (empty($messages)) {
            throw new Exception('Bulk messages must be filled!');
        }

        if (is_array($messages)) {
            $messages = json_encode($messages);
        }

        $response = $this->guzzle->post('sms/bulk', [
            'form_params' => compact('messages'),
        ]);

        return $this->parseResponse($response);
    }

    public function credit()
    {
        $response = $this->guzzle->get('sms/credit');

        return $this->parseResponse($response);
    }

    public function received($start = null, $end = null, $search = null)
    {
        $response = $this->guzzle->get('sms/received', [
            'query' => compact('start', 'end', 'search'),
        ]);

        return $this->parseResponse($response);
    }

    public function receivedId($id)
    {
        $response = $this->guzzle->get('sms/received/'.$id);

        return $this->parseResponse($response);
    }

    public function send($to = null, $message = null)
    {
        if (empty($to) || empty($message)) {
            throw new Exception('Phone number and message must be filled!');
        }

        $response = $this->guzzle->post('sms/send', [
            'form_params' => [
                'to' => $to,
                'message' => $message,
            ],
        ]);

        return $this->parseResponse($response);
    }

    public function sent($start = null, $end = null, $status = null, $search = null)
    {
        $response = $this->guzzle->get('sms/sent', [
            'query' => compact('start', 'end', 'status', 'search'),
        ]);

        return $this->parseResponse($response);
    }

    public function sentId($id)
    {
        $response = $this->guzzle->get('sms/sent/'.$id);

        return $this->parseResponse($response);
    }

    protected function parseResponse(ResponseInterface $response)
    {
        $data = $response->getBody();

        return json_decode($data);
    }
}
