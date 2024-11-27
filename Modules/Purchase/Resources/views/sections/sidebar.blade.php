@php
    $purchaseViewVendorPermission = user()->permission('view_vendor');
    $purchaseViewOrderPermission = user()->permission('view_purchase_order');
    $purchaseViewBillPermission = user()->permission('view_bill');
    $purchaseViewCreditPermission = user()->permission('view_vendor_credit');
    $purchaseViewInventoryPermission = user()->permission('view_inventory');
    $purchaseViewOrderReportPermission = user()->permission('view_order_report');
    $purchaseViewPaymentPermission = user()->permission('view_vendor_payment');
@endphp
@if (in_array(\Modules\Purchase\Entities\PurchaseManagementSetting::MODULE_NAME, user_modules()) && ($purchaseViewVendorPermission != 'none' || $purchaseViewOrderPermission != 'none' || $purchaseViewBillPermission != 'none'
|| $purchaseViewCreditPermission != 'none' || $purchaseViewInventoryPermission != 'none' || $purchaseViewOrderReportPermission != 'none' || $purchaseViewPaymentPermission != 'none'))

    <x-menu-item icon="wallet" :text="__('purchase::app.menu.purchase')" :addon="App::environment('demo')">
        <x-slot name="iconPath">
            <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
            <path
                d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
        </x-slot>

        <div class="accordionItemContent pb-2">

            <!-- NAV ITEM - VENDORS -->
            <x-sub-menu-item :link="route('vendors.index')"
                            :text="__('purchase::app.menu.vendor')"
                            :permission="($purchaseViewVendorPermission != 'none' && $purchaseViewVendorPermission != '')"
            />

            <!-- NAV ITEM - PRODUCTS -->
            @if (in_array('products', user_modules()) && $sidebarUserPermissions['view_product'] != 5 && $sidebarUserPermissions['view_product'] != 'none')
               <x-sub-menu-item :link="route('purchase-products.index')" :text="__('purchase::app.menu.products')" />
            @endif

            <!-- NAV ITEM - ORDERS -->
            <x-sub-menu-item :link="route('purchase-order.index')"
                            :text="__('purchase::app.menu.purchaseOrder')"
                            :permission="($purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != '')"
            />

            <!-- NAV ITEM - BILLS -->
            <x-sub-menu-item :link="route('bills.index')"
                            :text="__('purchase::app.menu.bills')"
                            :permission="($purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != '')"
            />

            <!-- NAV ITEM - PAYMENTS -->
            <x-sub-menu-item :link="route('vendor-payments.index')"
                            :text="__('purchase::app.purchaseOrder.vendorPayments')"
                            :permission="($purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != '')"
            />

            <x-sub-menu-item :link="route('vendor-credits.index')"
                            :text="__('purchase::app.menu.vendorCredits')"
                            :permission="($purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != '')"
            />

            <!-- NAV ITEM - INVENTORY -->
            <x-sub-menu-item :link="route('purchase-inventory.index')" :text="__('purchase::app.menu.inventory')"
            :permission="($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '')"
            />

            <!-- NAV ITEM - REPORTS -->
            <x-sub-menu-item :link="route('reports.index')" :text="__('purchase::app.menu.reports')"
            :permission="($purchaseViewOrderReportPermission != 'none' && $purchaseViewOrderReportPermission != '')"
            />

        </div>

    </x-menu-item>

@endif
