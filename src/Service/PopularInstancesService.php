<?php

namespace App\Service;

final class PopularInstancesService
{
    /**
     * @return array<string>
     *
     * @todo actually get the list without hardcoding it and remove @codeCoverageIgnore
     * @codeCoverageIgnore
     */
    public function getPopularInstances(): array
    {
        return [
            'lemmy.world',
            'lemmy.ml',
            'lemm.ee',
            'feddit.de',
            'lemmynsfw.com',
            'lemmy.ca',
            'lemmings.world',
        ];
    }
}
