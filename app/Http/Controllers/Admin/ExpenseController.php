<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTables\Admin\ExpenseDataTable;


class ExpenseController extends Controller
{
    public function index(ExpenseDataTable $dataTable)
    {
        // if (Auth::user()->can('manage-user')) {
        $expense_types = ExpenseType::where('tenant_id', tenant()->id)->get();
        return $dataTable->render('admin.expense.index', compact('expense_types'));
        // } else {
        //     return redirect()->back()->with('failed', __('Permission denied.'));
        // }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'expense_type' => 'required|exists:expenses_type,id',
            'amount'          => 'required|string|max:255',
            'notes'          => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $expense = new Expense();
            $expense->expenses_type_id = $validatedData['expense_type'];
            $expense->amount = $validatedData['amount'];
            $expense->notes = $validatedData['notes'];
            $expense->tenant_id = tenant()->id;
            $expense->save();
            DB::commit();
            return redirect()->back()->with('success', 'Expense created successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id'            => 'required|exists:expenses,id',
            'expense_type' => 'required|exists:expenses_type,id',
            'amount'          => 'required|string|max:255',
            'notes'          => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $expense = Expense::find($request->input('id'));
            $expense->expenses_type_id = $validatedData['expense_type'];
            $expense->amount = $validatedData['amount'];
            $expense->notes = $validatedData['notes'];
            $expense->tenant_id = tenant()->id;
            $expense->tenant_id = tenant()->id;
            $expense->save();
            DB::commit();
            return redirect()->back()->with('success', 'Expense updated successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        $expense = Expense::find($id);
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully.');
    }
}