<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_pembayaran',
        'tanggal',
        'invoice_id',
        'customer_id',
        'jumlah',
        'metode',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'payment_allocations')
            ->withPivot('jumlah_alokasi')
            ->withTimestamps();
    }

    public static function generateNumber(): string
    {
        $prefix = 'PAY/' . date('Ym') . '/';
        $last = self::withTrashed()
            ->where('nomor_pembayaran', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->nomor_pembayaran, -4);
            $next = $lastNumber + 1;
        } else {
            $next = 1;
        }

        $generated = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
        while (self::withTrashed()->where('nomor_pembayaran', $generated)->exists()) {
            $next++;
            $generated = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        return $generated;
    }
}
