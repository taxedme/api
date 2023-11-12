<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['organization_id', 'names', 'salary', 'months'];
    
}
