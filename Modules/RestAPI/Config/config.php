<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'RestAPI',
    'verification_required' => true,
    'envato_item_id' => 26204850,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.2.9',
    'script_name' => $addOnOf.'-restapi-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\RestAPI\Entities\RestAPISetting::class,
    'jwt_secret' => '2dSW430D2ZfLwO1TjO03Q25S7mII5StAgvdCcxU8GMqgykjS1d3i2r2bLT5bvIFT',
    'debug' => config('app.api_debug', false),
];
