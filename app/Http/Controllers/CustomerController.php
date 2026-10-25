<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WorksheetItem;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Customer::withCount(['worksheetItems', 'invoices'])
            ->withSum(['invoices as total_sisa_piutang' => function ($q) {
                $q->where('status_pembayaran', '!=', 'lunas');
            }], 'sisa_piutang');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_customer', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }

        $customers = $query->latest('id')->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'search', 'status'));
    }

    public function create()
    {
        $nextCode = Customer::generateCode();
        return view('customers.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('kode_customer')) {
            $request->merge(['kode_customer' => Customer::generateCode()]);
        }

        $validated = $request->validate([
            'kode_customer' => 'required|string|max:50|unique:customers,kode_customer',
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'kategori' => 'required|in:standar,prioritas',
            'tarif_default' => 'nullable|numeric|min:0',
            'tarif_pecahan' => 'nullable|numeric|min:0',
            'tarif_pernama' => 'nullable|numeric|min:0',
        ]);

        if ($validated['kategori'] === 'prioritas') {
            $validated['tarif_pecahan'] = $validated['tarif_pecahan'] ?? 2000;
            $validated['tarif_pernama'] = $validated['tarif_pernama'] ?? 1980;
            $validated['tarif_default'] = $validated['tarif_pecahan'];
        } else {
            $validated['tarif_default'] = $validated['tarif_default'] ?? 2100;
            $validated['tarif_pecahan'] = $validated['tarif_default'];
            $validated['tarif_pernama'] = $validated['tarif_default'];
        }

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer "' . $customer->nama . '" berhasil ditambahkan!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['worksheetItems' => function ($q) {
            $q->with('worksheet')->latest('id')->take(20);
        }, 'invoices' => function ($q) {
            $q->latest('tanggal')->take(20);
        }, 'payments' => function ($q) {
            $q->latest('tanggal')->take(20);
        }]);

        $totalSisaHutang = $customer->sisa_hutang;
        $kartuPiutang = $customer->kartu_piutang;

        return view('customers.show', compact('customer', 'totalSisaHutang', 'kartuPiutang'));
    }

    public function statement(Customer $customer)
    {
        $kartuPiutang = $customer->kartu_piutang;
        $totalSisaHutang = $customer->sisa_hutang;

        return view('customers.statement', compact('customer', 'kartuPiutang', 'totalSisaHutang'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'kode_customer' => 'required|string|max:50|unique:customers,kode_customer,' . $customer->id,
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'kategori' => 'required|in:standar,prioritas',
            'tarif_default' => 'nullable|numeric|min:0',
            'tarif_pecahan' => 'nullable|numeric|min:0',
            'tarif_pernama' => 'nullable|numeric|min:0',
        ]);

        if ($validated['kategori'] === 'prioritas') {
            $validated['tarif_pecahan'] = $validated['tarif_pecahan'] ?? 2000;
            $validated['tarif_pernama'] = $validated['tarif_pernama'] ?? 1980;
            $validated['tarif_default'] = $validated['tarif_pecahan'];
        } else {
            $validated['tarif_default'] = $validated['tarif_default'] ?? 2100;
            $validated['tarif_pecahan'] = $validated['tarif_default'];
            $validated['tarif_pernama'] = $validated['tarif_default'];
        }

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Data customer berhasil diperbarui!');
    }

    public function toggleStatus(Customer $customer)
    {
        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Customer {$customer->nama} berhasil {$label}.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->where('sisa_piutang', '>', 0)->exists()) {
            return redirect()->back()->with('error', "Customer {$customer->nama} tidak dapat dihapus karena masih memiliki saldo piutang berjalan yang belum lunas.");
        }

        $nama = $customer->nama;
        $customer->delete();

        return redirect()->route('customers.index')->with('success', "Customer {$nama} berhasil dihapus (Soft Delete).");
    }

    /**
     * API: Get customer pending unbilled worksheet items & unpaid debt balance
     * Used in Tahap 4 for dynamic Invoice creation & Payment allocation via JavaScript
     */
    public function apiDebt(Customer $customer)
    {
        $sisaHutang = $customer->sisa_hutang;

        $unbilledItems = WorksheetItem::with('worksheet')
            ->where('customer_id', $customer->id)
            ->where('status', 'selesai')
            ->whereNull('invoice_id')
            ->get();

        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
            ->where('status_pembayaran', '!=', 'lunas')
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'customer' => $customer,
            'sisa_hutang_sebelumnya' => $sisaHutang,
            'unbilled_worksheets' => $unbilledItems,
            'unpaid_invoices' => $unpaidInvoices,
        ]);
    }
}
