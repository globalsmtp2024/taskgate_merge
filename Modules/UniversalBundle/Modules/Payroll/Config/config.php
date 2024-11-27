<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Payroll',
    'verification_required' => true,
    'envato_item_id' => 26202694,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.2.3',
    'script_name' => $addOnOf.'-payroll-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Payroll\Entities\PayrollGlobalSetting::class,
];
