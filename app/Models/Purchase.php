<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'bar_code_id',
        'category_id',
        'supplier_id',
        'invoice_no',
        'date',
        'quantity',
        'cost_price',
        'expiry_date', // Ensure this field is fillable
        'near_expiry_date', // Ensure this field is fillable
        'original_quantity',
        'details',
        'image',
        'pill_amount',
        'original_pill_amount',
        'leftover_pills',
    ];

    protected $casts = [
        'quantity' => 'integer', // Add this line to cast quantity as an integer
        'pill_amount' => 'integer',
        'original_pill_amount' => 'integer',
        'near_expiry_date' => 'date', // Cast near_expiry_date as a date
    ];

    // Define the relationship with BarCodeData
    public function barCodeData()
    {
        return $this->belongsTo(BarCodeData::class, 'bar_code_id');
    }
    
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'purchase_sale')->withPivot('quantity');
    }

    public function boxInventories()
    {
        return $this->hasMany(BoxInventory::class, 'purchase_id');
    }

    public function getTotalPillAmountAttribute()
    {
        if ($this->pill_amount !== null) {
            return $this->boxInventories()->sum('remaining_pills');
        }
        return null;
    }

    public function getLeftoverPillsAttribute($value)
    {
        // Return the stored DB value with no fallback
        return $value;
    }

    public function isExpired()
    {
        return $this->expiry_date && Carbon::parse($this->expiry_date)->lt(Carbon::today());
    }
}
