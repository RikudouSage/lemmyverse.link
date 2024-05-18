<?php

namespace App\Service;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class WebFingerParser
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function getSoftware(string $instance): ?string
    {
        $cacheItem = $this->cacheItemPool->getItem("web_finger_{$instance}");
        if ($cacheItem->isHit()) {
            assert(is_string($cacheItem->get()) || $cacheItem->get() === null);

            return $cacheItem->get();
        }

        try {
            $url = "https://{$instance}/.well-known/nodeinfo";
            $response = $this->httpClient->request(Request::METHOD_GET, $url);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                $cacheItem->set(null);

                return null;
            }

            $json = json_decode($response->getContent(), true);
            assert(is_array($json));
            if (!isset($json['links'])) {
                $cacheItem->set(null);

                return null;
            }

            foreach ($json['links'] as $link) {
                if (!str_contains($link['rel'] ?? '', 'nodeinfo.diaspora.software/ns/schema/2')) {
                    continue;
                }
                $response = $this->httpClient->request(Request::METHOD_GET, $link['href']);
                if ($response->getStatusCode() !== Response::HTTP_OK) {
                    continue;
                }
                $nodeInfo = json_decode($response->getContent(), true);
                assert(is_array($nodeInfo));
                if (!isset($nodeInfo['software']['name'])) {
                    continue;
                }

                $cacheItem->set($nodeInfo['software']['name']);

                return $nodeInfo['software']['name'];
            }

            $cacheItem->set(null);

            return null;
        } finally {
            $cacheItem->expiresAfter(new DateInterval('PT2H'));
            $this->cacheItemPool->save($cacheItem);
        }
    }
}
