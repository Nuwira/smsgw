## Installation

```console
composer require nuwira/smsgw:~3.0
```

## Configuration

Open `config/app.php` add these lines:

```php
'providers' => [
	Nuwira\Smsgw\SmsServiceProvider::class,
];

'aliases' => [
	'SMS' => Nuwira\Smsgw\SmsFacade::class,
];
```

## Publish Config

```console
php artisan vendor:publish
```

Open your `.env` file or `config/sms.php` and add your *API Key* 

## Usage

To send SMS, use this function:

```php
SMS::send($phone_number, $message);
```

To check, use this function:

```php
SMS::check($message_id);
```