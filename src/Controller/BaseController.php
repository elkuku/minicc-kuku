<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method User|null getUser()
 */
class BaseController extends AbstractController
{
    /**
     * Returns $url if it is a safe same-site path, null otherwise.
     *
     * Guards against open-redirect via user-supplied redirect targets
     * (e.g. a "view" query/form parameter passed back into a redirect()).
     */
    protected function sanitizeLocalRedirect(?string $url): ?string
    {
        if ($url
            && str_starts_with($url, '/')
            && !str_starts_with($url, '//')
            && !str_starts_with($url, '/\\')
        ) {
            return $url;
        }

        return null;
    }
}
