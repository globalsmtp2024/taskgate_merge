<?php

namespace Modules\Subdomain\Entities;

use App\Models\BaseModel;

class SubdomainSetting extends BaseModel
{

    protected $table = 'sub_domain_module_settings';

    protected $default = ['id'];

    public function setBannedSubdomainAttribute($value)
    {
        if (isset($value)) {
            $this->attributes['banned_subdomain'] = json_encode($value);
        }
        else {
            $this->attributes['banned_subdomain'] = null;
        }
    }

    public function getBannedSubdomainAttribute()
    {
        if (isset($this->attributes['banned_subdomain']) && $this->attributes['banned_subdomain'] !== null) {
            return json_decode($this->attributes['banned_subdomain'], true);
        }

        return null;
    }

    public static function addDefaultSubdomain($company)
    {
        $companyName = array_first(explode(' ', strtolower($company->app_name)));
        $companyName = str_replace(',', '', $companyName);
        $serverName = getDomain();
        $company->sub_domain = $companyName . '.' . $serverName;
        $company->saveQuietly();
    }

}
