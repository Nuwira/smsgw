## Installation

```console
composer require nuwira/smsgw
```

## Configuration

Open `config/app.php` add these lines:

```php
'providers' => [
	Nuwira\Smsgw\ServiceProvider::class,
];

'aliases' => [
	'SMS' => Nuwira\Smsgw\Facade::class,
];
```

## Publish Config

```console
php artisan vendor:publish
```

Open your `.env` file or `config/sms.php` and add your *client ID* and *client secret*

## Usage

To send SMS, use this function:

```php
SMS::send($phone_number, $message);
```

To check, use this function:

```php
SMS::check($message_id);
```

All results is array.
