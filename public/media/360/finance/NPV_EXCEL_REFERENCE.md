# Outflow.xlsx — Agent-Readable Calculation Reference

## Purpose
This document translates the Excel workbook `outflow.xlsx` into a readable description of its structure and formulas. It contains one worksheet: `2284`, representing a lease/rent cash-outflow, present-value, lease-obligation, PPE/depreciation, and advance/security-deposit adjustment schedule.

> Important: In this workbook, the row labelled **NPV** is effectively the **present value of future lease cash outflows**, calculated month by month using a discount factor. An implementation should preserve this business meaning unless stakeholders confirm that a broader investment-style NPV is intended.

## 1. Core Inputs

| Excel cell/area | Meaning | Example in workbook |
|---|---|---|
| C2/D2 | Counterparty/property identifiers | 2284 / Laxmipur BB POP |
| E4 | Agreement date | 2025-09-01 |
| E5 | Expiry date | 2035-08-31 |
| E6 | Remaining term | `DATEDIF(E4,E5,"m")+1` |
| E7 | Base date | 2025-11-01 |
| E8 | Months to calculate | `DATEDIF(E7,E5,"m")+1` |
| E9 | Annual interest rate | 12.16% |
| E10 | Monthly interest rate | `(1+annual_rate)^(1/12)-1` |
| B12/C12 | Office area / net office rent | 343+396 sq ft / 14,000 |
| B13/C13 | DG & Battery room area / net rent | optional |
| C15/D15 | Tax top-up flags | boolean |
| E18 | Tax rate | 10% |
| E19 | VAT rate | 15% |
| E20 | Rent increment interval | 3 years |
| E21 | Increment counting date | agreement date |
| C22:C28 | Increment percentages | 10% per configured increment |
| D22:D28 | Increment sequence/enablement | 1..7 where populated |
| E22:E28 | Increment effective dates | previous date + interval |
| E29 | Security deposit | 40,000 |
| C29 | Security deposit enabled | TRUE/FALSE |
| E31 | Advance amount | 360,000 |
| E32 | Advance adjustment start date | 2025-09-01 |
| E33 | Advance adjustment months | 120 |
| E34 | Advance adjustment end date | `EDATE(E32,E33)-1` |

## 2. Rent Gross-Up Logic

### Office gross rent — C16
The office net rent is converted to gross rent depending on area and tax/VAT rules.

Conceptually:

```text
if office_area > 150:
    gross = net_rent
    if tax_top_up: gross = gross / (1 - tax_rate)
    gross = gross * (1 + vat_rate)
else:
    gross = net_rent
    if tax_top_up: gross = gross / (1 - tax_rate)
```

The workbook stores the calculated office gross rent in C16 and combines C16 + D16 in E16.

### DG & Battery room gross rent — C17
Same general gross-up logic using C13, B13 and C15.

## 3. Rent Increment Logic

Rows 22–28 define future rent increments.

For each increment:
- C row = increment percentage
- D row = increment sequence/activation
- E row = effective date
- F row = illustrative calculated increased rent

The monthly cash-flow formulas check the current payment date against increment effective dates. If an increment is active, rent is compounded through all increments that have become effective.

Conceptually:

```text
rent_for_month = base_gross_rent

for each configured_increment ordered by effective date:
    if payment_date >= increment.effective_date:
        rent_for_month *= (1 + increment.percent)
```

The Excel implementation uses nested `IF(AND(...))` formulas rather than a loop.

## 4. Monthly Timeline

Rows 40–43 create the monthly calculation timeline.

- Row 40: running calculation/base month
- Row 41: month-end/payment month
- Row 42: installment/month number
- Row 43: discount factor

Starting from the base date, the schedule generates one month at a time until `Months to calculate` is reached.

### Discount factor

For month `n`:

```text
discount_factor(n) = 1 / (1 + monthly_interest_rate)^n
```

Excel formula pattern:

```text
=IFERROR(1/(1+$E$10)^month_number,0)
```

## 5. Cash Outflow Schedule

### Row 44 — Cash outflow: Office
Each monthly office payment is based on:
1. Base gross office rent
2. Applicable compounded rent increments
3. Advance/security-deposit adjustments from row 62

The first months may include special initial amounts. The formulas then generate the recurring rent for each month.

### Row 45 — Cash outflow: DG & Battery room
Uses the same increment-driven concept for DG/Battery room rent. The current sample contains zero/no active amount.

## 6. Present Value / "NPV" Calculation

### Row 46
Each monthly cash outflow is discounted:

```text
monthly_present_value =
    (office_cash_outflow + dg_battery_cash_outflow)
    * discount_factor
```

The initial period may use the undiscounted initial cash outflow.

The total value is the sum of the discounted monthly cash flows.

Conceptually:

```text
NPV_or_PV = Σ cash_outflow[n] / (1 + monthly_interest_rate)^n
```

The workbook labels this result as `NPV`.

### Important implementation note
Because all scheduled values in this workbook are lease/rent **outflows**, this is closer to a **present value of lease payments / lease liability calculation** than a traditional investment NPV containing both positive inflows and negative outflows.

If the Property Management feature will calculate investment profitability from rent **income**, the implementation should distinguish:
- Rent income / inflows
- Lease or operating outflows
- Net cash flow
- Discounted net cash flow
- Final NPV

Do not assume the Excel's row-46 result alone is the complete business definition for the new feature.

## 7. Lease Obligation Schedule

Rows 49–52 create a lease-liability amortization schedule.

### Opening lease obligation
First opening balance:

```text
opening_obligation = total_present_value - initial_period_value
```

Later months:

```text
opening_obligation[current] = closing_obligation[previous]
```

### Finance expense

```text
finance_expense = opening_obligation * monthly_interest_rate
```

### Lease payment

```text
lease_payment =
    office_cash_outflow
    + dg_battery_cash_outflow
    - finance_expense
```

### Closing lease obligation

```text
closing_obligation =
    opening_obligation - lease_payment
```

## 8. PPE Movement and Depreciation

Rows 57–59 track a right-of-use/PPE style balance.

### Opening balance
Initial opening balance = total present value.

Subsequent months carry forward previous WDV.

### Depreciation

```text
monthly_depreciation =
    initial_opening_balance / months_to_calculate
```

The workbook keeps depreciation constant each month.

### WDV

```text
wdv = opening_balance - depreciation
```

## 9. Advance and Security Deposit Adjustment

Rows 61–63 track adjustments to the office cash-flow/advance balance.

### Opening balance
The first opening balance starts from the initial office cash outflow.

Later months carry forward the previous closing balance.

### Adjustment
The workbook may:
- Spread the advance amount across the configured adjustment period.
- Apply adjustment only between the configured start and end dates.
- Return the security deposit at expiry when the security-deposit flag is enabled.
- Include a special hard-coded first-month adjustment in the sample (`-3000*3`).

Conceptually:

```text
advance_adjustment =
    advance_amount / adjustment_months
    only when current_date is inside adjustment_period

security_deposit_adjustment =
    security_deposit
    only at expiry/payment month when enabled
```

The workbook stores these as negative adjustments.

### Closing balance

```text
closing_balance = opening_balance + adjustment
```

## 10. Calculation Flow

```text
Lease / Property Inputs
        |
        v
Agreement Dates + Base Date
        |
        v
Remaining Calculation Months
        |
        v
Net Rent
        |
        +--> Tax/VAT Gross-up
        |
        v
Gross Rent
        |
        +--> Scheduled Rent Increments
        |
        v
Monthly Cash Outflows
        |
        +--> Advance Adjustment
        +--> Security Deposit Adjustment
        |
        v
Monthly Net Lease Cash Flow
        |
        +--> Monthly Discount Factor
        |
        v
Present Value / NPV Total
        |
        +-------------------+--------------------+
        v                   v                    v
Lease Obligation      PPE / Depreciation    Advance Balance
```

## 11. Recommended System Data Model Mapping

The coding agent should investigate the existing Property Management module and map these concepts to real tables/models:

| Excel concept | Required system data |
|---|---|
| Agreement date | Lease/rental agreement start date |
| Expiry date | Lease/rental agreement end date |
| Base date | Calculation/reporting date |
| Net rent | Contracted monthly rent |
| Office/DG components | Property/unit/space or rent component |
| Tax top-up | Contract/tax configuration |
| Tax rate | Tax configuration |
| VAT rate | VAT configuration |
| Increment interval | Rent escalation rule |
| Increment percentage | Rent escalation percentage |
| Increment effective date | Calculated or stored escalation date |
| Security deposit | Deposit/payment record |
| Advance amount | Advance payment record |
| Advance adjustment period | Contract/payment adjustment rule |
| Discount rate | Finance/accounting configuration |
| Payment timeline | Contract schedule or generated monthly schedule |

## 12. Recommended Implementation Approach

Do not reproduce the Excel as hundreds of hard-coded conditional statements.

Use a normalized calculation pipeline:

```text
LeaseAgreement / Property
        |
        v
Load contract + rent components + financial configuration
        |
        v
Generate monthly schedule
        |
        +--> calculate base/gross rent
        +--> apply active increments
        +--> apply advance adjustments
        +--> apply security deposit events
        |
        v
Monthly Cash Flow[]
        |
        v
Calculate Discount Factor
        |
        v
Present Value / NPV
        |
        +--> Lease liability schedule
        +--> Finance expense schedule
        +--> Depreciation schedule
```

Suggested service responsibilities:

```text
RentScheduleService
    - generate monthly periods
    - determine rent components
    - apply escalation/increments

CashFlowAdjustmentService
    - advance adjustment
    - security deposit adjustment
    - other special adjustments

DiscountingService
    - convert annual rate to monthly rate
    - calculate discount factors
    - calculate present value

LeaseCalculationService
    - orchestrate the full calculation
    - produce detailed monthly results
    - calculate total present value/NPV

LeaseObligationService
    - opening balance
    - finance expense
    - principal/lease payment
    - closing balance

DepreciationService
    - opening ROU/PPE balance
    - monthly depreciation
    - WDV
```

## 13. Items Requiring Business Clarification

Before implementation, confirm:

1. Is the requested system feature a traditional investment **NPV** based on rental income and all property cash flows, or an **IFRS/lease present-value calculation** matching this workbook?
2. Why is the current workbook named `outflow.xlsx` and labelled NPV when it contains only cash outflows?
3. Which Excel inputs should come automatically from Property Management?
4. Should discount/interest rates be global, per property, per agreement, or manually selected?
5. Are rent increments always fixed-interval, or can contracts define arbitrary effective dates?
6. Are tax and VAT always calculated with the current area-based condition (`area > 150`)?
7. How should special hard-coded initial adjustments such as `-3000*3` be represented in the system?
8. Should the system calculate actual collected rent, contractual expected rent, or projected future rent?
9. Should the output include lease obligation and depreciation schedules, or only NPV?
10. What events besides rent, advance, and deposit should affect the cash flow?
