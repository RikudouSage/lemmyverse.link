<?php

namespace App\Service\LinkProvider;

use App\Exception\UnsupportedFeatureException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.link_provider')]
interface LinkProvider
{
    public function supports(string $software): bool;

    /**
     * @throws UnsupportedFeatureException
     */
    public function getCommunityLink(string $instance, string $communityName): string;

    /**
     * @throws UnsupportedFeatureException
     */
    public function getUserLink(string $instance, string $userName): string;

    /**
     * @throws UnsupportedFeatureException
     */
    public function getPostLink(string $instance, string $postId): string;

    /**
     * @throws UnsupportedFeatureException
     */
    public function getCommentLink(string $instance, string $commentId): string;
}
