<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    // AUTH — Login / Register / Logout
    // ════════════════════════════════════════════════════════════════

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors(['email' => 'Compte inactif ou inexistant.']);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectToDashboard();
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed',
            'role'       => 'required|string|in:admin,agent,viewer',
            'department' => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'department' => $validated['department'] ?? null,
            'phone'      => $validated['phone']       ?? null,
            'is_active'  => true,
        ]);

        Auth::login($user);
        return $this->redirectToDashboard();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // ════════════════════════════════════════════════════════════════
    // Redirection post-login
    // Tous les rôles → même dashboard ; @can Blade / Alpine filtrent.
    // ════════════════════════════════════════════════════════════════

    public function redirectToDashboard()
    {
        return Auth::user()
            ? redirect()->route('dashboard')
            : redirect('/login');
    }

    // ════════════════════════════════════════════════════════════════
    // Confirmation mot de passe (actions sensibles)
    // ════════════════════════════════════════════════════════════════

    public function confirmPassword(Request $request)
    {
        if (!Auth::guard('web')->validate([
            'email'    => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());
        return redirect()->intended();
    }

    // ════════════════════════════════════════════════════════════════
    // PROFIL — utilisateur connecté
    // Route : PATCH /profile        → profile.update
    // Route : PATCH /password       → password.update
    // ════════════════════════════════════════════════════════════════

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
        ]);

        Auth::user()->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mot de passe actuel incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }

    // ════════════════════════════════════════════════════════════════
    // ADMINISTRATION DES UTILISATEURS (admin uniquement)
    // Routes API consommées par Alpine.js via fetch()
    // ════════════════════════════════════════════════════════════════

    /** POST /api/users */
    public function storeUser(Request $request)
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed',
            'role'       => 'required|string|in:admin,agent,viewer',
            'department' => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'is_active'  => 'boolean',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'department' => $validated['department'] ?? null,
            'phone'      => $validated['phone']       ?? null,
            'is_active'  => $validated['is_active']   ?? true,
        ]);

        return response()->json(['success' => true, 'data' => $this->userToArray($user)]);
    }

    /** PUT /api/users/{user} */
    public function updateUser(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'role'       => 'required|string|in:admin,agent,viewer',
            'department' => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'is_active'  => 'boolean',
        ]);

        // Protection : un admin ne peut pas se rétrograder lui-même
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre rôle.',
            ], 422);
        }

        $user->update($validated);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['success' => true, 'data' => $this->userToArray($user->fresh())]);
    }

    /** PATCH /api/users/{user}/toggle-status */
    public function toggleUserStatus(User $user)
    {
        Gate::authorize('update', $user);

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas désactiver votre propre compte.',
            ], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Compte activé.' : 'Compte désactivé.',
            'data'    => $this->userToArray($user),
        ]);
    }

    /** DELETE /api/users/{user} */
    public function destroyUser(User $user)
    {
        Gate::authorize('delete', $user);

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════════
    // Helper privé — sérialise un User pour Alpine.js
    // ════════════════════════════════════════════════════════════════

    private function userToArray(User $u): array
    {
        return [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role'       => $u->role,
            'department' => $u->department,
            'phone'      => $u->phone,
            'is_active'  => $u->is_active,
            'created_at' => $u->created_at?->toISOString(),
            'updated_at' => $u->updated_at?->toISOString(),
            'is_current' => $u->id === Auth::id(),
        ];
    }
}