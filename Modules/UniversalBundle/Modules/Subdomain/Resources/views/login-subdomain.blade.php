<x-auth>
    <form id="login-form" action="{{ route('login') }}" class="ajax-form" method="POST">
        @csrf
        <h3 class="mb-4 f-w-500">@lang('subdomain::app.core.workspaceTitle')</h3>

        <h3 class="mb-5 f-w-200 f-15">
            {{__('subdomain::app.core.enterYourSubdomain')}}
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

        <div id="password-section " class="mb-3 mt-5">
            <x-forms.input-group>
                <input type="text" name="sub_domain" id="sub_domain"
                       placeholder="Subdomain" tabindex="3"
                       class="form-control height-50 f-15 light_text @error('sub_domain') is-invalid @enderror">

                <x-slot name="append">
                                        <span
                                            class="btn btn-outline-secondary border-grey height-50">.{{ getDomain() }}</span>
                </x-slot>

            </x-forms.input-group>
        </div>
        @if ($errors->has('sub_domain'))
            <div class="help-block">{{ $errors->first('sub_domain') }}</div>
        @endif

        <button type="submit"
                id="save-form"
                class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            @lang('subdomain::app.core.continue')
            <i class="fa fa-arrow-right pl-1"></i>
        </button>
        <x-slot name="outsideLoginBox">
            <div class="form-group mt-2">
                <div class="col-sm-12 text-center">
                    <p class="my-2">{{__('subdomain::app.core.signInTitle')}}</p>
                    <span class="my-1"><a href="{{ route('front.forgot-company') }}"
                                          class="text-primary ">
                                        <b>
                                            {{__('subdomain::app.messages.findCompanyUrl')}}
                                        </b>
                                    </a>
                                </span>
                </div>
            </div>
            @if (isWorksuiteSaas() && !$globalSetting->frontend_disable)
                <p class="mt-2 f-12"><a href="{{ route('front.home') }}"
                                        class="text-dark-grey">@lang('superadmin.goToWebsite')</a></p>
            @endif

            @if ($global->enable_register)
                <p class="f-12"><a href="{{ route('front.signup.index') }}"
                               class="text-dark-grey">@lang('subdomain::app.core.dontHaveAccount') </a>
                </p>
             @endif
        </x-slot>
    </form>

    <x-slot name="scripts">
        <script>
            $(document).ready(function () {
                $('#save-form').on('click', function (e) {
                    e.preventDefault();
                    $.easyAjax({
                        url: '{{route('front.check-domain')}}',
                        container: '#login-form',
                        type: "POST",
                        buttonSelector: "#save-form",
                        data: $('#login-form').serialize(),
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

