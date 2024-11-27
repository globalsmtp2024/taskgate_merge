@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        {{-- WORKSUITESAAS --}}
        @if (user()->is_superadmin)
            <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
        @else
            <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
        @endif

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">Subdomain
                        Settings</h2>
                </div>
            </x-slot>

            <div class="s-b-n-content">

                <div class="d-flex flex-wrap justify-content-between">

                    <div class="col-xl-6 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                        <h4 class="f-16 f-w-500 text-dark-grey mb-20">@lang('subdomain::app.core.bannedSubdomains')</h4>

                        <form class="ajax-form mt-5" method="POST" id="editSettings">
                            @csrf
                            @method('PUT')
                            <div class="input-group">

                                <input type="text" name="banned_subdomain" id="banned_subdomain"
                                       autocomplete="off" class="form-control height-35 f-14">

                                <div class="input-group-append">
                                    <span
                                        class="input-group-text f-14 bg-white-shade text-bold"> .{{ getDomain() }} </span>
                                </div>

                            </div>
                        </form>
                        <div class="mt-5 text-muted">
                            <h4 class="f-16 f-w-500 text-dark-grey mb-20"> @lang('subdomain::app.match.title')</h4>
                            @lang('subdomain::app.match.pattern')

                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-12 col-md-12 ntfcn-tab-content-right border-left-grey p-4">
                        <h4
                            class="f-16 text-capitalize f-w-500 text-dark-grey">Banned Subdomain List</h4>
                        <x-table class="table table-bordered border-0">
                            <x-slot name="thead">
                                <thead>
                                <tr>
                                    <th>Subdomains</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                                </thead>
                            </x-slot>
                            <tbody>

                            @if(isset($bannedSubDomains))

                                @forelse($bannedSubDomains as $key => $subdomains)
                                    <tr>
                                        <td>{{ $subdomains.'.'.getDomain() }} </td>
                                        <td>
                                            <div class="task_view mt-1 mt-lg-0 mt-md-0">
                                                <a href="javascript:;" data-key-id="{{$key}}"
                                                   class="sa-params task_view_more d-flex align-items-center justify-content-center">
                                                    <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-cards.no-record-found-list/>
                                @endforelse
                            @else
                                <x-cards.no-record-found-list/>
                            @endif
                            </tbody>
                        </x-table>
                    </div>
                </div>
            </div>

            <!-- Buttons Start -->
            <div class="w-100 border-top-grey set-btns">
                <x-setting-form-actions>
                    <x-forms.button-primary id="save-form" icon="check">@lang('app.save')</x-forms.button-primary>
                </x-setting-form-actions>
            </div>
            <!-- Buttons End -->
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')

    <script>
        // change task Setting For Setting
        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('super-admin.post.banned-subdomains')}}',
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#editSettings').serialize()
            })
        });

        $(".add-more").click(function () {
            $(".form-entries>.row")
                .last()
                .clone()
                .appendTo($(".form-entries"))
                .find("input").attr("name", function (i, oldVal) {
                return oldVal.replace(/\[(\d+)\]/, function (_, m) {
                    return "[" + (+m + 1) + "]";
                });
            });
            return false;
        });


        $('body').on('click', '.sa-params', function () {
            const keyIndex = $(this).data('key-id');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deleteField')",
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

                    let url = "{{ route('super-admin.banned-subdomains.destroy',':keyIndex') }}";
                    url = url.replace(':keyIndex', keyIndex);
                    const token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                    });
                }
            });
        });
    </script>

@endpush
