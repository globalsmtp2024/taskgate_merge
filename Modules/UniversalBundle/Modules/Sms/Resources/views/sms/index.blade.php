@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">


        @if (user()->is_superadmin)
            <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
        @else
            <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
        @endif


        <x-setting-card>
            <x-slot name="alert">
                <div class="row">
                    <div class="col-md-12">
                        <x-alert type="info" icon="info-circle">
                            @lang('sms::modules.gatewayLimitation')
                        </x-alert>
                    </div>
                    <div class="col-md-12">
                        <x-alert type="info" icon="info-circle">
                            @lang('sms::modules.mobileNumberFormat')
                        </x-alert>
                    </div>
                </div>
            </x-slot>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-xl-8 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 py-4 ">
                @if (user()->is_superadmin)
                <input type="hidden"
                       @if ($smsSetting->whatsapp_status || $smsSetting->status)
                       value="twilio"
                       @elseif ($smsSetting->nexmo_status)
                       value="nexmo"
                       @elseif ($smsSetting->msg91_status)
                       value="msg91"
                       @elseif ($smsSetting->telegram_status)
                       value="telegram"
                       @endif
                       name="active_gateway" id="active_gateway">
                <div class="row">
                    <div class="col-md-10">
                        <h4 class="mb-4 f-21 font-weight-normal text-capitalize">
                            <img src="{{ asset('img/twilio-logo-red.6b0811b1f.svg') }}" width="100" alt="">
                        </h4>

                    </div>

                    <div class="col-lg-2 mb-2">
                        <div class="form-group text-right">
                            <div class="d-flex mt-2 justify-content-end">
                                <x-forms.checkbox fieldLabel=" " class="sms-gateway-status" fieldName="twilio-gateway"
                                                  fieldId="twilio-gateway" fieldValue="twilio"
                                                  :checked="$smsSetting->whatsapp_status || $smsSetting->status"/>
                            </div>
                        </div>
                    </div>

                    <div id="twilio-form"
                         class="col-lg-12 @if (!$smsSetting->whatsapp_status && !$smsSetting->status) d-none @endif">
                        <div class="row">
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="account_sid" fieldLabel="Account SID"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="account_sid" id="account_sid"
                                           class="form-control height-35 f-14" value="{{ $smsSetting->account_sid }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="auth_token" fieldLabel="Auth Token"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="auth_token" id="auth_token"
                                           class="form-control height-35 f-14" value="{{ $smsSetting->auth_token }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-12">
                                <x-forms.tel fieldId="from_number" :fieldLabel="'SMS ' . __('sms::app.fromNumber')"
                                             fieldName="from_number"
                                             fieldPlaceholder="e.g. 987654321"
                                             fieldRequired="true"
                                             :fieldValue="$smsSetting->from_number"></x-forms.tel>
                            </div>
                            <div class="col-6">
                                <div class="form-group my-3">
                                    <label class="f-14 text-dark-grey mb-12 w-100"
                                           for="whatsapp_status1"><img src="{{ asset("img/whatsapp.svg") }}" width="20"
                                                                       alt=""> WhatsApp</label>
                                    <div class="d-flex">
                                        <x-forms.radio fieldId="whatsapp_status1" :fieldLabel="__('app.enable')"
                                                       fieldName="whatsapp_status"
                                                       fieldValue="1"
                                                       :checked="$smsSetting->whatsapp_status == 1 || is_null($smsSetting->whatsapp_status)">
                                        </x-forms.radio>
                                        <x-forms.radio fieldId="whatsapp_status2" :fieldLabel="__('app.disable')"
                                                       fieldValue="0"
                                                       fieldName="whatsapp_status"
                                                       :checked="$smsSetting->whatsapp_status == 0"></x-forms.radio>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <x-forms.tel fieldId="whatapp_from_number"
                                             :fieldLabel="'WhatsApp ' . __('sms::app.fromNumber')"
                                             fieldName="whatapp_from_number"
                                             fieldPlaceholder="e.g. 987654321"
                                             fieldRequired="true"
                                             :fieldValue="$smsSetting->whatapp_from_number"></x-forms.tel>
                            </div>
                        </div>
                        <div class="row pb-3 @if (!$smsSetting->whatsapp_status) d-none @endif"
                             id="whatsappTemplates">
                            @foreach ($smsSettings as $setting)
                                <div
                                    class="col-md-6 pt-3 whatsappTemplate{{$setting->id}} @if ($setting->send_sms == 'no') d-none @endif">
                                    <x-forms.label :fieldId="'whatsapp_'.$setting->id"
                                                   :fieldLabel="$setting->slug->label()"></x-forms.label>
                                    <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                       data-clipboard-target="#whatsapp_template{{$setting->id}}"> <i
                                            class="fa fa-copy mx-1"></i></a>
                                    <textarea class="form-control f-14 pt-2" readonly rows="4"
                                              id="whatsapp_template{{$setting->id}}">{{ $setting->slug->whatsappTemplate() }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10">
                        <h4 class="mb-4 f-21 font-weight-normal text-capitalize">
                            <img src="{{ asset('img/vonage-nexmo.png') }}" width="100" alt="">
                        </h4>
                    </div>

                    <div class="col-lg-2 mb-2">
                        <div class="form-group text-right">
                            <div class="d-flex mt-2 justify-content-end">
                                <x-forms.checkbox fieldLabel=" " class="sms-gateway-status" fieldName="nexmo-gateway"
                                                  fieldId="nexmo-gateway" fieldValue="nexmo"
                                                  :checked="$smsSetting->nexmo_status"/>
                            </div>
                        </div>
                    </div>

                    <div id="nexmo-form" class="col-lg-12 @if (!$smsSetting->nexmo_status) d-none @endif">
                        <div class="row">
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="nexmo_api_key" fieldLabel="API Key"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="nexmo_api_key" id="nexmo_api_key"
                                           class="form-control height-35 f-14" value="{{ $smsSetting->nexmo_api_key }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="nexmo_api_secret" fieldLabel="Auth Token"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="nexmo_api_secret" id="nexmo_api_secret"
                                           class="form-control height-35 f-14"
                                           value="{{ $smsSetting->nexmo_api_secret }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-12">
                                <x-forms.tel fieldId="nexmo_from_number"
                                             :fieldLabel="'SMS ' . __('sms::app.fromNumber')"
                                             fieldName="nexmo_from_number"
                                             fieldPlaceholder="e.g. 987654321"
                                             fieldRequired="true"
                                             :fieldValue="$smsSetting->nexmo_from_number"></x-forms.tel>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10">
                        <h4 class="mb-4 f-21 font-weight-normal text-capitalize">
                            <img src="{{ asset('img/msg91_logo.svg') }}" width="100" alt="">
                        </h4>
                    </div>

                    <div class="col-lg-2 mb-2">
                        <div class="form-group text-right">
                            <div class="d-flex mt-2 justify-content-end">
                                <x-forms.checkbox fieldLabel=" " class="sms-gateway-status" fieldName="msg91-gateway"
                                                  fieldId="msg91-gateway" fieldValue="msg91"
                                                  :checked="$smsSetting->msg91_status"/>
                            </div>
                        </div>
                    </div>

                    <div id="msg91-form" class="col-lg-12 @if (!$smsSetting->msg91_status) d-none @endif">
                        <div class="row">
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="nexmo_api_key" fieldLabel="AUTH KEY"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="msg91_auth_key" id="msg91_auth_key"
                                           class="form-control height-35 f-14"
                                           value="{{ $smsSetting->msg91_auth_key }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-6">
                                <x-forms.label class="mt-3" fieldId="nexmo_api_secret" fieldLabel="SENDER ID"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="msg91_from" id="msg91_from"
                                           class="form-control height-35 f-14" value="{{ $smsSetting->msg91_from }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                        </div>
                        <div row class="row pt-3">
                            @foreach ($smsSettings as $setting)
                                <div
                                    class="col-md-6 msg91Template{{$setting->id}} @if ($setting->send_sms == 'no') d-none @endif">
                                    <x-forms.label :fieldId="'msg91_'.$setting->id"
                                                   :fieldLabel="$setting->slug->label()"></x-forms.label>
                                    <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                       data-clipboard-target="#msg91_template{{$setting->id}}">
                                        <i class="fa fa-copy mx-1"></i></a>
                                    <textarea class="form-control f-14 pt-2" readonly rows="4"
                                              id="msg91_template{{$setting->id}}">{{ $setting->slug->msg91Template() }}</textarea>
                                    <div class="form-group my-3">
                                        <x-forms.label :fieldId="'msg91_flow_id'.$setting->id"
                                                       :fieldLabel="$setting->slug->label(). ' ' . __('app.flowId')"
                                                       :fieldRequired="true"></x-forms.label>

                                        <input type="text" class="form-control height-35 f-14"
                                               value="{{ $setting->msg91_flow_id }}"
                                               name="msg91_flow_id[{{$setting->id}}]"
                                               id="{{'msg91_flow_id'.$setting->id }}"
                                               @if ($setting->send_sms == 'no') disabled @endif required>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row pt-2">
                    <div class="col-md-10">
                        <h4 class="mb-4 f-21 font-weight-normal text-capitalize text-dark-grey">
                            <img src="{{ asset('img/telegram.svg') }}" width="40" alt=""> Telegram
                        </h4>
                    </div>

                    <div class="col-lg-2 mb-2">
                        <div class="form-group text-right">
                            <div class="d-flex mt-2 justify-content-end">
                                <x-forms.checkbox fieldLabel=" " class="sms-gateway-status" fieldName="telegram"
                                                  fieldId="msg91-gateway" fieldValue="telegram"
                                                  :checked="$smsSetting->telegram_status"/>
                            </div>
                        </div>
                    </div>

                    <div id="telegram-form" class="col-lg-12 @if (!$smsSetting->telegram_status) d-none @endif">
                        <div class="row">
                            <div class="col-12">
                                <x-forms.label class="mt-3" fieldId="telegram_bot_token" :fieldLabel="__('sms::modules.telegramTelegramBotToken')"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="telegram_bot_token" id="telegram_bot_token"
                                           class="form-control height-35 f-14"
                                           value="{{ $smsSetting->telegram_bot_token }}">

                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                                data-original-title="Click Here to View Key"
                                                class="btn btn-outline-secondary border-grey height-35 toggle-password">
                                            <i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                            </div>
                            <div class="col-12">
                                <x-forms.label class="mt-3" fieldId="telegram_bot_name" :fieldLabel="__('sms::modules.telegramTelegramBotName')"
                                               fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="text" name="telegram_bot_name" id="telegram_bot_name"
                                           class="form-control height-35 f-14"
                                           value="{{ $smsSetting->telegram_bot_name }}">
                                </x-forms.input-group>
                            </div>

                        </div>
                    </div>
                </div>
                @else
                <div class="col-lg-12 py-5">
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                        <div>
                            @lang('sms::modules.smsConfigure')
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-xl-4 col-lg-12 col-md-12 ntfcn-tab-content-right border-left-grey px-4 pb-4 pt-2">
                <h4 class="f-16 text-capitalize f-w-500 text-dark-grey mb-5 mb-lg-0">
                    @lang("sms::app.notificationTitle") <br/>
                </h4>
                @foreach ($smsSettings as $setting)
                    <div class="mb-3 d-flex">
                        <x-forms.checkbox :checked="$setting->send_sms == 'yes'" class="send_sms"
                                          :fieldLabel="$setting->slug->label()"
                                          fieldName="send_sms[]" :fieldId="'send_sms_'.$setting->id"
                                          :fieldValue="$setting->id"/>
                    </div>
                @endforeach
            </div>

            <!-- Buttons Start -->
            <div class="w-100 border-top-grey">
                <div class="settings-btns py-3 d-lg-flex d-md-flex justify-content-end px-4">
                    @if (user()->is_superadmin)
                        <x-forms.button-secondary id="send-test-email" class="mr-3" icon="location-arrow">
                        @lang('sms::modules.sendTestMessage')</x-forms.button-secondary>
                    @endif
                    <x-forms.button-primary id="save-form" icon="check">@lang('app.save')</x-forms.button-primary>
                </div>
            </div>
            <!-- Buttons End -->

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')

    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function (e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.smsTemplateCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });

        @if ($smsSetting->whatsapp_status == 0)
        $('#whatapp_from_number').attr('readonly', true);
        @endif

        $('input[name="whatsapp_status"]').change(function () {
            let status = $(this).val();
            if (status == "0") {
                $("#whatapp_from_number").attr("readonly", true);
                $('#whatsappTemplates').addClass('d-none');
            } else {
                $("#whatapp_from_number").removeAttr("readonly");
                $('#whatsappTemplates').removeClass('d-none');
            }
        })
        $('#twilio-gateway, #nexmo-gateway, #msg91-gateway').change(function () {
            var gateway = $(this).val();

            $('#active_gateway').val('')
            if ($(this).is(':checked')) {
                console.log('#' + gateway + '-form');
                $('#' + gateway + '-form').removeClass('d-none');

                $('.sms-gateway-status').each(function (index) {
                    var switchStatus = $('.sms-gateway-status')[index].value;
                    var switchChecked = $('.sms-gateway-status')[index].checked;
                    if (gateway != switchStatus && switchChecked) {
                        $(this).trigger('click');
                    }
                });
                $('#active_gateway').val(gateway)

            } else {
                $('#' + gateway + '-form').addClass('d-none');
            }
        });

        $('#save-form').click(function () {
            var url = "{{ route('sms-setting.update', '1') }}";

            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#editSettings').serialize(),
            })
        });

        @if (user()->is_superadmin)
        $('#send-test-email').click(function () {
            const url = "{{ route('sms-setting.test_message') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
        @endif

        $('.send_sms').change(function () {
            var status = $(this).is(':checked');
            var settingId = $(this).val();

            if (status) {
                $('input[name="msg91_flow_id[' + settingId + ']"]').removeAttr('disabled');
                $('.whatsappTemplate' + settingId).removeClass('d-none');
                $('.msg91Template' + settingId).removeClass('d-none');
            } else {
                $('input[name="msg91_flow_id[' + settingId + ']"]').attr('disabled', true);
                $('.whatsappTemplate' + settingId).addClass('d-none');
                $('.msg91Template' + settingId).addClass('d-none');
            }
        });

    </script>
@endpush
