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
    public function home(Request $request): Response
    {
        return $this->render('index.html.twig', [
            'host' => $request->getHost(),
        ]);
    }
}
