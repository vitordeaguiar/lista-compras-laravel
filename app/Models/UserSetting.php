<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id', 'theme', 'accent_color', 'salary_day',
        'monthly_budget', 'monthly_savings_goal', 'notify_due_days',
        'notify_budget_alert', 'notify_monthly_summary', 'notify_list_reminder',
        'notify_new_month', 'notify_email', 'notify_push',
        'auto_copy_fixed', 'auto_copy_incomes', 'auto_keep_investments',
        'layout_density',
    ];

    protected $casts = [
        'notify_budget_alert'    => 'boolean',
        'notify_monthly_summary' => 'boolean',
        'notify_list_reminder'   => 'boolean',
        'notify_new_month'       => 'boolean',
        'notify_email'           => 'boolean',
        'notify_push'            => 'boolean',
        'auto_copy_fixed'        => 'boolean',
        'auto_copy_incomes'      => 'boolean',
        'auto_keep_investments'  => 'boolean',
        'monthly_budget'         => 'decimal:2',
        'monthly_savings_goal'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
