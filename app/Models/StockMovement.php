<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'reference_id',
        'reference_no',
        'previous_stock',
        'quantity_change',
        'new_stock',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'previous_stock' => 'integer',
            'quantity_change' => 'integer',
            'new_stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
