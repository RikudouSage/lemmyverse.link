<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class PreferenceManager
{
    public function __construct(
        private RequestStack $requestStack,
        #[Autowire('%app.preferred_instance_cookie%')]
        private string $cookieName,
    ) {
    }

    public function getPreferredLemmyInstance(): ?string
    {
        return $this->requestStack->getCurrentRequest()?->cookies->get($this->cookieName);
    }
}
