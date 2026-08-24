<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubcenterController;
use App\Http\Controllers\Admin\TableSettingController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\FacilitiesManagement\BuildingsController;
use App\Http\Controllers\FacilitiesManagement\FloorsController;
use App\Http\Controllers\FacilitiesManagement\DashboardController;
use App\Http\Controllers\FacilitiesManagement\AgreementsController;
use App\Http\Controllers\FacilitiesManagement\Rent\RentController;
use App\Http\Controllers\FacilitiesManagement\UtilityTypeController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetCategoryController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\AssetAttributeController;
use App\Http\Controllers\FacilitiesManagement\AssetManagement\ProjectController;
use App\Http\Controllers\VehicleManagement\DriverController;

use App\Http\Controllers\GenericDocumentManagement\GenericDocumentController;

use App\Http\Controllers\TicketManagement\TicketController;
use App\Http\Controllers\TicketManagement\AdminTicketController;
use App\Http\Controllers\TicketManagement\VehicleAssignmentController;

use App\Http\Controllers\VendorController;
use App\Http\Controllers\VehicleMaintenanceManagement\VehicleMaintenanceController;
use App\Http\Controllers\VehicleMaintenanceManagement\VehiclePartController;
use App\Http\Controllers\VehicleMaintenanceManagement\VehicleOperationalLogController;
use App\Http\Controllers\InvoiceManagement\InvoiceController;
use App\Http\Controllers\InvoiceManagement\RentInvoiceController;
use App\Http\Controllers\InvoiceManagement\VehicleInvoiceController;
use App\Http\Controllers\InvoiceManagement\InvoiceDashboardController;
use App\Http\Controllers\InvoiceManagement\VatTaxController;
use App\Http\Controllers\VehicleMaintenanceManagement\MaintenanceReportController;
use App\Http\Controllers\FacilitiesManagement\Electricity\RioController;
use App\Http\Controllers\FacilitiesManagement\Electricity\ElectricityMeterController;
use App\Http\Controllers\FacilitiesManagement\Electricity\ElectricityMeterNocController;
use App\Http\Controllers\FacilitiesManagement\Electricity\ElectricityBillController;
use App\Http\Controllers\FacilitiesManagement\Electricity\ElectricityReportController;

use App\Http\Controllers\FacilitiesManagement\NpvAnalysisController;
use App\Http\Controllers\Admin\FinanceSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyWizardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::middleware(['auth'])->group(function(){
    // Vehicle Management: Vehicle Documents CRUD
    //Route::resource('vehicle-documents', App\Http\Controllers\VehicleManagement\VehicleDocumentController::class);
    //Route::get('vehicle-documents-list', [App\Http\Controllers\VehicleManagement\VehicleDocumentController::class, 'list'])->name('vehicle-documents.list');
    // Vehicle Management: Vehicle Document Attributes CRUD
    // Route::get('vehicle-document-attributes/list', [\App\Http\Controllers\VehicleManagement\VehicleDocumentAttributeController::class, 'list'])->name('vehicle-document-attributes.list');
    // Route::resource('vehicle-document-attributes', \App\Http\Controllers\VehicleManagement\VehicleDocumentAttributeController::class);
    // // Vehicle Management: Vehicle Document Categories CRUD
    // Route::get('vehicle-document-categories/list', [\App\Http\Controllers\VehicleManagement\VehicleDocumentCategoryController::class, 'list'])->name('vehicle-document-categories.list');
    // Route::resource('vehicle-document-categories', \App\Http\Controllers\VehicleManagement\VehicleDocumentCategoryController::class);
    // Vehicle Management: Vehicles CRUD
    Route::get('vehicles/list', [\App\Http\Controllers\VehicleManagement\VehicleController::class, 'list'])->name('vehicles.list');
    Route::resource('vehicles', \App\Http\Controllers\VehicleManagement\VehicleController::class);
    // Vehicle Management: Vehicle Types CRUD
    Route::get('vehicle-types-list', [\App\Http\Controllers\VehicleManagement\VehicleTypeController::class, 'list'])->name('vehicle-types.list');
    Route::resource('vehicle-types', \App\Http\Controllers\VehicleManagement\VehicleTypeController::class);
    // Vehicle Management: Import drivers from external API
    Route::get('drivers/import/api', [\App\Http\Controllers\VehicleManagement\DriverController::class, 'importFromApi'])->name('drivers.import.api');
    // Vehicle Management DataTables route
    Route::get('drivers-list', [\App\Http\Controllers\VehicleManagement\DriverController::class, 'list'])->name('drivers.list');
    Route::get('/', [DashboardController::class, 'index']);

    Route::resources([
        'roles' => RoleController::class,
        'users' => UserController::class,
        'departments' => DepartmentController::class,
        'subcenters' => SubcenterController::class,
        'buildings' => BuildingsController::class,
        'floors' => FloorsController::class,
        'agreements' => AgreementsController::class,
        'rent' => RentController::class,
        'assets' => AssetController::class,
        'projects' => ProjectController::class,
        'asset-categories' => AssetCategoryController::class,
        'asset-attributes' => AssetAttributeController::class,
        'utility-types' => UtilityTypeController::class,
    ]);

    // Electricity Module Routes
    Route::prefix('facilities-management/electricity')->name('electricity.')->group(function () {
        // RIO Management
        Route::resource('rios', RioController::class)->except(['create', 'show', 'edit', 'destroy']);
        Route::post('rios/{rio}/assign-users', [RioController::class, 'assignUsers'])->name('rios.assign-users');

        // Meters Master & NOCs
        Route::get('meters/building/{building_id}/agreement-vendor', [ElectricityMeterController::class, 'getAgreementVendor'])->name('meters.building.agreement-vendor');
        Route::resource('meters', ElectricityMeterController::class);
        Route::get('meters/{meter}/nocs', [ElectricityMeterNocController::class, 'index'])->name('meters.nocs.index');
        Route::post('meters/{meter}/nocs', [ElectricityMeterNocController::class, 'store'])->name('meters.nocs.store');
        Route::delete('meters/nocs/{noc}', [ElectricityMeterNocController::class, 'destroy'])->name('meters.nocs.destroy');

        // Bills & Requisitions
        Route::get('bills/bulk/print', [ElectricityBillController::class, 'bulkPrint'])->name('bills.bulk-print');
        Route::resource('bills', ElectricityBillController::class);
        Route::get('bills/{bill}/print', [ElectricityBillController::class, 'printSheet'])->name('bills.print');
        Route::post('bills/{bill}/pay', [ElectricityBillController::class, 'markAsPaid'])->name('bills.pay');
        Route::get('previous-reading/{meterId}', [ElectricityBillController::class, 'getPreviousReading'])->name('bills.previous-reading');

        // Reports
        Route::get('reports', [ElectricityReportController::class, 'index'])->name('reports.index');
    });

    // Asset
    Route::get('assets/{id}/history', [AssetController::class, 'getHistory'])->name('assets.history');
    // Agreement
    Route::get('agreements/{id}/history', [AgreementsController::class, 'getHistory'])->name('agreements.history');
    // Rent
    Route::get('rent/{id}/history', [RentController::class, 'getHistory'])->name('rent.history');

    // Vehicle Management
    Route::resource('drivers', DriverController::class);
    Route::get('rent-list', [RentController::class, 'list'])->name('rent.list');
    Route::get('floors-list', [FloorsController::class, 'list'])->name('floors.list');
    Route::get('floors/{floor}', [FloorsController::class, 'show'])->name('floors.show');
    Route::get('floors/{id}/history', [FloorsController::class, 'getHistory'])->name('floors.history');
    Route::get('buildings-list', [BuildingsController::class, 'list'])->name('buildings.list');
    //-----------------Subcenter extra routes---------------
    Route::get('subcenter-list', [SubcenterController::class, 'subcenterList'])->name('subcenterList');
    //-----------------User extra routes--------------------
    Route::get('user-list', [UserController::class, 'userList'])->name('userList');
    // Facilities Management Dashboard
    Route::get('facilities/dashboard', [DashboardController::class, 'index'])->name('facilities.dashboard');

    // Net Present Value (NPV) Calculation & Reporting Routes
    Route::prefix('facilities/npv')->name('facilities.npv.')->group(function () {
        Route::get('/', [NpvAnalysisController::class, 'index'])->name('index');
        Route::post('/calculate', [NpvAnalysisController::class, 'calculate'])->name('calculate');
        Route::get('/export/{format?}', [NpvAnalysisController::class, 'export'])->name('export');

        // Portfolio NPV Summary Reporting Routes
        Route::get('/report', [NpvAnalysisController::class, 'report'])->name('report');
        Route::get('/report/data', [NpvAnalysisController::class, 'reportData'])->name('report.data');
        Route::get('/report/{agreementId}/detail', [NpvAnalysisController::class, 'agreementDetail'])->name('report.detail');
        Route::post('/report/refresh-cache', [NpvAnalysisController::class, 'refreshReportCache'])->name('report.refresh-cache');
    });

    // Finance Settings Routes (Discount Rate management)
    Route::prefix('admin/finance-settings')->name('admin.finance-settings.')->group(function () {
        Route::get('/', [FinanceSettingController::class, 'index'])->name('index');
        Route::post('/', [FinanceSettingController::class, 'update'])->name('update');
    });



    // Generic Document Management: Generic Documents CRUD
    Route::resource('generic-documents', App\Http\Controllers\GenericDocumentManagement\GenericDocumentController::class);
    Route::get('generic-documents-list', [App\Http\Controllers\GenericDocumentManagement\GenericDocumentController::class, 'list'])->name('generic-documents.list');
    // Generic Document Management: Generic Document Attributes CRUD
    Route::get('generic-document-attributes/list', [\App\Http\Controllers\GenericDocumentManagement\GenericDocumentAttributeController::class, 'list'])->name('generic-document-attributes.list');
    Route::resource('generic-document-attributes', \App\Http\Controllers\GenericDocumentManagement\GenericDocumentAttributeController::class);
    // Generic Document Management: Generic Document Categories CRUD
    Route::get('generic-document-categories/list', [\App\Http\Controllers\GenericDocumentManagement\GenericDocumentCategoryController::class, 'list'])->name('generic-document-categories.list');
    Route::resource('generic-document-categories', \App\Http\Controllers\GenericDocumentManagement\GenericDocumentCategoryController::class);
    Route::get('/fetch-documentable-records', [GenericDocumentController::class, 'fetchDocumentables'])
    ->name('documentable.fetch');

    Route::post('/table-settings/save', [TableSettingController::class, 'save'])->name('table_settings.save');

    Route::get('wizard/create', [PropertyWizardController::class, 'create'])->name('wizard.property.create');
    Route::post('wizard/store', [PropertyWizardController::class, 'store'])->name('wizard.property.store');

});


Auth::routes(['register' => false]);

// Document Manager Prototype Route
Route::get('document-manager/prototype', function () {
    return view('DocumentManagement.document_manager');
})->name('document-manager.prototype');


// User Ticket Routes
Route::middleware(['auth'])->prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('/create', [TicketController::class, 'create'])->name('create');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/update', [TicketController::class, 'addUpdate'])->name('addUpdate');
    Route::post('/{ticket}/cancel', [TicketController::class, 'cancel'])->name('cancel');
});

// Admin Ticket Routes
Route::middleware(['auth'])->prefix('admin/tickets')->name('admin.tickets.')->group(function () {
    Route::get('/', [AdminTicketController::class, 'index'])->name('index');
    Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('assign');
    
    Route::post('/{ticket}/update-status', [AdminTicketController::class, 'updateStatus'])->name('updateStatus');
    Route::post('/{ticket}/update', [AdminTicketController::class, 'addUpdate'])->name('addUpdate');

    // Vehicle Assignment Routes
    Route::get('/assignment/resources', [VehicleAssignmentController::class, 'getAvailableResources'])->name('assignment.resources');
    Route::post('/assignment-assign/', [VehicleAssignmentController::class, 'assignToTicket'])->name('assignment.assign');
    Route::get('/assignment/schedule', [VehicleAssignmentController::class, 'getResourceSchedule'])->name('assignment.schedule');

    Route::patch('/trip/{id}/start', [VehicleAssignmentController::class, 'tripStart'])->name('trip.start');
    Route::patch('/trip/{id}/completed', [VehicleAssignmentController::class, 'tripCompleted'])->name('trip.completed');
});


//openstreet proxy route
Route::get('/api/reverse-geocode', function (Request $request) {
    $lat = $request->lat;
    $lon = $request->lon;

    $response = Http::withHeaders([
        'User-Agent' => 'YourAppName/1.0 (contact@yourdomain.com)',
        'Accept-Language' => 'en'
    ])->get('https://nominatim.openstreetmap.org/reverse', [
        'format' => 'json',
        'lat' => $lat,
        'lon' => $lon,
    ]);

    return response()->json($response->json());
})->name('api.reverse-geocode');

Route::middleware(['auth'])->group(function () {
    // Invoices
    Route::get('invoices/dashboard', [InvoiceDashboardController::class, 'index'])->name('invoices.dashboard');

    // 1. Rent Invoices
    Route::prefix('invoices/rent')->name('invoices.rent.')->group(function () {
        Route::get('bulk-generate', [RentInvoiceController::class, 'bulkGenerateForm'])->name('bulk-generate');
        Route::post('bulk-generate/preview', [RentInvoiceController::class, 'previewBulkGenerate'])->name('bulk-generate.preview');
        Route::post('bulk-generate', [RentInvoiceController::class, 'bulkGenerate'])->name('bulk-generate.store');
        Route::get('rent-breakdown-modal/{rent}', [RentInvoiceController::class, 'rentBreakdownModal'])->name('rent-breakdown-modal');

        Route::get('/', [RentInvoiceController::class, 'index'])->name('index');
        Route::get('create', [RentInvoiceController::class, 'create'])->name('create');
        Route::post('/', [RentInvoiceController::class, 'store'])->name('store');
        Route::get('{invoice}', [RentInvoiceController::class, 'show'])->name('show');
        Route::get('{invoice}/edit', [RentInvoiceController::class, 'edit'])->name('edit');
        Route::put('{invoice}', [RentInvoiceController::class, 'update'])->name('update');
        Route::delete('{invoice}', [RentInvoiceController::class, 'destroy'])->name('destroy');
        Route::post('{invoice}/pay', [RentInvoiceController::class, 'recordPayment'])->name('pay');
        Route::get('{invoice}/print', [RentInvoiceController::class, 'printInvoice'])->name('print');
        Route::get('{invoice}/download', [RentInvoiceController::class, 'download'])->name('download');
    });

    // 2. Vehicle Invoices
    Route::prefix('invoices/vehicle')->name('invoices.vehicle.')->group(function () {
        Route::get('/', [VehicleInvoiceController::class, 'index'])->name('index');
        Route::get('create', [VehicleInvoiceController::class, 'create'])->name('create');
        Route::post('/', [VehicleInvoiceController::class, 'store'])->name('store');
        Route::get('{invoice}', [VehicleInvoiceController::class, 'show'])->name('show');
        Route::get('{invoice}/edit', [VehicleInvoiceController::class, 'edit'])->name('edit');
        Route::put('{invoice}', [VehicleInvoiceController::class, 'update'])->name('update');
        Route::delete('{invoice}', [VehicleInvoiceController::class, 'destroy'])->name('destroy');
        Route::post('{invoice}/pay', [VehicleInvoiceController::class, 'recordPayment'])->name('pay');
        Route::get('{invoice}/print', [VehicleInvoiceController::class, 'printInvoice'])->name('print');
        Route::get('{invoice}/download', [VehicleInvoiceController::class, 'download'])->name('download');
    });

    // 3. Global Invoices
    Route::get('invoices/bulk-generate', [InvoiceController::class, 'bulkGenerateForm'])->name('invoices.bulk-generate');
    Route::post('invoices/bulk-generate/preview', [InvoiceController::class, 'previewBulkGenerate'])->name('invoices.bulk-generate.preview');
    Route::post('invoices/bulk-generate', [InvoiceController::class, 'bulkGenerate'])->name('invoices.bulk-generate.store');
    Route::get('invoices/rent-breakdown-modal/{rent}', [InvoiceController::class, 'rentBreakdownModal'])->name('invoices.rent-breakdown-modal');

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'recordPayment'])->name('invoices.pay');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('vat-taxes-list', [VatTaxController::class, 'list'])->name('vat-taxes.list');
    Route::resource('vat-taxes', VatTaxController::class);

    // Profile & Password Routes
    Route::get('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::post('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Admin Reset User Password
    Route::post('/admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
});

// Universal Master Vendor Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('vendors', VendorController::class);
    Route::get('vendors/{vendor}/history', [VendorController::class, 'history'])->name('vendors.history');
});

Route::prefix('maintenance')->name('maintenance.')->middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [MaintenanceReportController::class, 'dashboard'])->name('dashboard');
    
    // Vendors
    Route::resource('vendors', VendorController::class);
    Route::get('vendors/{vendor}/history', [VendorController::class, 'history'])->name('vendors.history');
    
    // Vehicle Parts Master
    Route::resource('parts', VehiclePartController::class);
    Route::get('parts/{part}/history/{vehicle}', [VehiclePartController::class, 'partHistory'])->name('parts.history');
    
    // Vehicle Maintenance Records
    Route::resource('maintenances', VehicleMaintenanceController::class);
    Route::post('maintenances/{maintenance}/approve', [VehicleMaintenanceController::class, 'approve'])->name('maintenances.approve');
    Route::get('maintenances/{maintenance}/invoice', [VehicleMaintenanceController::class, 'generateInvoice'])->name('maintenances.invoice');
    
    // Operational Logs
    Route::resource('operational-logs', VehicleOperationalLogController::class);
    Route::post('operational-logs/meter-reading', [VehicleOperationalLogController::class, 'quickMeterReading'])->name('operational-logs.meter-reading');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('VehicleManagement.VehicleMaintenance.Reports.index');
        })->name('index');
        Route::get('/vehicle-cost', [MaintenanceReportController::class, 'vehicleCost'])->name('vehicle-cost');
        Route::get('/vendor-cost', [MaintenanceReportController::class, 'vendorCost'])->name('vendor-cost');
        Route::get('/vendor-bill', [MaintenanceReportController::class, 'vendorBill'])->name('vendor-bill');
        Route::get('/vendor-bill/export', [MaintenanceReportController::class, 'vendorBillExport'])
     ->name('vendor-bill.export');
        Route::get('/monthly-expenses', [MaintenanceReportController::class, 'monthlyExpenses'])->name('monthly-expenses');
        Route::get('/parts-history', [MaintenanceReportController::class, 'partsHistory'])->name('parts-history');
        Route::get('/service-due', [MaintenanceReportController::class, 'serviceDue'])->name('service-due');
        Route::get('/vendor-comparison', [MaintenanceReportController::class, 'vendorComparison'])->name('vendor-comparison');
    });
});

