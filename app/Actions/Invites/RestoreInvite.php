<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Models\UserInvite;

final readonly class RestoreInvite
{
    public function __invoke(UserInvite $invite): void
    {
        $invite->restore();
    }
}
