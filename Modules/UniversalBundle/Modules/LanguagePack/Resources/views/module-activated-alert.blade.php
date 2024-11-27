@if(session('languagepack_module_activated') == 'activated')
@php
    $moduleLink = '<a href="'.route('language-settings.index').'">'.__('app.languageSetting').'</a>';
@endphp
<x-alert type="primary">
    <span class="mb-12"><strong>Note:</strong></span>
    <span>@lang('languagepack::messages.moduleActivatedNote', ['link' => $moduleLink])</span>
</x-alert>
@endif
