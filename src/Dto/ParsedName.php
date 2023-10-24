<?php

namespace App\Dto;

final readonly class ParsedName
{
    public function __construct(
        public string $name,
        public string $homeInstance,
        public string $fullName,
    ) {
    }
}
