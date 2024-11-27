<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySmtpSetting extends Model
{
    use HasFactory;

    protected $table = 'company_smtp_settings';
    protected $guarded = ['id'];

    protected $fillable = [
        'company_id',
        'sender_name',
        'sender_email',
        'smtp_username',
        'smtp_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'imap_host',
        'refresh_token',
        'added_type',
        'created_at',
        'updated_at','module_id'
    ];
}
