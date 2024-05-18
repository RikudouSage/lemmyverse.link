<?php

namespace App\Service\LinkProvider;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class LinkProviderManager
{
    /**
     * @param iterable<LinkProvider> $linkProviders
     */
    public function __construct(
        #[TaggedIterator('app.link_provider')]
        private iterable $linkProviders,
    ) {
    }

    public function findProvider(string $software): ?LinkProvider
    {
        foreach ($this->linkProviders as $linkProvider) {
            if ($linkProvider->supports($software)) {
                return $linkProvider;
            }
        }

        return null;
    }
}
