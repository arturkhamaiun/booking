<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\IssueTokenRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class IssueToken extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\IssueTokenRequest  $request
     * @return array
     */
    public function __invoke(IssueTokenRequest $request): array
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return ['token' => $user->createToken('token')->plainTextToken];
    }
}
