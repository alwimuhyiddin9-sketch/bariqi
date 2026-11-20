<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();
        if ($s = $request->search) $query->where('name', 'like', "%{$s}%");
        if ($c = $request->category) $query->where('category', $c);
        if ($f = $request->from) $query->where('date', '>=', $f);
        if ($t = $request->to) $query->where('date', '<=', $t . ' 23:59:59');
        $expenses = $query->orderByDesc('date')->get();
        $total = $expenses->sum('amount');
        $byCategory = $expenses->groupBy('category')->map(fn ($r) => $r->sum('amount'))->toArray();
        return view('expenses.index', compact('expenses', 'total', 'byCategory'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'category' => 'required',
            'amount' => 'required|integer|min:1',
            'note' => 'nullable',
            'date' => 'nullable|date',
        ]);
        Expense::create([
            'name' => $data['name'], 'category' => $data['category'], 'amount' => $data['amount'],
            'note' => $data['note'] ?? null, 'date' => $data['date'] ?? now(),
        ]);
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran dicatat');
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'name' => 'required', 'category' => 'required', 'amount' => 'required|integer|min:1',
            'note' => 'nullable', 'date' => 'nullable|date',
        ]);
        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran diperbarui');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran dihapus');
    }
}
