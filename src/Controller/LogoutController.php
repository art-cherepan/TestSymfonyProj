<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class LogoutController
{
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }
}
