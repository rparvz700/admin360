<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $rentBases = DB::table('rent_base')
            ->whereNotNull('invoice_id')
            ->get();

        foreach ($rentBases as $rent) {
            $invoice = DB::table('invoices')->where('id', $rent->invoice_id)->first();
            if (!$invoice) {
                continue;
            }

            $billingMonth = $invoice->billing_month;
            if (!$billingMonth && $invoice->invoice_date) {
                $billingMonth = Carbon::parse($invoice->invoice_date)->format('Y-m');
                DB::table('invoices')->where('id', $invoice->id)->update([
                    'billing_month' => $billingMonth,
                ]);
            }

            if ($billingMonth) {
                DB::table('rent_invoices')->updateOrInsert(
                    [
                        'rent_base_id'  => $rent->id,
                        'billing_month' => $billingMonth,
                    ],
                    [
                        'invoice_id' => $invoice->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Reversible if needed
        DB::table('rent_invoices')->truncate();
    }
};
