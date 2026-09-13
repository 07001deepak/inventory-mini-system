<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'price_per_unit',
        'tax_percentage',
        'stock_on_hand',
        'low_stock_threshold',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'stock_on_hand' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeLowStock($query, ?int $threshold = null)
    {
        if ($threshold !== null) {
            return $query->where('stock_on_hand', '<=', $threshold);
        }
        return $query->whereColumn('stock_on_hand', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock_on_hand <= $this->low_stock_threshold;
    }
}
