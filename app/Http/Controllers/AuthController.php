<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Formulaire login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login utilisateur
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Vérifie si user existe ET actif
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'email' => 'Compte inactif ou inexistant.'
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return $this->redirectToDashboard();
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    /**
     * Formulaire inscription
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Register utilisateur
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true
        ]);

        Auth::login($user);

        return $this->redirectToDashboard();
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirection dashboard selon rôle
     */
    public function redirectToDashboard()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        switch ($user->role) {

            case 'admin':
                return redirect()->route('dashboard');

            case 'agent':
                return redirect()->route('dashboard');

            default:
                return redirect()->route('dashboard');
        }
    }

    /**
     * Confirmation password actions sensibles
     */
    public function confirmPassword(Request $request)
    {
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended();
    }
}
