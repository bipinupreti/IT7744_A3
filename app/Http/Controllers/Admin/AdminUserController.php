<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->orderBy('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->accountRules(),
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = 'user';
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->accountRules($user));

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === 'user', 403);

        try {
            $user->delete();
        } catch (QueryException $exception) {
            // MySQL rejects deletion when an image post still references this user.
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            return redirect()->route('admin.users.index')
                ->with('status', 'Delete this user’s image posts before deleting the account.');
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'User deleted.');
    }

    private function accountRules(?User $user = null): array
    {
        $uniqueEmail = Rule::unique('users', 'email');

        if ($user !== null) {
            $uniqueEmail->ignore($user);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
        ];
    }
}