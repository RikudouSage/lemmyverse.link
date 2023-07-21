<?php

namespace App\Tests\Service;

use App\Service\PreferenceManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PreferenceManagerTest extends KernelTestCase
{
    private RequestStack $requestStack;

    private PreferenceManager $instance;

    protected function setUp(): void
    {
        $this->requestStack = self::getContainer()->get('request_stack');
        $this->instance = new PreferenceManager(
            $this->requestStack,
            'test_cookie',
        );
    }

    public function testGetPreferredLemmyInstance(): void
    {
        // no request
        $this->assertNull($this->instance->getPreferredLemmyInstance());

        // request without cookie
        $this->requestStack->push(new Request());
        $this->assertNull($this->instance->getPreferredLemmyInstance());

        $this->requestStack->push(new Request(cookies: [
            'test_cookie' => 'lemmings.world',
        ]));
        $this->assertSame('lemmings.world', $this->instance->getPreferredLemmyInstance());
    }
}
