<?php

namespace App\Controller;

use App\Service\PopularInstancesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
final class PreferenceController extends AbstractController
{
    #[Route('/preferences/instance', name: 'app.preferences.instance', methods: [Request::METHOD_GET])]
    public function setPreferredInstance(
        Request $request,
        PopularInstancesService $popularInstances,
        #[Autowire('%app.preferred_instance_cookie%')] string $cookieName,
        #[Autowire('%app.skip_preferred_cookie%')] string $skipCookieName,
    ): Response {
        return $this->render('save-instance-preference.html.twig', [
            'redirectTo' => $request->query->get('redirectTo'),
            'community' => $request->query->get('community'),
            'user' => $request->query->get('user'),
            'instances' => $popularInstances->getPopularInstances(),
            'cookieName' => $cookieName,
            'skipCookieName' => $skipCookieName,
        ]);
    }
}
