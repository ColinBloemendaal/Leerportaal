<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegenerateRecoveryCodes;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RecoveryCodeController extends Controller
{
    public function store(Request $request, RegenerateRecoveryCodes $regenerate): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $codes = $regenerate($user);

        return to_route('settings.two-factor.show')->with('recoveryCodes', $codes);
    }
}
