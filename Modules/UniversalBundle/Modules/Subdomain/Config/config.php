<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Subdomain',
    'verification_required' => true,
    'envato_item_id' => 26384704,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf.'-subdomain-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Subdomain\Entities\SubdomainSetting::class,
];

