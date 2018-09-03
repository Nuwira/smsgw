## Installation

```console
composer require nuwira/smsgw
```

[Version 3.0](https://github.com/Nuwira/smsgw/tree/v3) is for old API. For new API, use version 4.0 (latest).

[Version 2.0](https://github.com/Nuwira/smsgw/tree/v2) has been deprecated. Please don't use the version 2.0 or below.

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

### Auth

```php
SMS::auth($username, $password);
```

### Profile

```php
SMS::profile();
```

### Send Bulk SMS

```php
$bulk = [
	[
		'to' => $number,
		'message' => $message,
	]
];
SMS::bulk($bulk);
```

### Send Single SMS

```php
SMS::send($to_number, $message);
```

### Check Credit

```php
SMS::credit();
```

### Get Received (Inbox)

```php
SMS::received($startDate, $endDate, $search, $page);
```

### Get Detailed Received SMS (Inbox)

```php
SMS::receivedId($id);
```

### Get Sent (Outbox)

```php
SMS::sent($startDate, $endDate, $status, $search, $page);
```

### Get Detailed Sent SMS (Outbox)

```php
SMS::sentId($id);
```
