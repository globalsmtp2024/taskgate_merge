<style>
    .domain-type>.dropdown-toggle.bs-placeholder {
        color: unset !important;
    }
</style>
<div class="col-12">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <x-forms.label class="mt-3" fieldId="type-subdomain" fieldRequired="true" :fieldLabel="__('subdomain::app.core.domainType')">
                </x-forms.label>
                <select class="form-control select-picker domain-type" name="domain">
                    <option @selected(str($company->sub_domain)->endsWith(getDomain())) value="{{ '.'. getDomain() }}">{{ __('subdomain::app.core.subdomain') }}</option>
                    <option @selected(!str($company->sub_domain)->endsWith(getDomain())) value="">@lang('subdomain::app.core.customDomain')</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <x-forms.label class="mt-3" fieldId="sub_domain"
                           fieldRequired="true"
                           :fieldLabel="__('subdomain::app.core.domain')">
            </x-forms.label>
            <x-forms.input-group>
                <input type="text" name="sub_domain" id="sub_domain"
                       value="{{str_replace('.'.getDomain(),'',$company->sub_domain)}}"
                       placeholder="@lang('subdomain::app.core.domain')" class="form-control height-35 f-14"/>
                <x-slot name="preappend">
                    <label class="input-group-text f-14 bg-white-shade text-bold">.{{ getDomain() }}</label>
                </x-slot>
            </x-forms.input-group>
        </div>
    </div>
</div>
