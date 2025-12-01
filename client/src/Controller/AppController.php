<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class AppController extends AbstractController
{
    /** @return array<mixed> */
    #[Route('', name: 'index')]
    #[Template('index.html.twig')]
    public function index(): array
    {
        return [];
    }
}
