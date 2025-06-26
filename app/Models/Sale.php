<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;

    public function totalPrice()
    {
        return collect($this->items)
            ->reduce(fn($total, $item) => $total + $item->subTotal(), 0);
    }
    public function getHasDiscountAttribute(): string
    {
        // Check if any sale item has discount_id not null and not 1
        $hasDiscount = $this->items()->whereNotNull('discount_id')
            ->where('discount_id', '!=', 1)
            ->exists();

<<<<<<< HEAD
        return $hasDiscount ? 'Yes' : 'No';
    }

    public function totalPay()
    {
        return $this->items->sum(function ($item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;

            if ($item->discount_id && $item->discount) {
                $discountModel = $item->discount;
                $discount = $discountModel->value;
                $isPercent = $discountModel->ispercent;

                $discountAmount = $isPercent
                    ? ($unitPrice * $discount / 100)
                    : $discount;
            }

            return ($unitPrice - $discountAmount) * $qty;
        });
    }
    // In Sale.php model
    public function getDiscountAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;

            if ($item->discount_id && $item->discount) {
                $discountModel = $item->discount;
                $discount = $discountModel->value;
                $isPercent = $discountModel->ispercent;

                $discountAmount = $isPercent
                    ? ($unitPrice * $discount / 100) * $qty
                    : $discount * $qty;
            }

            return $discountAmount;
        });
    }

    public function totalItemQty()
=======
    protected function totalQty(): Attribute
>>>>>>> fc9abc19883a4d43d2d6d4d549e388cbf79819c1
    {
        return Attribute::make(
            get: fn() => $this->items->sum('qty')
        );
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
