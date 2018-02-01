<?php

namespace Nuwira\Smsgw;

use Exception;
use GuzzleHttp\Client;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Psr\Http\Message\ResponseInterface;

class Sms
{
    protected $base_url;
    protected $api_key;
    protected $to;
    protected $message;
    protected $locale = 'ID';

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function setLocale($locale)
    {
        $this->locale = strtoupper($locale);

        return $this;
    }

    public function to($phone_number)
    {
        $this->to = $this->formatPhone($phone_number);

        return $this;
    }

    public function text($message)
    {
        $this->text = trim($message);

        return $this;
    }

    public function send($phone_number = null, $message = null)
    {
        if (! empty($phone_number)) {
            $this->to($phone_number);
        }

        if (! empty($message)) {
            $this->text($message);
        }

        if (empty($this->to) || empty($this->text)) {
            throw new Exception('Phone number and message must be filled!');
        }

        $response = $this->guzzle->post('send', [
            'form_params' => [
                'to' => $phone_number,
                'msg' => $message,
            ],
        ]);

        return $this->parseResponse($response);
    }

    public function check($id)
    {
        if (empty($id) || ! preg_match('/^(\d*)$/', $id)) {
            throw new Exception('Correct ID must be provided!');
        }

        $response = $this->guzzle->get('check', [
            'query' => [
                'id' => $id,
            ],
        ]);

        return $this->parseResponse($response);
    }

    public function credit()
    {
        $response = $this->guzzle->get('credit');

        return $this->parseResponse($response);
    }

    protected function formatPhone($phone_number)
    {
        $locale = strtoupper($this->locale);

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phone = $phoneUtil->parse($phone_number, $locale);

            if (! $phoneUtil->isValidNumber($phone)) {
                return null;
            }

            return $phoneUtil->format($phone, PhoneNumberFormat::INTERNATIONAL);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function parseResponse(ResponseInterface $response)
    {
        $data = $response->getBody();

        return json_decode($data);
    }
}
