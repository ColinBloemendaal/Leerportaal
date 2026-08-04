<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Gdpr\EraseDataSubject;
use App\Contracts\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;

/**
 * CLAUDE.md §8 (GDPR): "Erasure ... must be implemented as first-class
 * admin actions, not manual SQL." Hangs off the platform user detail
 * page, the one existing "admin acting on a specific user" surface --
 * see UserPolicy::erase() for who may trigger it.
 */
final class EraseDataSubjectController extends Controller
{
    public function store(int $user, EraseDataSubject $erase, UserRepository $users): RedirectResponse
    {
        $target = $users->findById($user);
        abort_if($target === null, 404);

        $this->authorize('erase', $target);

        $erase($target);

        return redirect()
            ->route('admin.platform.users.index')
            ->with('success', __('This user\'s personal data has been erased.'));
    }
}
