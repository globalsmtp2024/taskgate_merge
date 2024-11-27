<x-auth>
    <form id="login-form" action="{{ route('login') }}" class="ajax-form" method="POST">
        @csrf
        <h3 class="mb-4 f-w-500 f-15">
            {{__('subdomain::app.messages.forgotPageMessage')}}
        </h3>
        @if($socialAuthSettings->social_auth_enable)
            <script>
                const facebook = "{{ route('social_login', 'facebook') }}";
                const google = "{{ route('social_login', 'google') }}";
                const twitter = "{{ route('social_login', 'twitter') }}";
                const linkedin = "{{ route('social_login', 'linkedin') }}";
            </script>

            @if ($socialAuthSettings->google_status == 'enable')
                <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = google;">
                                    <span>
                                        <img src="{{ asset('img/google.png') }}" alt="Google"/>
                                    </span>
                    @lang('auth.signInGoogle')</a>
            @endif
            @if ($socialAuthSettings->facebook_status == 'enable')
                <a class="mb-3 height_50 rounded f-w-500"
                   onclick="window.location.href = facebook;">
                                    <span>
                                        <img src="{{ asset('img/fb.png') }}" alt="Google"/>
                                    </span>
                    @lang('auth.signInFacebook')
                </a>
            @endif
            @if ($socialAuthSettings->twitter_status == 'enable')
                <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = twitter;">
                                    <span>
                                        <img src="{{ asset('img/twitter.png') }}" alt="Google"/>
                                    </span>
                    @lang('auth.signInTwitter')
                </a>
            @endif
            @if ($socialAuthSettings->linkedin_status == 'enable')
                <a class="mb-3 height_50 rounded f-w-500"
                   onclick="window.location.href = linkedin;">
                                    <span>
                                        <img src="{{ asset('img/linkedin.png') }}" alt="Google"/>
                                    </span>
                    @lang('auth.signInLinkedin')
                </a>
            @endif

        @endif
        <div class="form-group text-left removeable">
            <label for="email">@lang('auth.email')</label>
            <input tabindex="1" type="email" name="email"
                   class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror"
                   autofocus
                   value="{{request()->old('email')}}"
                   placeholder="@lang('auth.email')" id="email">
            @if ($errors->has('email'))
                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
            @endif
        </div>
        @if ($errors->has('sub_domain'))
            <div class="help-block">{{ $errors->first('sub_domain') }}</div>
        @endif

        <button type="submit"
                id="save-form"
                class="removeable btn-primary f-w-500 rounded w-100 height-50 f-18">@lang('app.submit')
            <i class="fa fa-arrow-right pl-1"></i></button>
        <x-slot name="outsideLoginBox">
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

        </x-slot>
    </form>

    <x-slot name="scripts">
        <script>
            $(document).ready(function () {

                $('#save-form').on('click', function (e) {
                    e.preventDefault();
                    $.easyAjax({
                        url: '{{route('front.submit-forgot-password')}}',
                        container: '#login-form',
                        type: "POST",
                        blockUI: true,
                        data: $('#login-form').serialize(),
                        messagePosition: "inline",
                        success: function (response) {
                            if (response.status === 'success') {
                                $('.removeable').remove();
                            }
                        },
                    })
                });

                @if (session('message'))
                Swal.fire({
                    icon: 'error',
                    text: '{{ session('message') }}',
                    showConfirmButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                })
                @endif

            });
        </script>
    </x-slot>

</x-auth>

