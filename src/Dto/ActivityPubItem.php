<?php

namespace App\Dto;

final readonly class ActivityPubItem
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
