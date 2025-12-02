<?php

it('can load translations from JSON format', function () {
    $jsonContent = <<<'JSON'
{
    "hello": "Hello",
    "welcome": "Welcome to our application!",
    "goodbye": "Goodbye!"
}
JSON;
    $translations = app(\Lenorix\FluentizyLaravelTools\Services\Formats\JsonTranslations::class)->load($jsonContent);
    expect($translations)->toEqual([
        'hello' => 'Hello',
        'welcome' => 'Welcome to our application!',
        'goodbye' => 'Goodbye!',
    ]);
});

it('can save translations to JSON format', function () {
    $translations = [
        'hello' => 'Hello',
        'welcome' => 'Welcome to our application!',
        'goodbye' => 'Goodbye!',
    ];

    $jsonContent = app(\Lenorix\FluentizyLaravelTools\Services\Formats\JsonTranslations::class)->save($translations);
    expect($jsonContent)->toContain('"hello": "Hello"')
        ->and($jsonContent)->toContain('"welcome": "Welcome to our application!"')
        ->and($jsonContent)->toContain('"goodbye": "Goodbye!"');
});
