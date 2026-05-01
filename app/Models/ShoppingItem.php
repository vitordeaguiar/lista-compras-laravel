<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShoppingItem extends Model
{
    use HasFactory;

    protected $fillable = ['shopping_list_id', 'name', 'unit', 'qty', 'price', 'purchased'];

    protected $casts = [
        'purchased' => 'boolean',
        'qty'       => 'float',
        'price'     => 'float',
    ];

    public function shoppingList() { return $this->belongsTo(ShoppingList::class); }

    public function getSubtotalAttribute(): ?float
    {
        if ($this->price === null) return null;
        return round($this->qty * $this->price, 2);
    }
}
