<?php

$addOnOf = 'worksuite-saas-new';
$product = $addOnOf . '-biolinks-module';

return [
    'name' => 'Biolinks',
    'verification_required' => true,
    'envato_item_id' => 52393825,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.4.11',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Biolinks\Entities\BiolinksGlobalSetting::class,
];
