<?php

namespace Modules\CyberSecurity\Entities;

use Illuminate\Database\Eloquent\Model;

class CyberSecuritySetting extends Model
{
    protected $guarded = ['id'];

    const MODULE_NAME = 'cybersecurity';

}
