<div class="table-responsive p-20">
    <div id="table-actions" class="d-block d-lg-flex align-items-center">
        <x-forms.button-primary icon="plus" id="addTemplate" class="mb-2">
            @lang('app.addNew')
            @lang('Template')
        </x-forms.button-primary>

    </div>
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('app.name')</th>
            <th>@lang('Subject')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>
        @forelse($mail->companyEmailTemplates as $template)
            <tr class="row{{ $template->id }}">
                <td>{{ $template->name }}
                    @if($template->autoreply_template)
                        <span class="badge badge-primary">@lang('Auto Reply Template')</span>
                    @endif
                </td>
                <td>{{ $template->subject }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a href="javascript:;" data-edit-template-id="{{ $template->id }}"
                            class="edit-email-template task_view_more d-flex align-items-center justify-content-center dropdown-toggle mr-2">
                            <i class="fa fa-edit icons mr-2"></i> @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a href="javascript:;" data-template-id="{{ $template->id }}"
                            class="delete-email-template task_view_more d-flex align-items-center justify-content-center dropdown-toggle">
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-cards.no-record icon="user" :message="__('No template')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

<div class="modal fade" id="addCompanyEmailTemplateModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeModalLabel">@lang('app.addNew') @lang('Email Template')  </h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form id="compose-email-form">
                    <div class="form-group">
                        <label for="email-template-name-input">Name</label>
                        <input type="text" class="form-control" id="email-template-name-input" name="name"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="email-isautoreply-template-input">Auto Reply Template</label>
                        <input type="checkbox" id="email-isautoreply-template-input" name="email-isautoreply-template-input">
                        <input type="hidden" id="email-isautoreply-template-hidden" name="autoreply_template" value="0">
                    </div>
                    <div class="form-group">
                        <label for="email-subject-input">Subject</label>
                        <input type="text" class="form-control" id="email-subject-input" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="email-content-input">Content</label>
                        <div id="editor" style="height: 300px;"></div>
                        <input type="hidden" id="email-content-input" name="content" required>
                    </div>
                    <button id="compose-email-button" type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Email Template Modal -->
<div class="modal fade" id="editCompanyEmailTemplateModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">@lang('app.edit') @lang('Email Template')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form id="edit-email-form">
                    <input type="hidden" id="edit-template-id" name="id">
                    <div class="form-group">
                        <label for="edit-email-template-name-input"> @lang('app.name')</label>
                        <input type="text" class="form-control" id="edit-email-template-name-input" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email-isautoreply-template-input">@lang('Auto Reply Template')</label>
                        <input type="checkbox" id="edit-email-isautoreply-template-input" name="edit-email-isautoreply-template-input">
                        <input type="hidden" id="edit-email-isautoreply-template-hidden" name="autoreply_template" value="0">
                    </div>
                    <div class="form-group">
                        <label for="edit-email-subject-input">@lang('app.subject')</label>
                        <input type="text" class="form-control" id="edit-email-subject-input" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email-content-input">@lang('app.content')</label>
                      <div id="editor-container">
                        <!-- <div id="edit-editor" style="height: 300px;"></div> -->
                      </div>
                        <input type="hidden" id="edit-email-content-input" name="content" required>
                    </div>
                    <button id="edit-email-button" type="submit" class="btn btn-primary">@lang('app.update') </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.list-group-item').on('click', function() {
            // Get the text of the list-group-item excluding the span text
            const emailSubject = $(this).clone() // Clone the element
                .children('span') // Select the children with the span
                .remove() // Remove the span elements
                .end() // Return to the cloned element
                .text().trim(); // Get the text content
            const emailContent = $(this).next('input[type="hidden"]').val();;

            $('#email-subject').text(emailSubject);
            $('#email-content').html(emailContent);
            $('#emailDetailModal').modal('show');
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

        $('body').on('click', '.edit-email-template', function() {

            var templateId = $(this).data('edit-template-id');
            $.ajax({
                url: "{{ route('edit_company_email_template', ':id') }}".replace(":id", templateId),
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#edit-template-id').val(response.data.id);
                        $('#edit-email-template-name-input').val(response.data.name);
                        $('#edit-email-subject-input').val(response.data.subject);
                        // Set the content of the editor
                        const editorContainer = document.getElementById('editor-container');
                        editorContainer.innerHTML = '<div id="edit-editor" style="height: 300px;"></div>';
                        var editor = new Quill('#edit-editor', {
                            theme: 'snow',
                        });
                        // editor.root.innerHTML = response.data.content;
                        editor.clipboard.dangerouslyPasteHTML(response.data.content);
                        $('#edit-email-isautoreply-template-hidden').val(response.data.autoreply_template);
                        $('#edit-email-isautoreply-template-input').prop('checked', response.data.autoreply_template == 1);
                        $('#editCompanyEmailTemplateModal').modal('show');
                    } else {
                        Swal.fire({
                            title: "Error!!",
                            text: response.message,
                            icon: "error"
                        });
                    }
                }
            });
        });

        $('#compose-email-button').on('click', function(e) {
            e.preventDefault();

            // Get HTML content from Quill editor
            var htmlContent = quill.root.innerHTML;
            // console.log(htmlContent,'htmlContent');
            // return
            $('#email-content-input').val(htmlContent);

            // Set the value of the hidden input based on the checkbox state
            if ($('#email-isautoreply-template-input').is(':checked')) {
                $('#email-isautoreply-template-hidden').val(1);
            } else {
                $('#email-isautoreply-template-hidden').val(0);
            }

            // Prepare form data
            var content_data = escape(htmlContent);
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('name', $("#email-template-name-input").val());
            formData.append('subject', $("#email-subject-input").val());
            formData.append('content', content_data );
            formData.append('autoreply_template', $("#email-isautoreply-template-hidden").val());
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Send AJAX request
            $.ajax({
                url: "{{ route('save_company_email_template') }}",
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
                    } else {
                        Swal.fire({
                            title: "Success!!",
                            text: response.message,
                            icon: "success"
                        });

                        $('#addCompanyEmailTemplateModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    alert('An error occurred while sending the email.');
                    console.error(xhr.responseText);
                }
            });
        });

        // Update template
        $('#edit-email-button').on('click', function(e) {
            e.preventDefault();

            var htmlContent = $('#edit-editor').html();
            $('#edit-email-content-input').val(htmlContent);

            // Check if the checkbox is checked
            var isAutoReplyTemplate = $('#edit-email-isautoreply-template-input').is(':checked') ? 1 : 0;
            $('#edit-email-isautoreply-template-hidden').val(isAutoReplyTemplate);
            var content_data = escape(htmlContent);
            var formData = new FormData($('#edit-email-form')[0]);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('content_updated', content_data );

            // Get the template ID
            var templateId = $('#edit-template-id').val();

            // Update the AJAX URL with the template ID
            var url = "{{ route('update_company_email_template', ':id') }}";
            url = url.replace(':id', templateId);

            $.ajax({
                url: url,
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
                    } else {
                        Swal.fire({
                            title: "Success!!",
                            text: response.message,
                            icon: "success"
                        });
                        $('#editCompanyEmailTemplateModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while updating the template.');
                    console.error(xhr.responseText);
                }
            });
        });
    });

    /* delete email-template */
    $('body').on('click', '.delete-email-template', function() {
        var id = $(this).data('template-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.removeAgentText')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('delete-company-email-template', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('.row' + id).fadeOut(100);
                            location.reload();
                        }
                    }
                });
            }
        });
    });

    /* open add agent modal */
    $('body').off('click', "#addTemplate").on('click', '#addTemplate', function() {
        $('#addCompanyEmailTemplateModal').modal('show');
        // $.ajaxModal("addCompanyEmailTemplateModal", url);
    });
</script>
