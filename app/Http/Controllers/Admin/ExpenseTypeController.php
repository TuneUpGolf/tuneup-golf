<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTables\Admin\ExpenseTypeDataTable;

class ExpenseTypeController extends Controller
{
    public function index(ExpenseTypeDataTable $dataTable)
    {
        // if (Auth::user()->can('manage-user')) {
            return $dataTable->render('admin.expense_type.index');
        // } else {
        //     return redirect()->back()->with('failed', __('Permission denied.'));
        // }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type'          => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $expense_type = new ExpenseType();
            $expense_type->type = $validatedData['type'];
            $expense_type->tenant_id = tenant()->id;
            $expense_type->save();
            DB::commit();
            return redirect()->back()->with('success', 'Expense Type created successfully.');


        }
        catch(Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id'            => 'required|exists:expenses_type,id',
            'type'          => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $expense_type = ExpenseType::find($request->input('id'));
            $expense_type->type = $validatedData['type'];
            $expense_type->tenant_id = tenant()->id;
            $expense_type->save();
            DB::commit();
            return redirect()->back()->with('success', 'Expense Type updated successfully.');
        }
        catch(Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        $expense_type = ExpenseType::find($id);
        $expense_type->delete();
        return redirect()->back()->with('success', 'Expense Type deleted successfully.');
    }

}