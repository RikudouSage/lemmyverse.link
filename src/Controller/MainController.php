<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
final class MainController extends AbstractController
{
    #[Route('/', name: 'app.home', methods: [Request::METHOD_GET])]
    public function index(Request $request): Response
    {
        return $this->render('index.html.twig', [
            'host' => $request->getHost(),
        ]);
    }

    #[Route('/how-does-it-work', name: 'app.explanation', methods: [Request::METHOD_GET])]
    public function howDoesItWork(Request $request): Response
    {
        return $this->render('how-does-it-work.html.twig', [
            'host' => $request->getHost(),
        ]);
    }
}
