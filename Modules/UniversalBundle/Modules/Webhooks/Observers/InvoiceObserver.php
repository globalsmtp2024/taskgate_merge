<?php

namespace Modules\Webhooks\Observers;

use App\Models\Invoice;
use Modules\Webhooks\Jobs\SendWebhook;

class InvoiceObserver
{

    public function created(Invoice $invoice)
    {
        SendWebhook::dispatch($invoice->toArray(), 'Invoice', $invoice->company_id)
            ->delay(5)
            ->onQueue('default');
    }

}
