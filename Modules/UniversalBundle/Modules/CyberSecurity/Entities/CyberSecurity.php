<?php

namespace Modules\CyberSecurity\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CyberSecurity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
