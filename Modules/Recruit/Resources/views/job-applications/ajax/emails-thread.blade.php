<!-- ROW START -->
<!-- Include Quill stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
    body {
        background-color: #f8f9fa;
    }

    #email-list-sent {
        cursor: pointer !important;
    }

    #email-list-inbox {
        cursor: pointer !important;
    }

    .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-text {
        color: rgba(173, 168, 168, 0.774);
        text-align: center;
    }

    .width-span span {
        width: 45%;
    }

    .list-group-item {
        display: block;
    }

    .table-row {
        display: table;
        width: 100%;
        table-layout: fixed;
        /* Ensures the table cells have a fixed layout */
    }

    .table-cell {
        display: table-cell;
        vertical-align: middle;
        padding: 5px;
    }

    .subject {
        width: 300px;
        /* Fixed width for the subject */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .info-text {
        white-space: nowrap;
        color: rgba(173, 168, 168, 0.774);
    }

    .date {
        text-align: right;
        /* Aligns the date to the right */
    }

    .gmail_signature td img {
        max-width: inherit !important;
    }
</style>
<div id="interview-table">
    <div class="row p-20">
        <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
            <div class="row">
                <div class="col-6">
                </div>
                @if ($application->smtpExist)
                    <div class="col-6">
                        <div class="float-right">
                            <button id="composeButton" class="btn btn-primary mb-3" data-toggle="modal"
                                data-target="#composeModal">Compose</button>
                        </div>
                    </div>
                @else
                    <div class="col-6">
                        <div class="float-right">
                            <a href="{{ route('leads.login.google') }}" class="btn btn-primary mb-3 mr-3"
                                style="background-color: #D30000" data-toggle="tooltip">
                                <i class="fab fa-google"></i>
                                Login With Google
                            </a>
                            <a class="btn btn-primary mb-3"
                                href="{{ route('recruit-settings.index', ['tab' => 'recruit-email-setting']) }}"> Set
                                SMTP Settings<a />
                        </div>
                    </div>
                @endif
            </div>


            @if ($application->smtpExist || !$application->smtpExist)
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    {{-- <li class="nav-item">
                    <a class="nav-link email-tab active" data-type="inbox" data-toggle="tab" href="#inbox">Inbox</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link email-tab" data-type="sent" data-toggle="tab" href="#sent">Sent</a>
                </li> --}}
                    <li class="nav-item">
                        <a class="nav-link email-tab active" id="inbox_tab" data-type="inbox"
                            href="{{ route('job-applications.show', $application->id) . '?view=emails-thread' }}">Inbox</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link email-tab" data-type="sent" id="sent_tab"
                            href="{{ route('job-applications.show', $application->id) . '?view=emails-thread&sent=true' }}">Sent</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="d-flex flex-wrap">
                    <div class="tab-content" style="width: 100%">
                        <div id="inbox" class="tab-pane active"><br>
                            <div id="email-list-inbox" class="list-group">
                                @if (isset($application->receivedEmails['emails']) && count($application->receivedEmails['emails']))
                                    @foreach ($application->receivedEmails['emails'] as $email)
                                        @php
                                            $formattedDate = getDataTimeFormat($email['date']);

                                        @endphp
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center width-span"
                                            data-id="1" data-type="sent"
                                            data-attachments="{{ json_encode($email['attachments']) }}">
                                            <div class="table-row">
                                                <div class="table-cell subject">{{ $email['subject'] }}
                                                    <br>
                                                    <span>From: {{ $email['from'] }}</span>
                                                </div>
                                                <div class="table-cell d-flex align-items-center">
                                                    <span class="info-text mx-2">Click To View Details</span>
                                                </div>
                                                <div class="table-cell date"><b>{{ $formattedDate }}</b></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="emailBody" value="{{ $email['body'] }}">
                                        <input type="hidden" name="ref_id" class="ref_id"
                                            value="{{ $email['ref_id'] }}">
                                    @endforeach
                                @else
                                    <div>No Email Found</div>
                                @endif
                            </div>
                        </div>

                        <div id="sent" class="tab-pane fade"><br>
                            <div id="email-list-sent" class="list-group">
                                @if (isset($application->sentEmails['emails']) && count($application->sentEmails['emails']))
                                    @foreach ($application->sentEmails['emails'] as $email)
                                        @php
                                            $formattedDate = $email['date'];

                                        @endphp
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center width-span"
                                            data-id="1" data-type="sent"
                                            data-attachments="{{ json_encode($email['attachments']) }}">
                                            <div class="table-row">
                                                <div class="table-cell subject">{{ $email['subject'] }}
                                                    <br>
                                                    <span>To: {{ $email['to'] }}</span>
                                                </div>
                                                <div class="table-cell d-flex align-items-center">
                                                    <span class="info-text mx-2">Click To View Details</span>
                                                </div>
                                                <div class="table-cell date"><b>{{ $formattedDate }}</b></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="emailBody" value="{{ $email['body'] }}">
                                        <input type="hidden" name="ref_id" class="ref_id"
                                            value="{{ $email['ref_id'] }}">
                                    @endforeach
                                @else
                                    <div>No Email Found</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</body>

<!-- Email Details Modal -->
<div class="modal fade" id="emailDetailModal" tabindex="-1" role="dialog" aria-labelledby="emailDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailDetailModalLabel">Email Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 id="email-subject"></h4>
                <div id="email-content"></div>
                <input type="hidden" name="email_ref_id" id="email-ref-id">
                <h5>Attachments</h5>
                <div id="email-attachments"></div>
                <div id="editor-container"></div>
            </div>
            @if ($application->smtpExist)
                <div id="reply-footer" class="modal-footer">
                    <button id="replyButton" class="btn btn-primary">Reply</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Compose Email Modal -->
<div class="modal" id="composeModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeModalLabel">Compose Email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="compose-email-form">
                    <div class="form-group">
                        <label for="email-subject-input">Subject</label>
                        <input type="text" class="form-control" id="email-subject-input" name="subject" required>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <label for="email-content-input">Content</label>
                            </div>
                            <div class="col-6">
                                <button id="select_template_button" type="button"
                                    class="btn btn-primary select_template_button float-right mb-2">Select
                                    Template</button>
                            </div>
                        </div>
                        <div id="editor" style="height: 300px;"></div>
                        <input type="hidden" id="email-content-input" name="content" required>
                    </div>
                    <div class="form-group">
                        <label for="email-file">Attachment (Max 5MB)</label>
                        <input type="file" class="form-control-file" id="email-file" name="attachment"
                            accept=".pdf,.doc,.docx,.txt">
                        <small class="form-text text-muted">Supported file types: .pdf, .doc, .docx, .txt</small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Select Email Templates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <x-cards.data :title="__('Email TemplateS')"
                        otherClasses="border-0 p-0 d-flex justify-content-between align-items-center table-responsive-sm">
                        <x-table class="border-0 pb-3 admin-dash-table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                </tr>
                            </thead>
                            <tbody id="templateList">
                                <!-- Template data will be loaded here -->
                                @foreach ($application->emailTemplates as $template)
                                    <tr class="template-row" style="cursor: pointer"
                                        data-subject="{{ $template->subject }}"
                                        data-content="{{ $template->content }}">
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->subject }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-table>
                    </x-cards.data>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const url = new URL(window.location.href);
        const isSentTrue = url.searchParams.get('sent') === 'true';

        if (isSentTrue) {
            $("#reply-footer").hide();
        } else {
            $("#reply-footer").show();
        }

        let currentEditor = null;
        var quillReply;
        $("#composeButton").on('click', function(e) {
            currentEditor = "compose";
        });
        //Dont Comment This Code it is in working state
        $(document).on("click", function(event) {
            setTimeout(function() {
                $(".modal-backdrop.show").remove();
            }, 500);
        });
        //Dont Comment This Code it is in working state
        $('.list-group-item').on('click', function() {
            currentEditor = "reply";
            $("#replyButton").attr('hidden', false);

            // Remove existing Quill editor if it exists
            $('#editor-container').html('');
            $('#editor-container').hide();

            // Get the text of the list-group-item excluding the span text
            const emailSubject = $(this).find('span:first').text()
                .trim(); // Get the first span text which contains the subject
            const emailContent = $(this).next('input[type="hidden"][name="emailBody"]').val();
            const emailRefId = $(this).next('input[type="hidden"][name="emailBody"]').next(
                'input[type="hidden"][name="ref_id"]').val();

            const emailAttachments = $(this).data(
                'attachments'); // Assuming attachments data is set in data-attachments
            $('#email-subject').text(emailSubject);
            $('#email-content').html(emailContent);
            $('#email-ref-id').val(emailRefId);
            // Display attachments
            const attachmentsContainer = $('#email-attachments');
            attachmentsContainer.empty(); // Clear previous attachments
            if (emailAttachments && emailAttachments.length > 0) {
                emailAttachments.forEach(attachment => {
                    const attachmentElement = `
                                <div>
                                    <a href="data:application/octet-stream;base64,${attachment.attachment}" download="${attachment.name}">${attachment.name}</a>
                                </div>`;
                    attachmentsContainer.append(attachmentElement);

                });
            } else {
                attachmentsContainer.html('<p>No attachments</p>');
            }

            $('#emailDetailModal').modal('show');
        });
        // Handle click event of the reply button
        $('#replyButton').on('click', function() {
            $(this).attr('hidden', true);
            $('#reply-footer').hide();
            // Remove existing Quill editor if it exists
            $('#editor-container').empty();
            // Remove existing Quill editor if it exists
            $('#editor-container').show();

            const editorContainer = document.getElementById('editor-container');
            // Append the reply form
            var replyForm = `
                    <form id="reply-email-form">
                        <input type="hidden" class="form-control" id="reply-subject-input" name="subject" required>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <label for="reply-content-input">Content</label>
                                </div>
                                <div class="col-6">
                                    <button id="select_reply_template_button" type="button" class="btn btn-primary select_template_button float-right mb-2">Select Template</button>
                                </div>
                            </div>
                            <div id="replyEditor" style="height: 300px;"></div>
                            <input type="hidden" id="reply-content-input" name="content" required>
                        </div>
                        <div class="form-group">
                            <label for="reply-file">Attachment (Max 5MB)</label>
                            <input type="file" class="form-control-file" id="reply-file" name="attachment" accept=".pdf,.doc,.docx,.txt">
                            <small class="form-text text-muted">Supported file types: .pdf, .doc, .docx, .txt</small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                `;
            $('#editor-container').append(replyForm);
            // Create a new div for the text editor
            // var editorDiv = $('<div id="replyEditor" style="height: 300px;"></div>');

            // Append the text editor to the modal body
            // $('#email-content').after(editorDiv);

            // Initialize Quill text editor
            quillReply = new Quill('#replyEditor', {
                theme: 'snow',
                placeholder: 'Write your reply...',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link', 'image'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'align': []
                        }],
                        ['clean']
                    ]
                }
            });

            // Get the HTML content from the email-content div
            var originalContent = $('#email-content').empty();

            // Parse the HTML content into a Delta object
            var delta = quill.clipboard.convert(originalContent);

            // Add some empty lines before the content
            delta.ops.unshift({
                insert: '\n\n\n\n'
            }); // Add more '\n' for more empty lines

            // Insert the Delta object into the Quill editor
            quillReply.setContents(delta);

            // Set the cursor position at the top
            quillReply.setSelection(0);

            // Listen for form submission
            $('#reply-email-form').on('submit', function(e) {
                e.preventDefault();

                // Get HTML content from Quill editor
                var htmlContent = quillReply.root.innerHTML;
                var content_data = escape(htmlContent);

                var emailSubject = $('#email-subject').text();
                var reply_ref_id = $('#email-ref-id').val();
                const refId = reply_ref_id.replace(/[<>]/g, '');
                $('#reply-content-input').val(content_data);
                $('#reply-subject-input').val(emailSubject);

                // Prepare form data

                var formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('to_email', '<?php echo $application->email; ?>');
                formData.append('ref_id', refId);

                const buttons = document.querySelectorAll("#reply-email-form button");
                const lastButton = buttons[buttons.length - 1];
                lastButton.innerHTML =
                    "<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> Processing...";
                lastButton.setAttribute("disabled", "disabled");
                $.ajax({
                    url: '{{ route('job-application-send-compose-email') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status === 'fail') {
                            Swal.fire({
                                title: "Error!!",
                                text: response.message,
                                icon: "error"
                            });
                            lastButton.innerHTML = "Send";
                            lastButton.removeAttribute("disabled");
                        } else {
                            Swal.fire({
                                title: "Success!!",
                                text: response.message,
                                icon: "success"
                            });
                            lastButton.innerHTML = "Send";
                            lastButton.removeAttribute("disabled");
                            // location.reload(true);
                            $('#emailDetailModal').modal('hide');
                            // setTimeout(function() {
                            //     $('.modal-backdrop.fade.show').remove();
                            // }, 500);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        lastButton.innerHTML = "Send";
                        lastButton.removeAttribute("disabled");
                    }
                });
            });
            // Listen for modal shown event
            $('#emailDetailModal').on('shown.bs.modal', function() {
                // When modal is shown, attach event listener to modal close button
                $('#emailDetailModal').on('hidden.bs.modal', function() {
                    // Destroy Quill editor and remove its container
                    if (quillReply) {
                        quillReply.remove();
                    }
                    editorDiv.remove();

                    // Remove the event listener to prevent multiple bindings
                    $('#emailDetailModal').off('hidden.bs.modal');
                });
            });

            addTemplateButtonListener();
        });

        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Compose your email...',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, 4, 5, 6, false]
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link', 'image'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    ['clean']
                ]
            }
        });

        // Handle compose email form submission
        $('#compose-email-form').on('submit', function(e) {
            $('.modal-backdrop.fade.show').remove();
            e.preventDefault();

            // Get HTML content from Quill editor
            var htmlContent = quill.root.innerHTML;
            var content_data = escape(htmlContent);
            $('#email-content-input').val(content_data);

            // Validate file size
            // const fileSize = $('#email-file')[0].files[0].size;
            // const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            // if (fileSize > maxSize) {
            //     alert('File size exceeds the maximum limit of 5MB.');
            //     return;
            // }

            // Prepare form data
            const formData = new FormData(this);
            formData.append('to-email', '<?php echo $application->email; ?>');
            formData.append('_token', '{{ csrf_token() }}');
            const buttons = document.querySelectorAll("#compose-email-form button");
            const lastButton = buttons[buttons.length - 1];
            lastButton.innerHTML =
                "<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> Processing...";
            lastButton.setAttribute("disabled", "disabled");
            // Send AJAX request
            $.ajax({
                url: '{{ route('job-application-send-compose-email') }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'fail') {
                        Swal.fire({
                            title: "Error!!",
                            text: response.message,
                            icon: "error"
                        });
                        lastButton.innerHTML = "Send";
                        lastButton.removeAttribute("disabled");
                    } else {
                        Swal.fire({
                            title: "Success!!",
                            text: response.message,
                            icon: "success"
                        });
                        lastButton.innerHTML = "Send";
                        lastButton.removeAttribute("disabled");
                        // location.reload(true);
                        $('#composeModal').modal('hide');
                        // setTimeout(function() {
                        //     $('.modal-backdrop.fade.show').remove();
                        // }, 500);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error(xhr.responseText);
                    lastButton.innerHTML = "Send";
                    lastButton.removeAttribute("disabled");
                }
            });
        });

        // Adding event listener for Select Template button
        function addTemplateButtonListener() {
            $('.select_template_button').on('click', function() {
                $('#templateModal').modal('show');
            });
        }

        addTemplateButtonListener();
        // $(".select_template_button").on('click', function(e) {
        //     $('#templateModal').modal('show');
        // });

        $('.template-row').on('click', function() {
            // Get subject and content of the selected template
            var subject = $(this).data('subject');
            var content = $(this).data('content');

            // Fill compose email form fields with template data
            // $('#email-subject-input').val(subject);
            // quill.root.innerHTML = content;

            if (currentEditor === "compose") {
                $('#email-subject-input').val(subject);
                // quill.root.innerHTML = content;
                var wrappedContent = '<p>' + content + '</p>';
                var delta = quill.clipboard.convert(wrappedContent);
                quill.setContents(delta);
            } else {
                // Parse the HTML content into a Delta object
                var originalContent = $('#email-content').html();

                // Wrap content in <p> tags and add line breaks
                var wrappedContent = '<p>' + content + '</p><br><br>' + originalContent;

                // Convert to Quill Delta
                var delta = quill.clipboard.convert(wrappedContent);

                // Set HTML of Quill editor
                quillReply.setContents(delta);
            }
            // Close the template modal
            $('#templateModal').modal('hide');

        });

        // Reset scroll behavior and styling when template modal is hidden
        $('#templateModal').on('hidden.bs.modal', function() {
            // Reset styling of the parent compose modal
            $('#composeModal').css('overflow', 'auto');
            $('#emailDetailModal').css('overflow', 'auto');
        });
    });
</script>
