<?php

namespace App\Service\LinkProvider;

use App\Exception\UnsupportedFeatureException;

final readonly class LemmyLinkProvider implements LinkProvider
{
    public function supports(string $software): bool
    {
        return in_array(strtolower($software), ['lemmy', 'piefed'], true);
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
