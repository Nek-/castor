<?php

use Castor\Attribute\AsTask;

use function Castor\request;

#[AsTask(description: 'Make HTTP request')]
function httpRequest(): void
{
    $response = request('GET', 'https://example.com/');

    echo $response->getContent();
}
