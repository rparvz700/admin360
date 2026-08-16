@php
    $firstBill = $bills->first();
    $billType = $firstBill ? $firstBill->bill_type : 'postpaid';
@endphp

@if ($billType === 'postpaid')
    @include('FacilitiesManagement.Electricity.Bills.bulk_print_postpaid')
@else
    @include('FacilitiesManagement.Electricity.Bills.bulk_print_prepaid')
@endif
