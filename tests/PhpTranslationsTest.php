<?php

it('can load translations from PHP format', function () {
    $phpContent = <<<'PHP'
<?php

return [
    'hello' => 'Hello',
    'welcome' => 'Welcome to our application!',
    'goodbye' => 'Goodbye!',
];
PHP;

    $translations = app(\Lenorix\FluentizyLaravelTools\Services\Formats\PhpTranslations::class)->load($phpContent);
    expect($translations)->toEqual([
        'hello' => 'Hello',
        'welcome' => 'Welcome to our application!',
        'goodbye' => 'Goodbye!',
    ]);
});

it('can save translations to PHP format', function () {
    $translations = [
        'hello' => 'Hello',
        'welcome' => 'Welcome to our application!',
        'goodbye' => 'Goodbye!',
    ];

    $phpContent = app(\Lenorix\FluentizyLaravelTools\Services\Formats\PhpTranslations::class)->save($translations);
    expect($phpContent)->toContain("'hello' => 'Hello'")
        ->and($phpContent)->toContain("'welcome' => 'Welcome to our application!'")
        ->and($phpContent)->toContain("'goodbye' => 'Goodbye!'");
});
