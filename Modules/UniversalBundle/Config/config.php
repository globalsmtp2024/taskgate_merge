<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'UniversalBundle',
    'verification_required' => true,
    'envato_item_id' => 48913734,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.3.4',
    'script_name' => $addOnOf.'-universalbundle-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\UniversalBundle\Entities\UniversalBundleSetting::class,
];
