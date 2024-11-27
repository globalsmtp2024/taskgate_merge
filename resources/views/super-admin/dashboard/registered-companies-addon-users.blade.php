<div class="card bg-white border-0 b-shadow-4">
    <div class="card-header bg-white border-0 text-capitalize d-flex justify-content-between pt-4">
        <h4 class="f-18 f-w-500 mb-0">All Companies Addon User's</h4>
    </div>
    <div class="card-body p-0 h-200" style="overflow: auto;
    height: 240px;">
        <div class="table-responsive">
            <x-table class="border-0 mb-0 admin-dash-table table-hover">
                <x-slot name="thead">
                    <th class="pl-20">#</th>
                    <th>@lang('app.name')</th>
                    <th>@lang('superadmin.packages.packages')</th>
                    <th>@lang('Addon Users')</th>
                    <th>@lang('Addon Users Amount')</th>
                </x-slot>
                @forelse($RegisteredCompaniesAddonusers as $key=>$item)
                    <tr id="row-{{ $item->id }}">
                        <td class="pl-20">{{ $key + 1 }}</td>
                        <td>
                            <x-company :company="$item" />
                        </td>
                        <td>
                            {{ ($item->package ? $item->package->name : '--') . ($item->package->default != 'trial' ? ' (' . $item->package_type . ')' : '') }}
                        </td>
                        <td>{{ $item->addon_users }}</td>
                        <td>{{ $item->addon_amount }}</td>
                    </tr>
                @empty
                    <x-cards.no-record-found-list colspan="5" />
                @endforelse
            </x-table>
        </div>
    </div>
</div>
