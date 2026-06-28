<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserIdentityController extends Controller
{
    public function __invoke(User $user)
    {
        abort_unless(auth()->id() === $user->id || auth()->user()->role === 'admin', 403);
        abort_unless($user->identity_file && Storage::exists($user->identity_file), 404);

        $extension = pathinfo($user->identity_file, PATHINFO_EXTENSION);

        return Storage::download(
            $user->identity_file,
            'identitas-'.$user->id.'.'.$extension
        );
    }
}
