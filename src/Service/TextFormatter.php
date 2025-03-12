<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

class TextFormatter
{

    public function formatRUC(User $user): string
    {
        $ruc = '?';

        if ($user->getInqRuc()) {
            $ruc = $user->getInqRuc();

            if (13 === strlen($ruc)) {
                $rucs = str_split($ruc, 10);

                $ruc = trim(chunk_split($rucs[0], 3, ' ')) . ' ' . $rucs[1];
            } else {
                $ruc = chunk_split($ruc, 3, ' ');
            }
        } elseif ($user->getInqCi()) {
            $ruc = str_replace('-', '', $user->getInqCi());
            $ruc = chunk_split($ruc, 3, ' ');
        }

        return trim($ruc);
    }
}
