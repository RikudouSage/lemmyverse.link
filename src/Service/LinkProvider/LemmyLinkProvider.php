<?php

namespace App\Service\LinkProvider;

use App\Exception\UnsupportedFeatureException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: -1000)]
final readonly class LemmyLinkProvider implements LinkProvider
{
    public function supports(string $software): bool
    {
        return true;
    }

    public function getCommunityLink(string $instance, string $communityName): string
    {
        return "https://{$instance}/c/{$communityName}";
    }

    public function getUserLink(string $instance, string $userName): string
    {
        return "https://{$instance}/u/{$userName}";
    }

    public function getPostLink(string $instance, string $postId): string
    {
        throw new UnsupportedFeatureException();
    }

    public function getCommentLink(string $instance, string $commentId): string
    {
        throw new UnsupportedFeatureException();
    }
}
