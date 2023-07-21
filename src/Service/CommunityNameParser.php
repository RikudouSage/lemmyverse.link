<?php

namespace App\Service;

use App\Dto\ParsedDomain;
use InvalidArgumentException;

final class CommunityNameParser
{
    public function parse(string $community): ParsedDomain
    {
        $regex = '/^(?<CommunityName>[a-z0-9_]+)@(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])$/';
        if (!preg_match($regex, $community, $matches)) {
            throw new InvalidArgumentException("Invalid community: {$community}");
        }

        return new ParsedDomain(
            name: $matches['CommunityName'],
            homeInstance: $matches['Instance'],
            fullName: $community,
        );
    }
}
