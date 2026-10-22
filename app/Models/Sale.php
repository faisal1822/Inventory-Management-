<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'customer_name',
        'customer_phone',
        'total_quantity',
        'subtotal',
        'discount',
        'grand_total',
        'paid_amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'total_quantity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getProfitAttribute(): float
    {
        // Profit = Sum of (item selling line total - item cost line total) - discount
        $totalCost = $this->items->sum(function ($item) {
            return $item->unit_cost * $item->quantity;
        });

        return max(0, (float) ($this->grand_total - $totalCost));
    }
}
