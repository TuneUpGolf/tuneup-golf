<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $table = 'expenses';
    protected $fillable = ['expenses_type_id','tenant_id','amount','notes'];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class,'expenses_type_id','id');
    }
}