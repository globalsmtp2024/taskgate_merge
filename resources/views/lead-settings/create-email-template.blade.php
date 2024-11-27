<div class="modal fade" id="addLeadEmailTemplateModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('app.addNew') @lang('Email Template')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <form id="company-email-template-form-lead">
                        <input type="hidden" name="company_id" value="{{ company()->id }}">
                        <div class="form-group">
                            <label for="email-template-name-input">Name</label>
                            <input type="text" class="form-control" id="email-template-name-input" name="name"
                                required>
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
                        {{-- <div class="form-group">
                <label for="email-file">Attachment (Max 5MB)</label>
                <input type="file" class="form-control-file" id="email-file" name="attachment"
                    accept=".pdf,.doc,.docx,.txt" required>
                <small class="form-text text-muted">Supported file types: .pdf, .doc, .docx, .txt</small>
            </div> --}}
                        <button id="save-company-email-template-lead" type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Compose your email template content...',
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

        $('#company-email-template-form-lead').on('submit', function(e) {
            e.preventDefault();

            // Get HTML content from Quill editor
            var htmlContent = quill.root.innerHTML;
            $('#email-content-input').val(htmlContent);


            // Prepare form data
            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            // Send AJAX request
            $.ajax({
                url: '{{ route('company_lead_email_template_save') }}',
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

                        $('#composeModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    alert('An error occurred while sending the email.');
                    console.error(xhr.responseText);
                }
            });
        });

    });
</script>
