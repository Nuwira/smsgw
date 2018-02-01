<?php

namespace Nuwira\Smsgw\Tests;

use Exception;
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
        $this->sms = new Sms($this->guzzle);
    }

    /**
     * @test
     */
    public function credit_return_correct_values()
    {
        $result = '{"credit":{"value":"779","activedate":"16 Februari 2018","text":"Success"}}';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $credit = $this->sms->credit();

        $this->assertSame('779', $credit->credit->value);
        $this->assertSame('16 Februari 2018', $credit->credit->activedate);
        $this->assertSame('Success', $credit->credit->text);
    }

    /**
     * @test
     */
    public function check_return_correct_values()
    {
        $result = '{"message":{"id":"12345","status":"Delivered"}}';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('get')->once()->andReturn($response);

        $check = $this->sms->check(12345);

        $this->assertSame('12345', $check->message->id);
        $this->assertSame('Delivered', $check->message->status);
    }

    /**
     * @test
     */
    public function check_return_exception_without_id()
    {
        $this->guzzle->shouldReceive('get')->never();

        $this->expectExceptionObject(
            new Exception('Correct ID must be provided!')
        );

        $this->sms->check(null);
    }

    /**
     * @test
     */
    public function check_return_exception_with_unformatted_id()
    {
        $this->guzzle->shouldReceive('get')->never();

        $this->expectExceptionObject(
            new Exception('Correct ID must be provided!')
        );

        $this->sms->check('abc');
    }

    /**
     * @test
     */
    public function send_return_correct_values()
    {
        $result = '{"message_id":"12345","phone_number":"081802596094","message":"sebuah test","status":"Sending"}';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('post')->once()->andReturn($response);

        $send = $this->sms->send('081802596094', 'sebuah test');

        $this->assertSame('12345', $send->message_id);
        $this->assertSame('081802596094', $send->phone_number);
        $this->assertSame('sebuah test', $send->message);
        $this->assertSame('Sending', $send->status);
    }

    /**
     * @test
     */
    public function send_return_correct_values_implicityly_set_number_and_message()
    {
        $result = '{"message_id":"12345","phone_number":"081802596094","message":"sebuah test","status":"Sending"}';

        $response = new Response(200, [], $result);

        $this->guzzle->shouldReceive('post')->once()->andReturn($response);

        $this->sms->to('081802596094');
        $this->sms->text('sebuah test');
        $send = $this->sms->send();

        $this->assertSame('12345', $send->message_id);
        $this->assertSame('081802596094', $send->phone_number);
        $this->assertSame('sebuah test', $send->message);
        $this->assertSame('Sending', $send->status);
    }

    /**
     * @test
     */
    public function send_return_exception_without_set_phone_number_or_message()
    {
        $this->guzzle->shouldReceive('post')->never();

        $this->expectExceptionObject(
            new Exception('Phone number and message must be filled!')
        );

        $this->sms->send();
    }

    /**
     * @test
     */
    public function send_return_exception_without_set_phone_number()
    {
        $this->guzzle->shouldReceive('post')->never();

        $this->expectExceptionObject(
            new Exception('Phone number and message must be filled!')
        );

        $this->sms->text('sebuah test');
        $this->sms->send();
    }

    /**
     * @test
     */
    public function send_return_exception_without_set_message()
    {
        $this->guzzle->shouldReceive('post')->never();

        $this->expectExceptionObject(
            new Exception('Phone number and message must be filled!')
        );

        $this->sms->send('081802596094');
    }
}
