<?php

namespace App\Dto;

final readonly class ParsedDomain
{
    public function __construct(
        public string $name,
        public string $homeInstance,
        public string $fullName,
    ) {
    }
}
