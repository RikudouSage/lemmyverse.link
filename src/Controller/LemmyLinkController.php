<?php

namespace App\Controller;

use App\Service\LemmyObjectResolver;
use App\Service\NameParser;
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
        #[Autowire('%app.redirect_timeout%')] int $redirectTimeout,
        #[Autowire('%app.skip_preferred_cookie%')] string $skipPreferred,
        #[Autowire('%app.delay_cookie%')] string $delayCookieName,
        PreferenceManager $preferenceManager,
        Request $request,
        NameParser $communityNameParser,
    ): Response {
        $forceHomeInstance
            = ($request->query->has('forceHomeInstance') && $request->query->getBoolean('forceHomeInstance'))
         || ($request->cookies->has($skipPreferred) && $request->cookies->getBoolean($skipPreferred))
        ;

        $redirectTimeout = $request->cookies->getInt($delayCookieName, $redirectTimeout);

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
                ...$request->query->all(),
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
        if ($request->query->count() > 0) {
            $url .= '?' . http_build_query($request->query->all());
        }

        if ($targetInstance === null) {
            return $this->redirect($preferenceRedirectUrl);
        }

        if ($redirectTimeout === 0) {
            return $this->redirect($url);
        }

        return $this->render('redirect.html.twig', [
            'timeout' => $redirectTimeout,
            'url' => $url,
            'preferenceUrl' => $preferenceRedirectUrl,
        ]);
    }

    #[Route('/u/{user}', name: 'app.user', methods: [Request::METHOD_GET])]
    public function userLink(
        string $user,
        #[Autowire('%app.redirect_timeout%')] int $redirectTimeout,
        #[Autowire('%app.skip_preferred_cookie%')] string $skipPreferred,
        #[Autowire('%app.delay_cookie%')] string $delayCookieName,
        PreferenceManager $preferenceManager,
        Request $request,
        NameParser $usernameParser,
    ): Response {
        $forceHomeInstance
            = ($request->query->has('forceHomeInstance') && $request->query->getBoolean('forceHomeInstance'))
            || ($request->cookies->has($skipPreferred) && $request->cookies->getBoolean($skipPreferred))
        ;

        $redirectTimeout = $request->cookies->getInt($delayCookieName, $redirectTimeout);

        try {
            $parsedName = $usernameParser->parse($user);
        } catch (InvalidArgumentException) {
            return $this->render('invalid-user.html.twig', [
                'user' => $user,
            ]);
        }

        $preferenceRedirectUrl = $this->generateUrl('app.preferences.instance', [
            'redirectTo' => $this->generateUrl('app.user', [
                'user' => $user,
            ]),
            'user' => $user,
        ]);

        if ($forceHomeInstance) {
            $targetInstance = $parsedName->homeInstance;
            $url = "https://{$targetInstance}/u/{$parsedName->name}";
        } else {
            $targetInstance = $preferenceManager->getPreferredLemmyInstance();
            $url = "https://{$targetInstance}/u/{$parsedName->fullName}";
        }

        if ($targetInstance === null) {
            return $this->redirect($preferenceRedirectUrl);
        }

        if ($redirectTimeout === 0) {
            return $this->redirect($url);
        }

        return $this->render('redirect.html.twig', [
            'timeout' => $redirectTimeout,
            'url' => $url,
            'preferenceUrl' => $preferenceRedirectUrl,
        ]);
    }

    #[Route('{originalInstance}/post/{postId}', name: 'app.post', methods: [Request::METHOD_GET])]
    public function postLink(
        string $originalInstance,
        int $postId,
        #[Autowire('%app.redirect_timeout%')] int $redirectTimeout,
        #[Autowire('%app.skip_preferred_cookie%')] string $skipPreferred,
        #[Autowire('%app.delay_cookie%')] string $delayCookieName,
        PreferenceManager $preferenceManager,
        Request $request,
        LemmyObjectResolver $objectResolver,
    ): Response {
        $forceHomeInstance
            = ($request->query->has('forceHomeInstance') && $request->query->getBoolean('forceHomeInstance'))
            || ($request->cookies->has($skipPreferred) && $request->cookies->getBoolean($skipPreferred))
        ;

        $redirectTimeout = $request->cookies->getInt($delayCookieName, $redirectTimeout);

        if ($forceHomeInstance) {
            $targetInstance = $originalInstance;
            $url = "https://{$originalInstance}/post/{$postId}";
        } else {
            $targetInstance = $preferenceManager->getPreferredLemmyInstance();
            if ($targetInstance === null) {
                $url = null;
            } else {
                $targetPostId = $objectResolver->getPostId($postId, $originalInstance, $targetInstance);
                if ($targetPostId === null) {
                    $url = "https://{$originalInstance}/post/{$postId}";
                } else {
                    $url = "https://{$targetInstance}/post/{$targetPostId}";
                }
            }
        }

        if ($redirectTimeout === 0 && $url !== null) {
            return $this->redirect($url);
        }

        $preferenceRedirectUrl = $this->generateUrl('app.preferences.instance', [
            'redirectTo' => $this->generateUrl('app.post', [
                'postId' => $postId,
                'originalInstance' => $originalInstance,
            ]),
            'instance' => $originalInstance,
            'post' => $postId,
        ]);

        if ($targetInstance === null) {
            return $this->redirect($preferenceRedirectUrl);
        }

        return $this->render('redirect.html.twig', [
            'timeout' => $redirectTimeout,
            'url' => $url,
            'preferenceUrl' => $preferenceRedirectUrl,
        ]);
    }

    #[Route('{originalInstance}/comment/{commentId}', name: 'app.comment', methods: [Request::METHOD_GET])]
    public function commentLink(
        string $originalInstance,
        int $commentId,
        #[Autowire('%app.redirect_timeout%')] int $redirectTimeout,
        #[Autowire('%app.skip_preferred_cookie%')] string $skipPreferred,
        #[Autowire('%app.delay_cookie%')] string $delayCookieName,
        PreferenceManager $preferenceManager,
        Request $request,
        LemmyObjectResolver $objectResolver,
    ): Response {
        $forceHomeInstance
            = ($request->query->has('forceHomeInstance') && $request->query->getBoolean('forceHomeInstance'))
            || ($request->cookies->has($skipPreferred) && $request->cookies->getBoolean($skipPreferred))
        ;

        $redirectTimeout = $request->cookies->getInt($delayCookieName, $redirectTimeout);

        if ($forceHomeInstance) {
            $targetInstance = $originalInstance;
            $url = "https://{$originalInstance}/comment/{$commentId}";
        } else {
            $targetInstance = $preferenceManager->getPreferredLemmyInstance();
            if ($targetInstance === null) {
                $url = null;
            } else {
                $targetCommentId = $objectResolver->getCommentId($commentId, $originalInstance, $targetInstance);
                if ($targetCommentId === null) {
                    $url = "https://{$originalInstance}/comment/{$commentId}";
                } else {
                    $url = "https://{$targetInstance}/comment/{$targetCommentId}";
                }
            }
        }

        if ($redirectTimeout === 0 && $url !== null) {
            return $this->redirect($url);
        }

        $preferenceRedirectUrl = $this->generateUrl('app.preferences.instance', [
            'redirectTo' => $this->generateUrl('app.comment', [
                'commentId' => $commentId,
                'originalInstance' => $originalInstance,
            ]),
            'instance' => $originalInstance,
            'comment' => $commentId,
        ]);

        if ($targetInstance === null) {
            return $this->redirect($preferenceRedirectUrl);
        }

        return $this->render('redirect.html.twig', [
            'timeout' => $redirectTimeout,
            'url' => $url,
            'preferenceUrl' => $preferenceRedirectUrl,
        ]);
    }
}
