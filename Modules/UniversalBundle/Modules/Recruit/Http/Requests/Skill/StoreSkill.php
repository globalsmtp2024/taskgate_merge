<?php

namespace Modules\Recruit\Http\Requests\Skill;

use App\Http\Requests\CoreRequest;

class StoreSkill extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'names.*' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'names.*.required' => __('recruit::modules.skill.addSkills'),
        ];
    }
}
