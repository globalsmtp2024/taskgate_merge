<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Letter',
    'verification_required' => true,
    'envato_item_id' => 50767378,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.3.61',
    'script_name' => $addOnOf.'-letter-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Letter\Entities\LetterSetting::class,
];
