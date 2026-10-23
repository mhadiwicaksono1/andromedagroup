<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'jumlah_alokasi',
    ];

    protected $casts = [
        'jumlah_alokasi' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
