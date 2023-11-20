<?php

namespace App\Service;

use JsonException;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Rikudou\LemmyApi\Exception\LemmyApiException;
use Rikudou\LemmyApi\Response\Model\Comment;
use Rikudou\LemmyApi\Response\Model\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LemmyObjectResolver
{
    public function __construct(
        private LemmyApiFactory $apiFactory,
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getPostId(int $originalPostId, string $originalInstance, string $targetInstance): ?int
    {
        if ($originalInstance === $targetInstance) {
            return $originalPostId;
        }

        $originalPost = $this->getPostById($originalInstance, $originalPostId);
        $activityPubId = $originalPost->apId;

        try {
            $targetInstance = $this->getRealTargetInstance($targetInstance);
            $targetPost = $this->getPostByActivityPubId($targetInstance, $activityPubId);
        } catch (LogicException) {
            return null;
        }

        return $targetPost?->id;
    }

    public function getCommentId(int $originalCommentId, string $originalInstance, string $targetInstance): ?int
    {
        if ($originalInstance === $targetInstance) {
            return $originalCommentId;
        }

        $originalComment = $this->getCommentById($originalInstance, $originalCommentId);
        $activityPubId = $originalComment->apId;

        try {
            $targetInstance = $this->getRealTargetInstance($targetInstance);
            $targetComment = $this->getCommentByActivityPubId($targetInstance, $activityPubId);
        } catch (LogicException) {
            return null;
        }

        return $targetComment?->id;
    }

    public function getPostById(string $instance, int $postId): Post
    {
        $cacheItem = $this->cache->getItem("post_{$instance}_{$postId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get(); // @phpstan-ignore-line
        }

        $api = $this->apiFactory->getForInstance($instance);
        $post = $api->post()->get($postId);
        $cacheItem->set($post->post);
        $this->cache->save($cacheItem);

        return $post->post;
    }

    public function getCommentById(string $instance, int $commentId): Comment
    {
        $cacheItem = $this->cache->getItem("comment_{$instance}_{$commentId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get(); // @phpstan-ignore-line
        }

        $api = $this->apiFactory->getForInstance($instance);
        $comment = $api->comment()->get($commentId);
        $cacheItem->set($comment->comment);
        $this->cache->save($cacheItem);

        return $comment->comment;
    }

    private function getPostByActivityPubId(string $instance, string $postId): ?Post
    {
        $cacheKeyPostId = str_replace(str_split(ItemInterface::RESERVED_CHARACTERS), '_', $postId);
        $cacheItem = $this->cache->getItem("post_{$instance}_{$cacheKeyPostId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get(); // @phpstan-ignore-line
        }

        $api = $this->apiFactory->getForInstance($instance);

        try {
            $post = $api->miscellaneous()->resolveObject(query: $postId)->post;
        } catch (LemmyApiException) {
            return null;
        }
        if ($post === null) {
            return null;
        }
        $cacheItem->set($post->post);
        $this->cache->save($cacheItem);

        return $post->post;
    }

    private function getCommentByActivityPubId(string $instance, string $commentId): ?Comment
    {
        $cacheKeyCommentId = str_replace(str_split(ItemInterface::RESERVED_CHARACTERS), '_', $commentId);
        $cacheItem = $this->cache->getItem("comment_{$instance}_{$cacheKeyCommentId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get(); // @phpstan-ignore-line
        }

        $api = $this->apiFactory->getForInstance($instance);

        try {
            $comment = $api->miscellaneous()->resolveObject(query: $commentId)->comment;
        } catch (LemmyApiException) {
            return null;
        }
        if ($comment === null) {
            return null;
        }
        $cacheItem->set($comment->comment);
        $this->cache->save($cacheItem);

        return $comment->comment;
    }

    private function getRealTargetInstance(string $targetInstance, ?string $instanceToCheck = null): string
    {
        $instanceToCheck ??= $targetInstance;

        $result = fn () => $this->getRealTargetInstance($targetInstance, $this->getParentDomain($instanceToCheck));

        $cacheItem = $this->cache->getItem("target_instance_{$targetInstance}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get(); // @phpstan-ignore-line
        }

        $url = "https://{$instanceToCheck}/.well-known/nodeinfo";
        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $result();
        }

        try {
            $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
            assert(is_array($json));
        } catch (JsonException) {
            return $result();
        }

        if (!isset($json['links'][0]['href'])) {
            return $result();
        }

        $response = $this->httpClient->request(Request::METHOD_GET, $json['links'][0]['href']);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $result();
        }

        try {
            $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
            assert(is_array($json));
        } catch (JsonException) {
            return $result();
        }

        if (($json['software']['name'] ?? null) !== 'lemmy') {
            return $result();
        }
        $cacheItem->set($instanceToCheck);
        $this->cache->save($cacheItem);

        return $instanceToCheck;
    }

    private function getParentDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        if (count($parts) <= 2) {
            throw new LogicException('Cannot get a parent domain for a top level domain');
        }

        unset($parts[0]);

        return implode('.', $parts);
    }
}
