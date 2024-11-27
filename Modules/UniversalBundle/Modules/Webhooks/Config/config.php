<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Webhooks',
    'verification_required' => true,
    'envato_item_id' => 49460682,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.3.54',
    'script_name' => $addOnOf . '-webhooks-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Webhooks\Entities\WebhooksGlobalSetting::class,
];
