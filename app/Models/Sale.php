<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id', 
        'bar_code_id', 
        'quantity', 
        'total_price',
        'discount_type',
        'discount_value',
        'payment_method',
        'pay_amount_dollar',
        'pay_amount_riel',
        'due_amount_dollar',
        'due_amount_riel',
        'cashback_dollar',
        'cashback_riel',
        'sale_by', // Add this line
        'invoice_id', // Add this line
    ];

    // If using the default primary key 'id', no need to specify
    // If using a different primary key, uncomment and set it
    // protected $primaryKey = 'sale_id';

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function barCodeData()
    {
        return $this->belongsTo(BarCodeData::class, 'bar_code_id');
    }

    public function purchaseSale()
    {
        return $this->hasOne(PurchaseSale::class, 'sale_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
