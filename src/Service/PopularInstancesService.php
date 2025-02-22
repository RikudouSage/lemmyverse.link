<?php

namespace App\Service;

final class PopularInstancesService
{
    /**
     * @return array<string>
     *
     * @todo actually get the list without hardcoding it and remove @codeCoverageIgnore
     *
     * @codeCoverageIgnore
     */
    public function getPopularInstances(): array
    {
        return [
            'lemmy.world',
            'lemm.ee',
            'lemmy.ca',
            'beehaw.org',
            'lemmy.dbzer0.com',
            'lemmings.world',
            'lemmy.blahaj.zone',
            'discuss.online',
        ];
    }
}
