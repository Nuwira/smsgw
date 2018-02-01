<?php

namespace Nuwira\Smsgw;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class Sms
{
    protected $base_url;
    protected $api_key;
    protected $to;
    protected $message;

    public function __construct()
    {
        $this->base_url = Config::get('sms.base_url');
        $this->api_key = Config::get('sms.api_key');

        $this->guzzle = new Client([
            'base_uri' => $this->base_url,
            'timeout' => 60,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Token '.$this->api_key,
            ],
        ]);
    }

    public function to($phone_number)
    {
        $this->to = $phone_number;

        return $this;
    }

    public function text($message)
    {
        $this->text = $message;

        return $this;
    }

    public function send($phone_number = null, $message = null)
    {
        $phone_number = ! empty($phone_number) ? $phone_number : $this->to;
        $phone_number = $this->formatPhone($phone_number);

        $message = ! empty($message) ? $message : $this->text;

        try {
            $response = $this->guzzle->post('/send', [
                'form_params' => [
                    'to' => $phone_number,
                    'msg' => $message,
                ],
            ]);
            $data = $response->getBody();

            return json_decode($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function check($message_id)
    {
        try {
            $response = $this->guzzle->get('check/', [
                'query' => [
                    'id' => $message_id,
                ],
            ]);
            $data = $response->getBody();

            return json_decode($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function credit()
    {
        try {
            $response = $this->guzzle->get('credit');
            $data = $response->getBody();

            return json_decode($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function formatPhone($phone_number)
    {
        $locale = strtoupper(app()->getLocale());

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phone = $phoneUtil->parse($phone_number, $locale);

            $is_valid = $phoneUtil->isValidNumber($phone, $locale);

            if ($is_valid) {
                return phone_format(
                    $phone_number, $locale, PhoneNumberFormat::INTERNATIONAL
                );
            }

            return $phone_number;
        } catch (Exception $e) {
            return $phone_number;
        }
    }
}
