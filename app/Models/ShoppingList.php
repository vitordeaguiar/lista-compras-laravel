<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShoppingList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'shopping_date', 'status', 'total', 'discount', 'notes', 'completed_at'];

    protected $casts = [
        'shopping_date' => 'date',
        'completed_at'  => 'datetime',
        'total'         => 'float',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(ShoppingItem::class); }

    public function getComputedTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->subtotal ?? 0);
    }

    public function isOpen(): bool      { return $this->status === 'open'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
}
