@extends('super-admin.layouts.saas-app')

@section('content')
    <section class="sp-100 login-section" id="section-contact">
        <div class="container">
            <div class="login-box mt-5 shadow bg-white form-section row align-items-center">
                <div class=" col-lg-12 order-lg-1 " id="form-box">

                    <h5 class="mb-0 text-center mt-5">
                        {{$pageTitle}}
                    </h5>
                    <form class="form-horizontal " method="POST" id="register">
                        @csrf
                        <p id="alert"></p>

                        <div class="form-group @if($errors->has('sub_domain')) has-error @endif">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="subdomain" name="sub_domain"
                                       id="sub_domain">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">.{{ getDomain() }}</span>
                                </div>

                            </div>
                            @if ($errors->has('sub_domain'))
                                <div class="help-block">{{ $errors->first('sub_domain') }}</div>
                            @endif
                        </div>

                        <div class="form-group text-center">
                            <div class="col-xs-6 text-center">
                                <button
                                    class="btn btn-custom btn-rounded text-uppercase waves-effect waves-light"
                                    type="submit" id="save-form">@lang('subdomain::app.core.continue')</button>
                            </div>
                        </div>
                        <div class="form-group m-b-0">
                            <div class="col-sm-12 text-center">
                                <div class="p-1">{{__('subdomain::app.core.signInTitle')}}</div>
                                <span> <a href="{{ route('front.forgot-company') }}" class="text-primary m-l-5">
                                        <b>
                                            {{__('subdomain::app.messages.findCompanyUrl')}}

                                        </b></a></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('footer-script')
    <script>
        $('#save-form').on('click', function (e) {
            e.preventDefault();
            $.easyAjax({
                url: '{{route('front.check-domain')}}',
                container: '#register',
                type: "POST",
                messagePosition: "inline",
                data: $('#register').serialize(),

            })
        });
    </script>
@endpush
