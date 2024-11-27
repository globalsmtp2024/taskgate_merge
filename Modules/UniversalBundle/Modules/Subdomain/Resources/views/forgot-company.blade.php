@extends('super-admin.layouts.front-app')
@push('head-script')
    <style>
        .email {
            display: flex !important;
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
        }

        .domain-text {
            padding: 6px 20px;
            font-size: 14px;
            font-weight: 600;
            padding-top: 6px;
            text-align: center;
            flex-shrink: 3;
        }

        .center-vh {
            height: unset !important;
        }
    </style>
@endpush
@section('content')
    <section class="section bg-img" id="section-contact"
             style="background-image: url({{ asset('front/img/bg-cup.jpg') }})" data-overlay="8">
        <div class="container">
            <div class="row gap-y justify-content-center align-items-center">
                <div class="col-12 col-md-6 form-section">

                    <form class="row" method="POST" id="register">
                        @csrf
                        <div class="col-12 col-md-10 bg-white px-30 py-45 rounded">
                            <p id="alert"></p>

                            <div class="form-group removeable">
                                <div>
                                    <div class="email">
                                        <input type="text" class="form-control" placeholder="Enter Email Address"
                                               name="email">
                                        <button
                                            class="domain-text btn btn-info btn-lg btn-block btn-rounded text-uppercase waves-effect waves-light"
                                            onclick="confirm();return false;">@lang('app.submit')</button>
                                    </div>
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
        function confirm() {
            $.easyAjax({
                url: '{{route('front.submit-forgot-password')}}',
                container: '.form-section',
                type: "POST",
                data: $('#register').serialize(),
                messagePosition: "inline",
                success: function (response) {
                    if (response.status == 'success') {
                        $('.removeable').remove();
                    }
                }
            })
        }
    </script>
@endpush
