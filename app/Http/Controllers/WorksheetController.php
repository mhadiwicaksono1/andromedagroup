<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorksheetController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $customerId = $request->query('customer_id');

        $query = Worksheet::with(['items.customer', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lembar_kerja', 'like', "%{$search}%")
                  ->orWhere('nomor_smu', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('items.customer', function ($c) use ($search) {
                      $c->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['draft', 'selesai', 'dinotakan'])) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->whereHas('items', function ($i) use ($customerId) {
                $i->where('customer_id', $customerId);
            });
        }

        $worksheets = $query->latest('tanggal')->latest('id')->paginate(10)->withQueryString();
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();

        return view('worksheets.index', compact('worksheets', 'customers', 'search', 'status', 'customerId'));
    }

    public function create()
    {
        $nextNumber = Worksheet::generateNumber();
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();
        $today = now()->toDateString();

        return view('worksheets.create', compact('nextNumber', 'customers', 'today'));
    }

    public function store(Request $request)
    {
        $tipe = $request->input('tipe', 'pecahan');
        $itemRules = $tipe === 'pernama' ? 'required|array|size:1' : 'required|array|min:1';

        $validated = $request->validate([
            'nomor_lembar_kerja' => 'required|string|max:50|unique:worksheets,nomor_lembar_kerja',
            'tanggal' => 'required|date',
            'nomor_smu' => 'required|string|max:50',
            'koli_smu' => 'nullable|integer|min:1',
            'berat_smu' => 'nullable|numeric|min:0.01',
            'origin' => 'nullable|string|max:20',
            'tipe' => 'required|in:pecahan,pernama',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,selesai',
            'items' => $itemRules,
            'items.*.customer_id' => 'required|exists:customers,id',
            'items.*.marking' => 'nullable|string|max:100',
            'items.*.origin' => 'nullable|string|max:20',
            'items.*.jumlah_koli' => 'required|integer|min:1',
            'items.*.berat_kg' => 'required|numeric|min:0.01',
            'items.*.tarif_per_kg' => 'required|numeric|min:0',
            'items.*.keterangan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $worksheet = Worksheet::create([
                'nomor_lembar_kerja' => $validated['nomor_lembar_kerja'],
                'tanggal' => $validated['tanggal'],
                'nomor_smu' => $validated['nomor_smu'],
                'koli_smu' => $validated['koli_smu'] ?? null,
                'berat_smu' => $validated['berat_smu'] ?? null,
                'origin' => $validated['origin'] ?? 'lop',
                'tipe' => $validated['tipe'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                // Pembulatan ke atas ke ribuan terdekat sesuai standar nota kargo
                $rawBiaya = $itemData['berat_kg'] * $itemData['tarif_per_kg'];
                $totalBiaya = (float) (ceil($rawBiaya / 1000) * 1000);

                WorksheetItem::create([
                    'worksheet_id' => $worksheet->id,
                    'customer_id' => $itemData['customer_id'],
                    'marking' => $itemData['marking'] ?? null,
                    'origin' => $itemData['origin'] ?? $validated['origin'] ?? 'lop',
                    'jumlah_koli' => $itemData['jumlah_koli'],
                    'berat_kg' => $itemData['berat_kg'],
                    'tarif_per_kg' => $itemData['tarif_per_kg'],
                    'total_biaya' => $totalBiaya,
                    'keterangan' => $itemData['keterangan'] ?? null,
                    'status' => $validated['status'],
                ]);
            }

            $worksheet->recalculateTotals();

            return redirect()->route('worksheets.show', $worksheet)
                ->with('success', 'Lembar kerja ' . $worksheet->nomor_lembar_kerja . ' (' . strtoupper($validated['tipe']) . ') berhasil disimpan!');
        });
    }

    public function show(Worksheet $worksheet)
    {
        $worksheet->load(['items.customer', 'items.invoice', 'user']);
        return view('worksheets.show', compact('worksheet'));
    }

    public function edit(Worksheet $worksheet)
    {
        if ($worksheet->status === 'dinotakan') {
            return redirect()->route('worksheets.show', $worksheet)
                ->with('error', 'Lembar kerja yang sudah dinotakan tidak dapat diedit.');
        }

        $worksheet->load('items.customer');
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();

        return view('worksheets.edit', compact('worksheet', 'customers'));
    }

    public function update(Request $request, Worksheet $worksheet)
    {
        if ($worksheet->status === 'dinotakan') {
            return redirect()->route('worksheets.show', $worksheet)
                ->with('error', 'Lembar kerja yang sudah dinotakan tidak dapat diedit.');
        }

        $tipe = $request->input('tipe', $worksheet->tipe ?? 'pecahan');
        $itemRules = $tipe === 'pernama' ? 'required|array|size:1' : 'required|array|min:1';

        $validated = $request->validate([
            'nomor_lembar_kerja' => 'required|string|max:50|unique:worksheets,nomor_lembar_kerja,' . $worksheet->id,
            'tanggal' => 'required|date',
            'nomor_smu' => 'required|string|max:50',
            'koli_smu' => 'nullable|integer|min:1',
            'berat_smu' => 'nullable|numeric|min:0.01',
            'origin' => 'nullable|string|max:20',
            'tipe' => 'required|in:pecahan,pernama',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,selesai',
            'items' => $itemRules,
            'items.*.customer_id' => 'required|exists:customers,id',
            'items.*.marking' => 'nullable|string|max:100',
            'items.*.origin' => 'nullable|string|max:20',
            'items.*.jumlah_koli' => 'required|integer|min:1',
            'items.*.berat_kg' => 'required|numeric|min:0.01',
            'items.*.tarif_per_kg' => 'required|numeric|min:0',
            'items.*.keterangan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $worksheet) {
            $worksheet->update([
                'nomor_lembar_kerja' => $validated['nomor_lembar_kerja'],
                'tanggal' => $validated['tanggal'],
                'nomor_smu' => $validated['nomor_smu'],
                'koli_smu' => $validated['koli_smu'] ?? null,
                'berat_smu' => $validated['berat_smu'] ?? null,
                'origin' => $validated['origin'] ?? 'lop',
                'tipe' => $validated['tipe'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
            ]);

            // Recreate items
            $worksheet->items()->forceDelete();

            foreach ($validated['items'] as $itemData) {
                // Pembulatan ke atas ke ribuan terdekat sesuai standar nota kargo
                $rawBiaya = $itemData['berat_kg'] * $itemData['tarif_per_kg'];
                $totalBiaya = (float) (ceil($rawBiaya / 1000) * 1000);

                WorksheetItem::create([
                    'worksheet_id' => $worksheet->id,
                    'customer_id' => $itemData['customer_id'],
                    'marking' => $itemData['marking'] ?? null,
                    'origin' => $itemData['origin'] ?? $validated['origin'] ?? 'lop',
                    'jumlah_koli' => $itemData['jumlah_koli'],
                    'berat_kg' => $itemData['berat_kg'],
                    'tarif_per_kg' => $itemData['tarif_per_kg'],
                    'total_biaya' => $totalBiaya,
                    'keterangan' => $itemData['keterangan'] ?? null,
                    'status' => $validated['status'],
                ]);
            }

            $worksheet->recalculateTotals();

            return redirect()->route('worksheets.show', $worksheet)
                ->with('success', 'Lembar kerja berhasil diperbarui!');
        });
    }

    public function updateStatus(Request $request, Worksheet $worksheet)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,selesai',
        ]);

        if ($worksheet->status === 'dinotakan') {
            return redirect()->back()->with('error', 'Lembar kerja sudah dinotakan dan tidak dapat diubah statusnya.');
        }

        $worksheet->update(['status' => $validated['status']]);
        $worksheet->items()->where('status', '!=', 'dinotakan')->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Status lembar kerja diubah menjadi " . strtoupper($validated['status']));
    }

    public function destroy(Worksheet $worksheet)
    {
        // Prevent delete if any item is already invoiced
        if ($worksheet->items()->whereNotNull('invoice_id')->exists() || $worksheet->status === 'dinotakan') {
            return redirect()->back()->with('error', 'Lembar kerja ini tidak dapat dihapus karena sebagian atau seluruh muatannya sudah diterbitkan ke Nota Penagihan.');
        }

        $nomor = $worksheet->nomor_lembar_kerja;

        DB::transaction(function () use ($worksheet) {
            $worksheet->items()->delete(); // Soft delete child items
            $worksheet->delete(); // Soft delete worksheet
        });

        return redirect()->route('worksheets.index')->with('success', "Lembar kerja {$nomor} berhasil dihapus (Soft Delete).");
    }
}
