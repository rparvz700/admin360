<script>
    (function() {
        const taxableAreaSft = parseFloat(@json($taxableAreaSft ?? 150)) || 150;
        const vatPercent = parseFloat(@json(optional($vatTax ?? null)->vat ?? 0)) || 0;
        const taxPercent = parseFloat(@json(optional($vatTax ?? null)->tax ?? 0)) || 0;
        const agreementAreas = @json($agreementAreas ?? []);
        const wizardAreaFields = {
            floor_area: '[name="floor_area_sft"]',
            car_parking: '[name="car_parking"]',
            dg_space: '[name="dg_space_sft"]',
            store_space: '[name="store_space_sft"]'
        };

        function money(value) {
            const number = parseFloat(value);
            return Number.isFinite(number) ? number : 0;
        }

        function fixed(value) {
            return money(value).toFixed(2);
        }

        function setComponentAreasFromAgreement(agreementId) {
            if (!agreementId || !agreementAreas[agreementId]) {
                return;
            }

            Object.entries(agreementAreas[agreementId]).forEach(function([type, area]) {
                $('[data-rent-component="' + type + '"] .rc-area').val(area ? fixed(area) : '');
            });

            refreshRentComponents();
        }

        function refreshVisibleComponentRows() {
            let visibleCount = 0;

            $('.rent-components-table tbody tr').each(function() {
                const row = $(this);
                const area = money(row.find('.rc-area').val());
                const rent = money(row.find('.rc-rent').val());
                const visible = area > 0 || rent > 0;

                row.toggleClass('d-none', !visible);
                if (visible) {
                    visibleCount++;
                }
            });

            $('.rent-components-empty').toggleClass('d-none', visibleCount > 0);
            $('.rent-components-table').toggleClass('d-none', visibleCount === 0);
        }

        function hasAnyComponentArea() {
            let hasArea = false;

            $('.rent-components-table .rc-area').each(function() {
                if (money($(this).val()) > 0) {
                    hasArea = true;
                    return false;
                }
            });

            return hasArea;
        }

        function syncWizardAreas() {
            Object.entries(wizardAreaFields).forEach(function([type, selector]) {
                const input = document.querySelector(selector);
                if (!input) {
                    return;
                }

                $('[data-rent-component="' + type + '"] .rc-area').val(input.value ? fixed(input.value) : '');
            });

            refreshRentComponents();
        }

        window.refreshRentComponents = function() {
            let baseRent = 0;
            let vatTotal = 0;
            let taxTotal = 0;

            $('.rent-components-table tbody tr').each(function() {
                const row = $(this);
                const area = money(row.find('.rc-area').val());
                const rent = money(row.find('.rc-rent').val());
                const taxable = area >= taxableAreaSft;
                const vat = taxable ? (rent * vatPercent) / 100 : 0;
                const tax = taxable ? (rent * taxPercent) / 100 : 0;
                const total = rent + vat + tax;

                row.find('.rc-vat').val(vat > 0 ? fixed(vat) : '');
                row.find('.rc-tax').val(tax > 0 ? fixed(tax) : '');
                row.find('.rc-total').val(total > 0 ? fixed(total) : '');
                row.find('.rc-tax-badge')
                    .toggleClass('bg-success', taxable)
                    .toggleClass('bg-secondary', !taxable)
                    .text(taxable ? 'Yes' : 'No');

                baseRent += rent;
                vatTotal += vat;
                taxTotal += tax;
            });

            $('#base_rent').val(fixed(baseRent)).trigger('input');
            $('#rent_vat_total').val(fixed(vatTotal));
            $('#rent_tax_total').val(fixed(taxTotal));
            refreshVisibleComponentRows();
        };

        $(document).on('input', '.rc-area, .rc-rent', refreshRentComponents);
        $(document).on('change', '#agreement_id', function() {
            setComponentAreasFromAgreement($(this).val());
        });
        $(document).on('input', '[name="floor_area_sft"], [name="car_parking"], [name="dg_space_sft"], [name="store_space_sft"]', syncWizardAreas);

        $(function() {
            if ($('#agreement_id').length && !hasAnyComponentArea()) {
                setComponentAreasFromAgreement($('#agreement_id').val());
            }

            syncWizardAreas();
            refreshRentComponents();
        });
    })();
</script>
