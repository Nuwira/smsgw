<?php

namespace Nuwira\Smsgw;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Matriphe\Format\Format;

use Config;
use Cache;

class Sms
{
    protected $base_url = 'http://apisms.nuwira.net/';
    
    protected $client_id;
    
    protected $client_secret;
    
    protected $scope = [
        'grant_type' => 'client_credentials',
        'scope' => 'admin',
    ];
    
    protected $guzzle;
    
    protected $format;
    
    protected $token;
    
    protected $cache_key = 'nuwira_sms_access_token';
    
    public function __construct()
    {
        $this->client_id = Config::get('sms.client_id');
        $this->client_secret = Config::get('sms.client_secret');
        
        $this->guzzle = new Client([
            'base_uri' => $this->base_url,
            'timeout' => 30,
        ]);
        
        $this->format = new Format;
    }
    
    public function send($phone_number, $message)
    {
        $phone_number = $this->format->phone($phone_number);
        
        $message = trim($message);
        $message = substr($message, 0, 160);
        
        $token = $this->getToken();
        
        $form_params = [
            'phone_number' => $phone_number,
            'message' => $message,
            'access_token' => $token,
        ];
        
        $response = $this->guzzle->post('api/v1/messages/new', [
            'form_params' => $form_params
        ]);
        
        $json = $response->getBody()->getContents();
        
        $data = json_decode($json);
        $data = collect($data)->toArray();
        
        return $data;
    }
    
    public function check($message_id)
    {
        $token = $this->getToken();
        
        $query = [
            'message_id' => $message_id,
            'access_token' => $token,
        ];
        
        $response = $this->guzzle->get('api/v1/messages', [
            'query' => $query
        ]);
        
        $json = $response->getBody()->getContents();
        
        $data = json_decode($json);
        $data = collect($data)->toArray();
        
        return $data;
    }
    
    protected function getToken()
    {
        if (empty($this->token)) {
            $this->token = $this->loginGetAccessToken();
        }
        
        return $this->token;
    }
    
    protected function login()
    {
        $form_params = array_merge($this->scope, [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ]);
        
        $response = $this->guzzle->post('oauth/access_token', [
            'form_params' => $form_params
        ]);
        
        $json = $response->getBody()->getContents();
        
        $data = json_decode($json);
        $data = collect($data)->toArray();
        
        return $data;
    }
    
    protected function loginGetAccessToken()
    {
        if (Cache::has($this->cache_key)) {
            return Cache::get($this->cache_key);
        } else {
            $data = $this->login();
            
            $token = $data['access_token'];
            $expires = Carbon::parse(date('r', $data['expires']));
            
            if (!empty($token)) {
                Cache::put($this->cache_key, $token, $expires);
            }
            
            return $token;
        }
    }
}