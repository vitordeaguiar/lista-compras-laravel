<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialMonth extends Model
{
    protected $fillable = ['user_id', 'month'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
