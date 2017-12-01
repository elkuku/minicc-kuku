<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class SecurityController
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render(
            'auth/login.html.twig',
            [
                'last_username' => $helper->getLastUsername(),
                'error'         => $helper->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
