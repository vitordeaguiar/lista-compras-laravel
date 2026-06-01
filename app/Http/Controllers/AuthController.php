<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\EmailVerification;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $ip = $request->ip();
        $blockKey    = 'login_blocked_'  . $ip;
        $attemptsKey = 'login_attempts_' . $ip;

        if (Cache::has($blockKey)) {
            Log::warning('Tentativa de login em IP bloqueado', ['ip' => $ip, 'at' => now()->toIso8601String()]);
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|max:255',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            Cache::forget($attemptsKey);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Contar tentativas com TTL de 15 minutos na primeira falha
        if (!Cache::has($attemptsKey)) {
            Cache::put($attemptsKey, 0, now()->addMinutes(15));
        }
        $attempts = Cache::increment($attemptsKey);

        Log::warning('Tentativa de login falha', [
            'ip'       => $ip,
            'email'    => $request->email,
            'attempts' => $attempts,
            'at'       => now()->toIso8601String(),
        ]);

        if ($attempts >= 5) {
            Cache::put($blockKey, true, now()->addMinutes(15));
            Cache::forget($attemptsKey);
            Log::warning('IP bloqueado por múltiplas tentativas de login', [
                'ip' => $ip,
                'at' => now()->toIso8601String(),
            ]);
        }

        return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
    }

    // Step 1: show form to enter email
    public function showRegister()
    {
        return view('auth.register');
    }

    // Step 2: send verification code
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        $email = strip_tags(trim($request->email));
        $code  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::where('email', $email)->delete();

        EmailVerification::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::raw(
                "Seu código de verificação para Smart Listiq é: {$code}\n\nEste código expira em 15 minutos.",
                function ($msg) use ($email, $code) {
                    $msg->to($email)
                        ->subject("Código de verificação: {$code} — Smart Listiq");
                }
            );
        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de verificação: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['email' => 'Não foi possível enviar o e-mail. Tente novamente em instantes.']);
        }

        return redirect()->route('register.verify', ['email' => $email])
            ->with('info', "Código enviado para {$email}. Verifique sua caixa de entrada.");
    }

    // Step 3: show verify form
    public function showVerify(Request $request)
    {
        $email = $request->query('email', $request->session()->get('reg_email', ''));
        return view('auth.verify', compact('email'));
    }

    // Step 4: verify code + show complete registration form
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code'  => 'required|digits:6',
        ]);

        // Bloqueia brute-force: máx 5 tentativas erradas por IP+e-mail
        $attemptsKey = 'verify_attempts_' . md5($request->ip() . $request->email);
        $attempts    = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return back()
                ->withErrors(['code' => 'Muitas tentativas. Solicite um novo código de verificação.'])
                ->withInput();
        }

        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$verification || $verification->isExpired()) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(30));
            return back()->withErrors(['code' => 'Código inválido ou expirado.'])->withInput();
        }

        Cache::forget($attemptsKey);

        return view('auth.complete', [
            'email' => $request->email,
            'code'  => $request->code,
        ]);
    }

    // Step 5: complete registration
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:255|unique:users,email',
            'code'     => 'required|digits:6',
            'name'     => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$verification || $verification->isExpired()) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Código expirado. Solicite um novo.']);
        }

        $user = User::create([
            'name'     => strip_tags(trim($request->name)),
            'email'    => strip_tags(trim($request->email)),
            'password' => $request->password,
        ]);

        EmailVerification::where('email', $request->email)->delete();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Bem-vindo! Sua conta foi criada.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
