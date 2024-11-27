@extends('super-admin.layouts.saas-app')
@section('content')
    <section class="sp-100 login-section" id="section-contact">
        <div class="container">
            <div class="login-box mt-5 shadow bg-white form-section">
                <h5 class="mb-0">
                    {{$pageTitle}}
                </h5>

                <form class="form-horizontal form-material" method="POST" id="register">
                    @csrf
                    <p>
                        {{__('subdomain::app.messages.forgotPageMessage')}}

                    </p>
                    <p id="alert"></p>
                    <div class="form-group removeable {{ $errors->has('email') ? 'has-error' : '' }}">
                        <div class="col-xs-12">
                            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}"
                                   autofocus required="" placeholder="@lang('app.email')">
                            @if ($errors->has('email'))
                                <div class="help-block with-errors">{{ $errors->first('email') }}</div>
                            @endif

                        </div>
                    </div>

                    <div class="form-group removeable text-center m-t-20">
                        <div class="col-xs-6 text-center">
                            <button
                                class="btn btn-custom btn-rounded text-uppercase waves-effect waves-light pl-5 pr-5"
                                type="submit" id="submit-form"
                                onclick="confirm();return false;"
                            >@lang('app.submit')</button>
                        </div>

                    </div>
                    <div class="form-group mt-2">
                        <div class="col-sm-12 text-center">
                            <p class="my-2 text-dark-grey">{{__('subdomain::app.core.alreadyKnow')}}</p>

                            <span class="my-1">
                                    <a href="{{ route('front.workspace') }}"
                                       class="text-primary f-w-500">
                                        {{__('subdomain::app.core.backToSignin')}}
                                    </a>
                                </span>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>
@endsection
@push('footer-script')
    <script>
        function confirm() {
            $.easyAjax({
                url: '{{route('front.submit-forgot-password')}}',
                container: '.form-section',
                type: "POST",
                data: $('#register').serialize(),
                messagePosition: "inline",
                success: function (response) {
                    if (response.status === 'success') {
                        $('.removeable').remove();
                    }
                }
            })
        }

    </script>
@endpush
