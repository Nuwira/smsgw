<?php

namespace Nuwira\Smsgw\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Nuwira\Smsgw\Sms;
use PHPUnit\Framework\TestCase;

class SmsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->guzzle = Mockery::mock(Client::class);
        $this->sms = new Sms($this->guzzle, 'id');
    }

    /**
     * @test
     */
    public function auth_return_correct_values()
    {
        $result = '{
            "token": "thetoken"
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('post')->once()->andReturn($response);

        $output = $this->sms->auth('username', 'password');

        $this->assertSame('thetoken', $output->token);
    }

    /**
     * @test
     */
    public function profile_return_correct_values()
    {
        $result = '{
            "data": {
              "username": "matriphe",
              "name": "Muhammad Zamroni",
              "email": "zam@nuwira.co.id"
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $output = $this->sms->profile();

        $this->assertSame('matriphe', $output->data->username);
        $this->assertSame('Muhammad Zamroni', $output->data->name);
        $this->assertSame('zam@nuwira.co.id', $output->data->email);
    }

    /**
     * @test
     */
    public function bulk_return_correct_values()
    {
        $result = '{
            "message": "All messages are being queued."
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('post')->once()->andReturn($response);

        $output = $this->sms->bulk('[{"to":"081802596094","message":"Test"}]');

        $this->assertSame('All messages are being queued.', $output->message);
    }

    /**
     * @test
     */
    public function credit_return_correct_values()
    {
        $result = '{
            "data": {
              "date": "2018-09-03",
              "credit": 394,
              "used": 1,
              "last": 2,
              "updated_at": "2018-08-07 18:18:56"
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $credit = $this->sms->credit();

        $this->assertSame('2018-09-03', $credit->data->date);
        $this->assertSame(394, $credit->data->credit);
        $this->assertSame(1, $credit->data->used);
        $this->assertSame(2, $credit->data->last);
    }

    /**
     * @test
     */
    public function received_return_correct_values()
    {
        $result = '{
            "data": [
              {
                "id": 1,
                "message": "sebuah pembalasan",
                "char_count": 17,
                "sms_count": 1,
                "is_long": false,
                "created_at": "2018-08-07 16:08:27",
                "updated_at": "2018-08-07 16:08:27",
                "from": "0818-0259-6094",
                "received_at": "2018-08-07 16:08:25",
                "reply_to_id": 2
              },
              {
                "id": 2,
                "message": "tanpa tujuan",
                "char_count": 12,
                "sms_count": 1,
                "is_long": false,
                "created_at": "2018-08-07 15:39:21",
                "updated_at": "2018-08-07 16:06:51",
                "from": "0818-0259-6094",
                "received_at": "2018-08-07 15:22:35",
                "reply_to_id": 0
              }
            ],
            "pagination": {
              "total": 2,
              "count": 2,
              "per_page": 15,
              "current_page": 1,
              "total_pages": 1
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $output = $this->sms->received();

        $this->assertSame(2, $output->pagination->total);
        $this->assertNotEmpty($output->data);
    }

    /**
     * @test
     */
    public function received_id_return_correct_values()
    {
        $result = '{
            "data": {
              "id": 1,
              "message": "sebuah pembalasan",
              "char_count": 17,
              "sms_count": 1,
              "is_long": false,
              "created_at": "2018-08-07 16:08:27",
              "updated_at": "2018-08-07 16:08:27",
              "from": "0818-0259-6094",
              "received_at": "2018-08-07 16:08:25",
              "reply_to_id": 2,
              "replyTo": {
                "id": 2,
                "message": "pesan yang harus dibalas",
                "char_count": 24,
                "sms_count": 1,
                "is_long": false,
                "created_at": "2018-08-07 16:07:40",
                "updated_at": "2018-08-07 16:07:49",
                "to": "0818-0259-6094",
                "status": "sent",
                "in_queue": false,
                "sent_at": "2018-08-07 16:07:49",
                "replies_count": 1
              }
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $output = $this->sms->receivedId(1);

        $this->assertSame(1, $output->data->id);
        $this->assertNotEmpty($output->data);
    }

    /**
     * @test
     */
    public function send_return_correct_values()
    {
        $result = '{
            "data": {
              "id": 3,
              "message": "sebuah pesan dikirim",
              "char_count": 20,
              "sms_count": 1,
              "is_long": false,
              "created_at": "2018-08-08 10:03:55",
              "updated_at": "2018-08-08 10:03:55",
              "to": "0818-0259-6094",
              "status": "pending",
              "in_queue": true,
              "sent_at": "",
              "replies_count": 0
            },
            "message": "SMS is successfuly queued."
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('post')->once()->andReturn($response);

        $output = $this->sms->send('081802596094', 'sebuah pesan dikirim');

        $this->assertSame('SMS is successfuly queued.', $output->message);
        $this->assertSame('0818-0259-6094', $output->data->to);
        $this->assertSame('sebuah pesan dikirim', $output->data->message);
        $this->assertSame('pending', $output->data->status);
    }

    /**
     * @test
     */
    public function sent_return_correct_values()
    {
        $result = '{
            "data": [
              {
                "id": 2,
                "message": "pesan kedua",
                "char_count": 11,
                "sms_count": 1,
                "is_long": false,
                "created_at": "2018-08-07 16:07:40",
                "updated_at": "2018-08-07 16:07:49",
                "to": "0818-0259-6094",
                "status": "sent",
                "in_queue": false,
                "sent_at": "2018-08-07 16:07:49",
                "replies_count": 1
              },
              {
                "id": 1,
                "message": "pesan pertama",
                "char_count": 13,
                "sms_count": 1,
                "is_long": false,
                "created_at": "2018-08-07 12:38:09",
                "updated_at": "2018-08-07 12:38:54",
                "to": "0818-0259-6094",
                "status": "",
                "in_queue": false,
                "sent_at": "2018-08-07 12:38:49",
                "replies_count": 7
              }
            ],
            "pagination": {
              "total": 2,
              "count": 2,
              "per_page": 15,
              "current_page": 1,
              "total_pages": 1
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $output = $this->sms->sent();

        $this->assertSame(2, $output->pagination->total);
        $this->assertNotEmpty($output->data);
    }

    /**
     * @test
     */
    public function sent_id_return_correct_values()
    {
        $result = '{
            "data": {
              "id": 2,
              "message": "pesan kedua",
              "char_count": 11,
              "sms_count": 1,
              "is_long": false,
              "created_at": "2018-08-07 16:07:40",
              "updated_at": "2018-08-07 16:07:49",
              "to": "0818-0259-6094",
              "status": "sent",
              "in_queue": false,
              "sent_at": "2018-08-07 16:07:49",
              "replies_count": 1,
              "replies": [
                {
                  "id": 3,
                  "message": "sebuah pembalasan",
                  "char_count": 17,
                  "sms_count": 1,
                  "is_long": false,
                  "created_at": "2018-08-07 16:08:27",
                  "updated_at": "2018-08-07 16:08:27",
                  "from": "0818-0259-6094",
                  "received_at": "2018-08-07 16:08:25",
                  "reply_to_id": 2
                }
              ]
            }
          }';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $output = $this->sms->sentId(2);

        $this->assertSame(2, $output->data->id);
        $this->assertNotEmpty($output->data);
    }
}
