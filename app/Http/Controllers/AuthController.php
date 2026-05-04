<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('lists.index'));
        }

        return back()->withErrors(['email' => 'E-mail ou senha incorretos.'])->onlyInput('email');
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
            'email' => 'required|email|unique:users,email',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        $email = $request->email;
        $code  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any previous codes for this email
        EmailVerification::where('email', $email)->delete();

        EmailVerification::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email
        Mail::raw(
            "Seu código de verificação para Lista de Compras é: {$code}\n\nEste código expira em 15 minutos.",
            function ($msg) use ($email, $code) {
                $msg->to($email)
                    ->subject("Código de verificação: {$code} — Lista de Compras");
            }
        );

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
            'email' => 'required|email',
            'code'  => 'required|digits:6',
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$verification || $verification->isExpired()) {
            return back()->withErrors(['code' => 'Código inválido ou expirado.'])->withInput();
        }

        // Code is valid — show complete registration
        return view('auth.complete', [
            'email' => $request->email,
            'code'  => $request->code,
        ]);
    }

    // Step 5: complete registration
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'code'     => 'required|digits:6',
            'name'     => 'required|string|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        // Re-verify code
        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$verification || $verification->isExpired()) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Código expirado. Solicite um novo.']);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        EmailVerification::where('email', $request->email)->delete();

        Auth::login($user);

        return redirect()->route('lists.index')->with('success', 'Bem-vindo! Sua conta foi criada.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
