<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Models\UserInvite;

final readonly class RevokeInvite
{
    public function __invoke(UserInvite $invite): void
    {
        $invite->delete();
    }
}
