<?php

namespace App\Service;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('url_decode', $this->urlDecode(...)),
        ];
    }

    private function urlDecode(string $text): string
    {
        return urldecode($text);
    }
}
