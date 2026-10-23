<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worksheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_lembar_kerja',
        'tanggal',
        'nomor_smu',
        'koli_smu',
        'berat_smu',
        'origin',
        'tipe',
        'total_koli',
        'total_berat_kg',
        'total_biaya',
        'keterangan',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'koli_smu' => 'integer',
        'berat_smu' => 'decimal:2',
        'total_koli' => 'integer',
        'total_berat_kg' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(WorksheetItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function iasPayments()
    {
        return $this->hasMany(IasPayment::class, 'nomor_smu', 'nomor_smu');
    }

    public function getDisplayKoliSmuAttribute(): int
    {
        return $this->koli_smu ?: (int) $this->total_koli;
    }

    public function getDisplayBeratSmuAttribute(): float
    {
        return (float) ($this->berat_smu ?: $this->total_berat_kg);
    }

    public function recalculateTotals(): void
    {
        $this->total_koli = (int) $this->items()->sum('jumlah_koli');
        $this->total_berat_kg = (float) $this->items()->sum('berat_kg');
        $this->total_biaya = (float) $this->items()->sum('total_biaya');

        if (!$this->koli_smu) {
            $this->koli_smu = $this->total_koli;
        }
        if (!$this->berat_smu) {
            $this->berat_smu = $this->total_berat_kg;
        }

        // Check if all items are dinotakan
        $totalItems = $this->items()->count();
        $dinotakanCount = $this->items()->where('status', 'dinotakan')->count();

        if ($totalItems > 0 && $totalItems === $dinotakanCount) {
            $this->status = 'dinotakan';
        }

        $this->save();
    }

    public static function generateNumber(): string
    {
        $prefix = 'LK-' . date('Ym') . '-';
        $last = self::where('nomor_lembar_kerja', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->nomor_lembar_kerja, -4);
            $next = $lastNumber + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
