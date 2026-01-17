<?php

declare(strict_types=1);

namespace App\Controller;

use App\ActivityPub\AccountFetch;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[When(env: 'dev')]
#[Route(path: '/debug', name: 'debug_')]
final class DebugController extends AbstractController
{
    /** @return array<mixed> */
    #[Route()]
    #[Template('base.html.twig')]
    public function index(Request $request, AccountFetch $accountFetch): array
    {
        $a = $accountFetch->fetchFromUsernameInstance('@jrmgxdev@mastodon.social');
        // dd($a);

        return ['a' => $a];
    }
}
