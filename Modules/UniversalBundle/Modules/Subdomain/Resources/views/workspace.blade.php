@extends('super-admin.layouts.front-app')
@push('head-script')
    <style>
        .sub-domain {
            display: flex !important;
            justify-content: center;
        }

        .help-block {
            text-align: center;
            color: red;
        }

        .form-control {
            border: 1px solid #e7eaf0;
            font-size: 15px;
            padding: 0 16px;
            -webkit-box-shadow: none;
            box-shadow: none;
            border-radius: 0;
            -webkit-transition: all .2s ease-in-out;
            transition: all .2s ease-in-out;
            color: #555;
            width: unset !important;
        }

        .domain-text {
            padding: 6px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #3e4042;
            text-align: center;
            background-color: #eee;
        }

        .center-vh {
            height: unset !important;
        }
    </style>
@endpush
@section('content')
    <section class="section bg-img" id="section-contact"
             style="background-image: url({{ asset('front/img/bg-cup.jpg') }})"
             data-overlay="8">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-10 offset-md-2 form-section">
                    <form class="row" method="POST" id="register">
                        @csrf
                        <div class="col-12 col-md-10 bg-white px-30 py-45 rounded">
                            <p id="alert"></p>


                            <div class="form-group">
                                <div class="sub-domain">
                                    <input type="text" class="form-control" placeholder="your-login-url"
                                           name="sub_domain">
                                    @if(function_exists('getDomain'))
                                        <span class="domain-text">.{{ getDomain() }}</span>
                                    @else
                                        <span class="domain-text">.{{ $_SERVER['SERVER_NAME'] }}</span>
                                    @endif
                                </div>
                            </div>


                            <div class="form-group center-vh">
                                <div class="col-xs-12">
                                    <button
                                        class="btn btn-info btn-lg btn-block btn-rounded text-uppercase waves-effect waves-light"
                                        type="submit" id="save-form">@lang('subdomain::app.core.continue')</button>
                                </div>
                            </div>

                            <div class="form-group m-b-0">
                                <div class="col-sm-12 text-center">
                                    <p>{{__('subdomain::app.core.signInTitle')}} <a
                                            href="{{ route('front.forgot-company') }}"
                                            class="text-primary m-l-5"><b>{{__('subdomain::app.messages.findCompanyUrl')}}</b></a>
                                    </p>
                                </div>
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
