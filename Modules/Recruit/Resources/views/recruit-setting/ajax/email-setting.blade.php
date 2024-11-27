<div class="table-responsive p-20">
    <div>
        <h3>
            Email Setting
        </h3>
        {{-- <p>
            <a href="https://saurabh-nakoti.medium.com/how-to-set-up-smtp-in-gmail-using-an-app-password-96adffa164b3" target="_blank">Set up SMTP in Gmail</a><br>
            <a href="https://learn.microsoft.com/en-us/exchange/mail-flow-best-practices/how-to-set-up-a-multifunction-device-or-application-to-send-email-using-microsoft-365-or-office-365" target="_blank">Set up email in Microsoft 365</a><br>
            <a href="https://ph.godaddy.com/help/enable-smtp-authentication-40981" target="_blank">Enable SMTP in GoDaddy</a>
        </p> --}}
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 ">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="mail_from_name">Mail From Name
                        <sup class="f-14 mr-1">*</sup>
                    </label>
                    <input type="text"
                        value="{{ isset($mail->companySmtpSetting->sender_name) ? $mail->companySmtpSetting->sender_name : '' }}"
                        class="form-control height-35 f-14" placeholder="e.g. John Doe" name="mail_from_name"
                        id="mail_from_name" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 ">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="mail_from_email">Mail From Email
                        <sup class="f-14 mr-1">*</sup>
                    </label>
                    <input type="text" class="form-control height-35 f-14"
                        value="{{ isset($mail->companySmtpSetting->sender_email) ? $mail->companySmtpSetting->sender_email : '' }}"
                        placeholder="e.g. johndoe@example.com" name="mail_from_email" id="mail_from_email"
                        autocomplete="off">

                </div>
            </div>
        </div>
        {{-- <div class="row"> --}}
        {{-- <div class="col-lg-6 col-md-6 ">
                <label class="f-14 text-dark-grey mb-12 mt-3" data-label="" for="mail_connection">Enable Email Queue

                    <svg class="svg-inline--fa fa-question-circle fa-w-16" data-toggle="popover"
                        data-placement="top"
                        data-content="<p>To speed up the emailing process, the system will add the emails in queue and will send them via cron job.</p>  <p> Choose <u>No</u> to send email immediately <strong>(Slower)</strong>.</p><p>Choose <u>Yes</u> to send emails in background <strong>(Faster)</strong>.</p><p><em>*Make sure the cron job is configured properly to use email queueing.</em></p>"
                        data-html="true" data-trigger="hover" aria-hidden="true" focusable="false" data-prefix="fa"
                        data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512" data-fa-i2svg="" data-original-title="" title="">
                        <path fill="currentColor"
                            d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z">
                        </path>
                    </svg>
                </label>
                <div class="form-group mb-0">
                    <div class="dropdown bootstrap-select form-control select-picker">
                        <select name="mail_connection" id="mail_connection" class="form-control select-picker"
                            data-size="8">
                            <option selected="" value="sync">
                                No
                            </option>
                            <option value="database">
                                Yes
                            </option>
                        </select>
                    </div>
                </div>
            </div> --}}
        {{-- <div class="col-lg-6 col-md-6 form-group my-3">
                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">Mail Driver</label>
                <div class="d-flex">
                    <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                        <input type="radio" value="mail" class="custom-control-input" id="mail_driver-mail"
                            name="mail_driver" autocomplete="off">
                        <label class="custom-control-label pt-1 cursor-pointer" for="mail_driver-mail">Mail</label>
                    </div>
                    <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                        <input type="radio" value="smtp" class="custom-control-input" id="mail_driver-smtp"
                            name="mail_driver" checked="" autocomplete="off">
                        <label class="custom-control-label pt-1 cursor-pointer" for="mail_driver-smtp">SMTP</label>
                    </div>
                </div>

            </div> --}}
        {{-- </div> --}}
        <div class="row">
            <div class="col-lg-3 col-md-3 smtp_div">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="mail_host">SMTP Host
                        <sup class="f-14 mr-1">*</sup>

                    </label>

                    <input type="text" class="form-control height-35 f-14" placeholder=""
                        value="{{ isset($mail->companySmtpSetting->smtp_host) ? $mail->companySmtpSetting->smtp_host : '' }}"
                        name="mail_host" id="mail_host" autocomplete="off">

                </div>
            </div>
            <div class="col-lg-6 col-md-6 smtp_div">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="imap_host">Imap Host
                        <sup class="f-14 mr-1">*</sup>

                    </label>

                    <input type="text" placeholder="{mail.REPLACE_WITH_YOUR_DOMAIN.com:993/imap/ssl/novalidate-cert}"
                        class="form-control height-35 f-14" placeholder=""
                        value="{{ isset($mail->companySmtpSetting->imap_host) ? $mail->companySmtpSetting->imap_host : '' }}"
                        name="imap_host" id="imap_host" autocomplete="off">

                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="form-group my-3 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="mail_port">Mail Port
                        <sup class="f-14 mr-1">*</sup>

                    </label>

                    <input type="text" class="form-control height-35 f-14" placeholder="465" name="mail_port"
                        value="{{ isset($mail->companySmtpSetting->smtp_port) ? $mail->companySmtpSetting->smtp_port : '' }}"
                        id="mail_port" autocomplete="off">

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 smtp_div">
                <label class="f-14 text-dark-grey mb-12 mt-3" data-label="" for="mail_encryption">Mail
                    Encryption</label>
                <div class="form-group mb-0">
                    <div class="dropdown bootstrap-select form-control select-picker">
                        <select name="mail_encryption" id="mail_encryption" class="form-control select-picker"
                            data-size="8">
                            <option value="tls" selected>
                                tls
                            </option>
                            <option value="ssl">
                                ssl
                            </option>
                            <option value="starttls">
                                starttls
                            </option>
                            <option value="null">
                                none
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 smtp_div">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2 field">
                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="mail_username">Mail Username
                        <sup class="f-14 mr-1">*</sup>

                    </label>

                    <input type="text" class="form-control height-35 f-14" placeholder="e.g. johndoe@example.com"
                        value="{{ isset($mail->companySmtpSetting->smtp_username) ? $mail->companySmtpSetting->smtp_username : '' }}"
                        name="mail_username" id="mail_username" autocomplete="off">

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 smtp_div">
                <label class="f-14 text-dark-grey mb-12 mt-3" data-label="" for="mail_password">Mail Password

                </label>
                <div class="input-group">

                    <input type="password" name="mail_password" id="mail_password"
                        value="{{ isset($mail->companySmtpSetting->smtp_password) ? $mail->companySmtpSetting->smtp_password : '' }}"
                        placeholder="Mail Password" class="form-control height-35 f-14 field" autocomplete="off">

                    <div class="input-group-append">
                        <button type="button" data-toggle="tooltip" data-original-title="Show/Hide Value"
                            class="btn btn-outline-secondary border-grey height-35 toggle-password"><svg
                                class="svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false"
                                data-prefix="fa" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 576 512" data-fa-i2svg="">
                                <path fill="currentColor"
                                    d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                                </path>
                            </svg><!-- <i class="fa fa-eye"></i> Font Awesome fontawesome.com --></button>
                    </div>

                </div>
            </div>
            {{-- <div class="col-lg-6 col-md-6 smtp_div">
                <label class="f-14 text-dark-grey mb-12 mt-3" data-label="" for="email_verified">Mail
                    Encryption</label>
                <div class="form-group mb-0">
                    <div class="dropdown bootstrap-select form-control select-picker">
                        <select name="email_verified" id="email_verified" class="form-control select-picker"
                            data-size="8">
                            <option value="yes">
                                Yes
                            </option>
                            <option selected="no">
                                No
                            </option>
                        </select>
                    </div>
                </div>
            </div> --}}
        </div>
        <div class="row" style="padding-top: 10px;">
            <div class="col-12">
                <div class="float-right">
                    @if (isset($mail->companySmtpSetting->sender_name) && $mail->companySmtpSetting->added_type == 'google')
                        <a href="{{ route('disconnect.google.authentication') }}" class="btn btn-primary mb-3"
                            data-toggle="tooltip" style="background-color:#D30000">
                            <i class="fab fa-google"></i>
                            Disconnect Google
                        </a>
                    @else
                        <a href="{{ route('leads.login.google') }}" class="btn btn-primary mb-3"
                            data-toggle="tooltip" style="background-color:#D30000">
                            <i class="fab fa-google"></i>
                            Login With Google
                        </a>
                        <button type="button" class="btn btn-primary mb-3" id="save-email-form">
                            <svg class="svg-inline--fa fa-check fa-w-16 mr-1" aria-hidden="true" focusable="false"
                                data-prefix="fa" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512" data-fa-i2svg="">
                                <path fill="currentColor"
                                    d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z">
                                </path>
                            </svg><!-- <i class="fa fa-check mr-1"></i> Font Awesome fontawesome.com -->
                            Save
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#save-email-form').on('click', function(e) {
            e.preventDefault();

            // Collect values from the form fields
            var formData = {
                mail_from_name: $('#mail_from_name').val(),
                mail_from_email: $('#mail_from_email').val(),
                mail_connection: $('#mail_connection').val(),
                mail_driver: $('input[name="mail_driver"]:checked').val(),
                mail_host: $('#mail_host').val(),
                imap_host: $('#imap_host').val(),
                mail_port: $('#mail_port').val(),
                mail_encryption: $('#mail_encryption').val(),
                mail_username: $('#mail_username').val(),
                mail_password: $('#mail_password').val(),
                email_verified: $('#email_verified').val(),
                _token: '{{ csrf_token() }}' // Include CSRF token if using Laravel
            };

            // Send AJAX request
            $.ajax({
                url: '{{ route('add-company-smtp-setting') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: "Success!!",
                            text: response.message,
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!!",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
