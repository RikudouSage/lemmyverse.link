<?php

namespace App\Service;

use App\Dto\ParsedName;
use InvalidArgumentException;

final class NameParser
{
    public function parse(string $identifier): ParsedName
    {
        $regex = '/^(?<CommunityOrUserName>[a-zA-Z0-9_-]+)@(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])$/';
        if (!preg_match($regex, $identifier, $matches)) {
            throw new InvalidArgumentException("Invalid community: {$identifier}");
        }

        return new ParsedName(
            name: $matches['CommunityOrUserName'],
            homeInstance: $matches['Instance'],
            fullName: $identifier,
        );
    }
}
