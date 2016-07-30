<?php

namespace Nuwira\Smsgw;

use GuzzleHttp\Client;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

use Config;
use Cache;
use Exception;

use GuzzleHttp\Exception\ClientException;

class Sms
{
    protected $base_url;
    protected $client_id;
    protected $client_secret;
    protected $pretend;
    
    protected $scope = [
        'grant_type' => 'client_credentials',
        'scope' => 'send,statistic,status',
    ];
    
    protected $guzzle;
    
    protected $token;
    protected $cache_key = 'nuwira_sms_access_token';
    protected $pretend_cache_key = 'nuwira_sms_pretend';
    
    public function __construct()
    {
        $this->base_url = Config::get('sms.base_url');
        
        $this->client_id = Config::get('sms.client_id');
        $this->client_secret = Config::get('sms.client_secret');
        
        $this->pretend = Config::get('sms.pretend');
        
        $this->guzzle = new Client([
            'base_uri' => $this->base_url,
            'timeout' => 30,
        ]);
    }
    
    public function send($phone_number, $message)
    {
        if ($this->pretend) {
            return $this->pretendSend($phone_number, $message);
        }
        
        $phone_number = $this->formatPhone($phone_number);
        
        $message = trim($message);
        $message = substr($message, 0, 160);
        
        $token = $this->getToken();
        
        $form_params = [
            'phone_number' => $phone_number,
            'message' => $message,
            'access_token' => $token,
        ];
        
        try {
            $response = $this->guzzle->post('api/v2/messages/send', [
                'form_params' => $form_params
            ]);
            
            $json = $response->getBody()->getContents();
            
            $data = json_decode($json);
            $data = collect($data)->toArray();
            
            return $data;
        } catch (ClientException $e) {
            $this->token = $this->getToken(true);
            
            return $this->send($phone_number, $message);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function check($message_id)
    {
        if ($this->pretend) {
            return $this->pretendCheck($message_id);
        }
        
        $token = $this->getToken();
        
        $query = [
            'access_token' => $token,
        ];
        
        try {
            $response = $this->guzzle->get('api/v2/messages/'.$message_id, [
                'query' => $query
            ]);
            
            $json = $response->getBody()->getContents();
            
            $data = json_decode($json);
            $data = collect($data)->toArray();
            
            return $data;
        } catch (ClientException $e) {
            $this->token = $this->getToken(true);
            
            return $this->check($message_id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function isPretend()
    {
        return $this->pretend;
    }
    
    public function getToken($force = false)
    {
        if ($this->pretend) {
            return $this->pretendCheckToken();
        }
        
        if (empty($this->token) || $force) {
            $this->token = $this->loginGetAccessToken($force);
        }
        
        return $this->token;
    }
    
    public function getURL()
    {
        return $this->base_url;
    }
    
    public function setPretend($pretend)
    {
        return $this->pretend = (bool) $pretend;
    }
    
    protected function pretendCheckToken()
    {
        return 'thisIsJustAPretendingTokenTime'.strtotime('now');
    }
    
    protected function pretendSend($phone_number, $message)
    {
        $phone_number = $this->format->phone($phone_number);
        
        $message = trim($message);
        $message = substr($message, 0, 160);
        
        $inboxes = collect(Cache::get($this->pretend_cache_key, []));
        $message_id = $inboxes->count();
        $message_id++;
        
        $inboxes->push([
            'message_id' => $message_id,
            'message' => $message,
            'phone_number' => $phone_number,
            'sending_datetime' => date('Y-m-d H:i:s'),
            'status' => 'Sent',
        ]);
        Cache::put($this->pretend_cache_key, $inboxes->toArray(), (60*60));
        
        $output = [
            'status' => 200,
            'message' => 'Your message has been queuing on outbox',
            'message_id' => [$message_id],
        ];
        
        return collect($output)->toArray();
    }
    
    protected function pretendCheck($message_id)
    {
        $inboxes = collect(Cache::get($this->pretend_cache_key, []));
        
        $message = $inboxes->filter(function($m) use ($message_id) {
            return $i['message_id'] = $message_id;
        })->first();
        
        if (empty($message)) {
            $output = [
                'status' => 400,
                'message' => 'Message not found',
            ];
        } else {
            $output = [
                'status' => 200,
                'message' => 'Detail of your message',
                'data' => collect($message)->except(['message_id'])->toArray(),
            ];
        }
        
        return collect($output)->toArray();
    }
    
    protected function login()
    {
        $form_params = array_merge($this->scope, [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ]);
        
        try {
            $response = $this->guzzle->post('oauth/access_token', [
                'form_params' => $form_params
            ]);
            
            $json = $response->getBody()->getContents();
            
            $data = json_decode($json);
            $data = collect($data)->toArray();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        
        return $data;
    }
    
    protected function loginGetAccessToken($force = false)
    {
        if ($force) {
            Cache::forget($this->cache_key);
        }
        
        if (Cache::has($this->cache_key)) {
            return Cache::get($this->cache_key);
        } else {
            $data = $this->login();
            
            try {
                $token = $data['access_token'];
                $expires_in_minutes = ($data['expires_in'] / 60);
                
                if (!empty($token)) {
                    Cache::put($this->cache_key, $token, $expires_in_minutes);
                }
                
                return $token;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
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
            } else {
                return $phone_number;
            }    
        } catch (Exception $e) {
            return $phone_number;
        }
    }
}