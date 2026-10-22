<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_no',
        'product_id',
        'type',
        'previous_quantity',
        'quantity_change',
        'new_quantity',
        'reason',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'previous_quantity' => 'integer',
            'quantity_change' => 'integer',
            'new_quantity' => 'integer',
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

    public static function getReasons(): array
    {
        return [
            'damaged' => 'Damaged Goods',
            'lost' => 'Lost / Stolen Stock',
            'opening_stock' => 'Opening Stock Setup',
            'manual_correction' => 'Manual Stock Correction',
            'expired' => 'Expired Product',
            'recount' => 'Physical Inventory Recount',
            'returned_to_vendor' => 'Returned to Supplier',
            'other' => 'Other Reason',
        ];
    }
}
