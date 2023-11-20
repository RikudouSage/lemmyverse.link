<?php

namespace App\Controller;

use App\Service\LemmyObjectResolver;
use App\Service\PopularInstancesService;
use Rikudou\LemmyApi\Exception\LemmyApiException;
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
        LemmyObjectResolver $lemmyObjectResolver,
    ): Response {
        if ($request->query->has('instance')) {
            if ($request->query->has('post')) {
                try {
                    $post = $lemmyObjectResolver->getPostById(
                        $request->query->get('instance'),
                        $request->query->getInt('post'),
                    );
                } catch (LemmyApiException) {
                    $post = null;
                }
            }
            if ($request->query->has('comment')) {
                try {
                    $comment = $lemmyObjectResolver->getCommentById(
                        $request->query->get('instance'),
                        $request->query->getInt('comment'),
                    );
                    $post = $lemmyObjectResolver->getPostById(
                        $request->query->get('instance'),
                        $comment->postId,
                    );
                } catch (LemmyApiException) {
                    $comment = null;
                }
            }
        }

        return $this->render('save-instance-preference.html.twig', [
            'redirectTo' => $request->query->get('redirectTo'),
            'community' => $request->query->get('community'),
            'user' => $request->query->get('user'),
            'instances' => $popularInstances->getPopularInstances(),
            'cookieName' => $cookieName,
            'skipCookieName' => $skipCookieName,
            'post' => $post ?? null,
            'comment' => $comment ?? null,
        ]);
    }
}
