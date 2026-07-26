<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\StartImpersonation;
use App\Actions\Auth\StopImpersonation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StartImpersonationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ImpersonationController extends Controller
{
    public function store(StartImpersonationRequest $request, int $user, StartImpersonation $startImpersonation): RedirectResponse
    {
        $target = User::query()->find($user);
        abort_if($target === null, 404);

        $this->authorize('impersonate', $target);

        $impersonatorId = $request->user()?->getAuthIdentifier();
        abort_unless(is_int($impersonatorId), 401);

        $startImpersonation($request->toDto($impersonatorId, $target->id));

        $request->session()->regenerate();

        return redirect('/')->with('success', __('Now impersonating :name.', ['name' => $target->name]));
    }

    public function destroy(Request $request, StopImpersonation $stopImpersonation): RedirectResponse
    {
        $impersonationId = $request->session()->get('impersonation_id');
        $impersonatorId = $request->session()->get('impersonator_id');

        abort_unless(is_int($impersonationId) && is_int($impersonatorId), 400);

        $stopImpersonation($impersonationId, $impersonatorId);

        $request->session()->regenerate();

        return redirect('/')->with('success', __('Stopped impersonating.'));
    }
}
