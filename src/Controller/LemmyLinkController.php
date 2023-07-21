<?php

namespace App\Controller;

use App\Service\CommunityNameParser;
use App\Service\PreferenceManager;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
final class LemmyLinkController extends AbstractController
{
    #[Route('/c/{community}', name: 'app.community', methods: [Request::METHOD_GET])]
    public function communityLink(
        string $community,
        PreferenceManager $preferenceManager,
        #[Autowire('%app.redirect_timeout%')] int $redirectTimeout,
        Request $request,
        CommunityNameParser $communityNameParser,
    ): Response {
        $forceHomeInstance = $request->query->getBoolean('forceHomeInstance');

        try {
            $parsedCommunityName = $communityNameParser->parse($community);
        } catch (InvalidArgumentException) {
            return $this->render('invalid-community.html.twig', [
                'community' => $community,
            ]);
        }

        $preferenceRedirectUrl = $this->generateUrl('app.preferences.instance', [
            'redirectTo' => $this->generateUrl('app.community', [
                'community' => $community,
            ]),
            'community' => $community,
        ]);

        if ($forceHomeInstance) {
            $targetInstance = $parsedCommunityName->homeInstance;
            $url = "https://{$targetInstance}/c/{$parsedCommunityName->name}";
        } else {
            $targetInstance = $preferenceManager->getPreferredLemmyInstance();
            $url = "https://{$targetInstance}/c/{$parsedCommunityName->fullName}";
        }

        if ($targetInstance === null) {
            return $this->redirect($preferenceRedirectUrl);
        }

        return $this->render('redirect-community.html.twig', [
            'timeout' => $redirectTimeout,
            'url' => $url,
            'preferenceUrl' => $preferenceRedirectUrl,
        ]);
    }
}
