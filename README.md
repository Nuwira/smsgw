## Installation

```console
composer require nuwira/smsgw:~3.0
```

Version 2.0 has been deprecated. Please don't use the version 2.0 or below.

## Configuration

### Laravel version >= 5.5

Nothing to do. This package use package auto-discovery feature.

### Laravel version < 5.5

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

Open your `.env` file or `config/sms.php` and add your URL and API Key.

## Usage

To send SMS, use this function:

```php
SMS::send($phone_number, $message);
```

To check, use this function:

```php
SMS::check($message_id);
```