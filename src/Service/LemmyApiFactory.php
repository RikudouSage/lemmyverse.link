<?php

namespace App\Service;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Rikudou\LemmyApi\DefaultLemmyApi;
use Rikudou\LemmyApi\Enum\LemmyApiVersion;
use Rikudou\LemmyApi\LemmyApi;

final readonly class LemmyApiFactory
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
    ) {
    }

    public function getForInstance(string $instance): LemmyApi
    {
        return new DefaultLemmyApi(
            instanceUrl: "https://{$instance}",
            version: LemmyApiVersion::Version3,
            httpClient: $this->client,
            requestFactory: $this->requestFactory,
            strictDeserialization: false,
        );
    }
}
