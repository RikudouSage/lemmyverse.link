<?php

namespace App\Service;

use App\Dto\ActivityPubItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ActivityPubResolver
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getItem(string $id): ?ActivityPubItem
    {
        try {
            $response = $this->httpClient->request(
                Request::METHOD_GET,
                $id,
                [
                    'headers' => [
                        'Accept' => 'application/activity+json',
                    ],
                ],
            );

            $content = $response->getContent();
            $json = json_decode($content, true, JSON_THROW_ON_ERROR);

            return new ActivityPubItem(
                id: $json['id'],
                name: $json['name'],
            );
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface) {
            return null;
        }
    }
}
