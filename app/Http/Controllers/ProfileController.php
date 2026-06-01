<?php
namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $settings = UserSetting::where('user_id', $user->id)->first();
        if (!$settings) {
            $settings = new UserSetting([
                'theme'                  => 'dark',
                'accent_color'           => '#2dd4bf',
                'salary_day'             => 5,
                'monthly_budget'         => 0,
                'monthly_savings_goal'   => 0,
                'notify_due_days'        => 3,
                'notify_budget_alert'    => true,
                'notify_monthly_summary' => true,
                'notify_list_reminder'   => true,
                'notify_new_month'       => true,
                'notify_email'           => true,
                'notify_push'            => false,
                'auto_copy_fixed'        => true,
                'auto_copy_incomes'      => true,
                'auto_keep_investments'  => true,
                'layout_density'         => 'comfortable',
            ]);
            $settings->user_id = $user->id;
            $settings->save();
        }

        $sessions = collect();
        try {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($s) {
                    $s->is_current  = $s->id === session()->getId();
                    $s->last_active = \Carbon\Carbon::createFromTimestamp($s->last_activity);
                    return $s;
                });
        } catch (\Exception $e) {}

        return view('profile.index', compact('user', 'settings', 'sessions'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        $user->update($data);
        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.'])->withInput();
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'theme'                  => 'required|in:dark,light',
            'accent_color'           => 'required|string|max:20',
            'layout_density'         => 'required|in:comfortable,compact',
            'salary_day'             => 'required|integer|min:1|max:31',
            'monthly_budget'         => 'nullable|numeric|min:0',
            'monthly_savings_goal'   => 'nullable|numeric|min:0',
            'notify_due_days'        => 'required|integer|min:0|max:30',
        ]);

        // Checkboxes not present in request = false
        foreach ([
            'notify_budget_alert', 'notify_monthly_summary', 'notify_list_reminder',
            'notify_new_month', 'notify_email', 'notify_push',
            'auto_copy_fixed', 'auto_copy_incomes', 'auto_keep_investments',
        ] as $bool) {
            $data[$bool] = $request->boolean($bool);
        }

        $data['monthly_budget']       = $data['monthly_budget'] ?? 0;
        $data['monthly_savings_goal'] = $data['monthly_savings_goal'] ?? 0;

        $settings = UserSetting::where('user_id', $user->id)->first();
        if ($settings) {
            $settings->update($data);
        } else {
            $settings = new UserSetting($data);
            $settings->user_id = $user->id;
            $settings->save();
        }
        return back()->with('success', 'Configurações salvas com sucesso!');
    }

    public function destroySession(Request $request)
    {
        $sessionId = $request->input('session_id');
        try {
            DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', Auth::id())
                ->where('id', '!=', session()->getId())
                ->delete();
        } catch (\Exception $e) {}
        return back()->with('success', 'Sessão encerrada.');
    }

    public function destroyAllSessions(Request $request)
    {
        try {
            DB::table('sessions')
                ->where('user_id', Auth::id())
                ->where('id', '!=', session()->getId())
                ->delete();
        } catch (\Exception $e) {}
        return back()->with('success', 'Todas as outras sessões foram encerradas.');
    }
}
