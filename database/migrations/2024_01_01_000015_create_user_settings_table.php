<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('theme', 10)->default('dark');
            $table->string('accent_color', 20)->default('#2dd4bf');
            $table->tinyInteger('salary_day')->unsigned()->default(5);
            $table->decimal('monthly_budget', 12, 2)->default(0);
            $table->decimal('monthly_savings_goal', 12, 2)->default(0);
            $table->tinyInteger('notify_due_days')->unsigned()->default(3);
            $table->boolean('notify_budget_alert')->default(true);
            $table->boolean('notify_monthly_summary')->default(true);
            $table->boolean('notify_list_reminder')->default(true);
            $table->boolean('notify_new_month')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_push')->default(false);
            $table->boolean('auto_copy_fixed')->default(true);
            $table->boolean('auto_copy_incomes')->default(true);
            $table->boolean('auto_keep_investments')->default(true);
            $table->string('layout_density', 20)->default('comfortable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
