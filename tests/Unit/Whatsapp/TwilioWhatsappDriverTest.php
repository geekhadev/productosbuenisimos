<?php

use App\Whatsapp\Drivers\TwilioWhatsappDriver;
use Illuminate\Http\Request;

test('parseIncomingMessage extracts phone body and message id', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: 'whatsapp:+14155238886',
        shouldVerifyWebhook: false,
    );

    $request = Request::create('/webhook/whatsapp/twilio', 'POST', [
        'From' => 'whatsapp:+56912345678',
        'To' => 'whatsapp:+14155238886',
        'Body' => 'Hola mundo',
        'MessageSid' => 'SM123456',
    ]);

    $message = $driver->parseIncomingMessage($request);

    expect($message)->not->toBeNull()
        ->and($message->phone)->toBe('+56912345678')
        ->and($message->body)->toBe('Hola mundo')
        ->and($message->messageId)->toBe('SM123456')
        ->and($message->rawFrom)->toBe('whatsapp:+56912345678')
        ->and($message->toNumber)->toBe('+14155238886');
});

test('parseIncomingMessage returns null when from is missing', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: 'whatsapp:+14155238886',
        shouldVerifyWebhook: false,
    );

    $request = Request::create('/webhook/whatsapp/twilio', 'POST', [
        'Body' => 'Hola',
        'MessageSid' => 'SM123456',
    ]);

    expect($driver->parseIncomingMessage($request))->toBeNull();
});

test('splitMessage divides long messages into chunks of at most 4096 characters', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: 'whatsapp:+14155238886',
        shouldVerifyWebhook: false,
    );

    $splitMessage = new ReflectionMethod(TwilioWhatsappDriver::class, 'splitMessage');
    $splitMessage->setAccessible(true);

    $longBody = str_repeat('a', 5000);
    $chunks = $splitMessage->invoke($driver, $longBody, 4096);

    expect($chunks)->toHaveCount(2)
        ->and(mb_strlen($chunks[0]))->toBeLessThanOrEqual(4096)
        ->and(mb_strlen($chunks[1]))->toBeLessThanOrEqual(4096)
        ->and(implode('', $chunks))->toBe($longBody);
});

test('splitMessage keeps short messages as a single chunk', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: 'whatsapp:+14155238886',
        shouldVerifyWebhook: false,
    );

    $splitMessage = new ReflectionMethod(TwilioWhatsappDriver::class, 'splitMessage');
    $splitMessage->setAccessible(true);

    $chunks = $splitMessage->invoke($driver, 'Mensaje corto', 4096);

    expect($chunks)->toBe(['Mensaje corto']);
});

test('from number is normalized to whatsapp channel on construction', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: '+14155238886',
        shouldVerifyWebhook: false,
    );

    $fromNumber = new ReflectionProperty(TwilioWhatsappDriver::class, 'fromNumber');
    $fromNumber->setAccessible(true);

    expect($fromNumber->getValue($driver))->toBe('whatsapp:+14155238886');
});

test('formatWhatsappAddress prefixes destination numbers for twilio api', function () {
    $driver = new TwilioWhatsappDriver(
        accountSid: 'ACtest',
        authToken: 'token',
        fromNumber: 'whatsapp:+14155238886',
        shouldVerifyWebhook: false,
    );

    $formatWhatsappAddress = new ReflectionMethod(TwilioWhatsappDriver::class, 'formatWhatsappAddress');
    $formatWhatsappAddress->setAccessible(true);

    expect($formatWhatsappAddress->invoke($driver, '+56912345678'))->toBe('whatsapp:+56912345678')
        ->and($formatWhatsappAddress->invoke($driver, 'whatsapp:+56912345678'))->toBe('whatsapp:+56912345678');
});
