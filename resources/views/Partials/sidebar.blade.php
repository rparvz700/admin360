                    <!-- Sidebar -->
                    <nav id="sidebar" aria-label="Main Navigation">
                        <!-- Side Header -->
                        <div class="content-header">
                            <!-- Logo -->
                            <a class="fw-semibold text-dual" href="{{ url('/') }}">
                                <span class="smini-visible">
                                    <i class="fa fa-circle-notch text-primary"></i>
                                </span>
                                <span class="smini-hide fs-3 tracking-wider text-center">{{ config('app.name') }} </span>
                            </a>

                            <!-- END Logo -->

                            <!-- Extra -->
                            <div>

                                <!-- Close Sidebar, Visible only on mobile screens -->
                                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                                <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout"
                                    data-action="sidebar_close" href="javascript:void(0)">
                                    <i class="fa fa-fw fa-times"></i>
                                </a>
                                <span>{{ config('app.version') }} </span>
                                <!-- END Close Sidebar -->
                            </div>
                            <!-- END Extra -->
                        </div>
                        <!-- END Side Header -->

                        <!-- Sidebar Scrolling -->
                        <div class="js-sidebar-scroll">
                            <!-- Side Navigation -->
                            <div class="content-side">
                                <ul class="nav-main">
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'dashboard' ? 'active' : '' }}"
                                            href="{{ url('/') }}">
                                            <i class="nav-main-link-icon si si-speedometer"></i>
                                            <span class="nav-main-link-name">Dashboard</span>
                                        </a>
                                    </li>

                                    {{-- -------------------------Facilities Management------------------------- --}}
                                    @canany(['property-wizard', 'create-agreement', 'edit-agreement',
                                        'delete-agreement', 'create-building', 'edit-building', 'delete-building',
                                        'create-floor', 'edit-floor', 'delete-floor', 'create-rent', 'edit-rent',
                                        'delete-rent', 'asset-management'])
                                        <li
                                            class="nav-main-item {{ isset($activeMenu) &&
                                            ($activeMenu == 'buildings' ||
                                                $activeMenu == 'floors' ||
                                                $activeMenu == 'agreements' ||
                                                $activeMenu == 'rent' ||
                                                $activeMenu == 'utility-types' ||
                                                $activeMenu == 'assets' ||
                                                $activeMenu == 'asset-categories' ||
                                                $activeMenu == 'electricity-bills' ||
                                                $activeMenu == 'electricity-meters' ||
                                                $activeMenu == 'electricity-rios' ||
                                                $activeMenu == 'electricity-reports' ||
                                                $activeMenu == 'asset-attributes')
                                                ? 'open'
                                                : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon fa fa-building"></i>
                                                <span class="nav-main-link-name">Facilities Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">

                                                <li
                                                    class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'buildings' || $activeMenu == 'floors' || $activeMenu == 'agreements' || $activeMenu == 'rent' || $activeMenu == 'utility-types' || $activeMenu == 'wizard.property' || $activeMenu == 'facilities.npv' || $activeMenu == 'admin.finance-settings') ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-home"></i>
                                                        <span class="nav-main-link-name">Property Management</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        @canany(['property-wizard'])
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'wizard.property' ? 'active' : '' }}"
                                                                    href="{{ route('wizard.property.create') }}">
                                                                    <span class="nav-main-link-name">Create All</span>
                                                                </a>
                                                            </li>
                                                        @endcanany
                                                        @canany(['create-agreement', 'edit-agreement', 'delete-agreement'])
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'agreements' ? 'active' : '' }}"
                                                                    href="{{ route('agreements.index') }}">
                                                                    <span class="nav-main-link-name">Agreements</span>
                                                                </a>
                                                            </li>
                                                        @endcanany
                                                        @canany(['create-building', 'edit-building', 'delete-building'])
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'buildings' ? 'active' : '' }}"
                                                                    href="{{ route('buildings.index') }}">
                                                                    <span class="nav-main-link-name">Buildings</span>
                                                                </a>
                                                            </li>
                                                        @endcanany
                                                        @canany(['create-floor', 'edit-floor', 'delete-floor'])
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'floors' ? 'active' : '' }}"
                                                                    href="{{ route('floors.index') }}">
                                                                    <span class="nav-main-link-name">Floors</span>
                                                                </a>
                                                            </li>
                                                        @endcanany

                                                        @canany(['create-rent', 'edit-rent', 'delete-rent'])
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'rent' ? 'active' : '' }}"
                                                                    href="{{ route('rent.index') }}">
                                                                    <span class="nav-main-link-name">Rent</span>
                                                                </a>
                                                            </li>
                                                        @endcanany
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'facilities.npv' ? 'active' : '' }}"
                                                                href="{{ route('facilities.npv.index') }}">
                                                                <span class="nav-main-link-name">NPV Calculation</span>
                                                            </a>
                                                        </li>
                                                        @canany(['create-rent', 'edit-rent', 'delete-rent'])
                                                            <li
                                                                class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'utility-types' || $activeMenu == 'admin.finance-settings') ? 'open' : '' }}">
                                                                <a class="nav-main-link nav-main-link-submenu"
                                                                    data-toggle="submenu" aria-haspopup="true"
                                                                    aria-expanded="false" href="#">
                                                                    <span class="nav-main-link-name">Settings</span>
                                                                </a>
                                                                <ul class="nav-main-submenu">
                                                                    <li class="nav-main-item">
                                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'utility-types' ? 'active' : '' }}"
                                                                            href="{{ route('utility-types.index') }}">
                                                                            <span class="nav-main-link-name">Utility
                                                                                Types</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-main-item">
                                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'admin.finance-settings' ? 'active' : '' }}"
                                                                            href="{{ route('admin.finance-settings.index') }}">
                                                                            <span class="nav-main-link-name">Finance Settings</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                        @endcanany
                                                    </ul>
                                                </li>

                                                <!-- Electricity Management Submenu -->
                                                <li
                                                    class="nav-main-item {{ isset($activeMenu) && strpos($activeMenu, 'electricity-') === 0 ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-bolt text-warning"></i>
                                                        <span class="nav-main-link-name">Electricity Management</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'electricity-bills' ? 'active' : '' }}"
                                                                href="{{ route('electricity.bills.index') }}">
                                                                <span class="nav-main-link-name">Electricity Bills</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'electricity-meters' ? 'active' : '' }}"
                                                                href="{{ route('electricity.meters.index') }}">
                                                                <span class="nav-main-link-name">Meters Master</span>
                                                            </a>
                                                        </li>
                                                        {{-- <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'electricity-rios' ? 'active' : '' }}"
                                                                href="{{ route('electricity.rios.index') }}">
                                                                <span class="nav-main-link-name">RIOs Setup</span>
                                                            </a>
                                                        </li> --}}
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'electricity-reports' ? 'active' : '' }}"
                                                                href="{{ route('electricity.reports.index') }}">
                                                                <span class="nav-main-link-name">Reports & Analytics</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                @canany(['asset-management'])
                                                    <li
                                                        class="nav-main-item {{ isset($activeMenu) &&
                                                        ($activeMenu == 'assets' ||
                                                            $activeMenu == 'asset-categories' ||
                                                            $activeMenu == 'projects' ||
                                                            $activeMenu == 'asset-attributes')
                                                            ? 'open'
                                                            : '' }}">
                                                        <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                            aria-haspopup="true" aria-expanded="false" href="#">
                                                            <i class="nav-main-link-icon fa fa-box"></i>
                                                            <span class="nav-main-link-name">Asset Management</span>
                                                        </a>
                                                        <ul class="nav-main-submenu">
                                                            <li class="nav-main-item">
                                                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'assets' ? 'active' : '' }}"
                                                                    href="{{ route('assets.index') }}">
                                                                    <span class="nav-main-link-name">Assets</span>
                                                                </a>
                                                            </li>
                                                            <li
                                                                class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'asset-categories' || $activeMenu == 'asset-attributes' || $activeMenu == 'projects') ? 'open' : '' }}">
                                                                <a class="nav-main-link nav-main-link-submenu"
                                                                    data-toggle="submenu" aria-haspopup="true"
                                                                    aria-expanded="false" href="#">
                                                                    <i class="nav-main-link-icon fa fa-cog"></i>
                                                                    <span class="nav-main-link-name">Settings</span>
                                                                </a>
                                                                <ul class="nav-main-submenu">
                                                                    <li class="nav-main-item">
                                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'asset-categories' ? 'active' : '' }}"
                                                                            href="{{ route('asset-categories.index') }}">
                                                                            <span class="nav-main-link-name">Asset
                                                                                Categories</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-main-item">
                                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'asset-attributes' ? 'active' : '' }}"
                                                                            href="{{ route('asset-attributes.index') }}">
                                                                            <span class="nav-main-link-name">Asset
                                                                                Attributes</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-main-item">
                                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'projects' ? 'active' : '' }}"
                                                                            href="{{ route('projects.index') }}">
                                                                            <span class="nav-main-link-name">Projects</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                @endcanany
                                            </ul>
                                        </li>
                                    @endcanany
                                    {{-- ------------------------------End Facilities Management-------------------------- --}}

                                    {{-- -------------------------Vehicle Management------------------------- --}}
                                    @canany(['vehicle-management'])
                                        <li
                                            class="nav-main-item {{ isset($activeMenu) && in_array($activeMenu, ['drivers', 'vehicle-types', 'vehicles']) ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon fa fa-car"></i>
                                                <span class="nav-main-link-name">Vehicle Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'drivers' ? 'active' : '' }}"
                                                        href="{{ route('drivers.index') }}">
                                                        <span class="nav-main-link-name">Drivers</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'vehicles' ? 'active' : '' }}"
                                                        href="{{ route('vehicles.index') }}">
                                                        <span class="nav-main-link-name">Vehicles</span>
                                                    </a>
                                                </li>

                                                <li
                                                    class="nav-main-item {{ isset($activeMenu) && $activeMenu == 'vehicle-types' ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-cog"></i>
                                                        <span class="nav-main-link-name">Settings</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'vehicle-types' ? 'active' : '' }}"
                                                                href="{{ route('vehicle-types.index') }}">
                                                                <span class="nav-main-link-name">Vehicle Types</span>
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    @endcanany
                                    {{-- -------------------------Vehicle Maintenance Management------------------------- --}}

                                    @canany(['vehicle-maintenance-management'])
                                        <li class="nav-main-item {{ request()->routeIs('maintenance.dashboard', 'maintenance.maintenances.*', 'maintenance.operational-logs.*', 'maintenance.reports.*', 'maintenance.parts*') ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon si si-wrench"></i>
                                                <span class="nav-main-link-name">Vehicle Maintenence Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }}"
                                                        href="{{ route('maintenance.dashboard') }}">
                                                        <span class="nav-main-link-name">Dashboard</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ request()->routeIs('maintenance.maintenances.*') ? 'active' : '' }}"
                                                        href="{{ route('maintenance.maintenances.index') }}">
                                                        <span class="nav-main-link-name">Maintenances</span>
                                                    </a>
                                                </li>

                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ request()->routeIs('maintenance.operational-logs.*') ? 'active' : '' }}"
                                                        href="{{ route('maintenance.operational-logs.index') }}">
                                                        <span class="nav-main-link-name">Operational Logs</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ request()->routeIs('maintenance.reports.*') ? 'active' : '' }}"
                                                        href="{{ route('maintenance.reports.index') }}">
                                                        <span class="nav-main-link-name">Reports</span>
                                                    </a>
                                                </li>

                                                <li
                                                    class="nav-main-item {{ request()->routeIs('maintenance.parts') ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-cog"></i>
                                                        <span class="nav-main-link-name">Settings</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ request()->routeIs('maintenance.parts') ? 'active' : '' }}"
                                                                href="{{ route('maintenance.parts.index') }}">
                                                                <span class="nav-main-link-name">Vehicle Parts</span>
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    @endcanany
                                    {{-- ------------------------------Start GenericDocument Management-------------------------- --}}

                                    @canany(['document-management'])
                                        <li
                                            class="nav-main-item {{ isset($activeMenu) && in_array($activeMenu, ['generic-documents', 'generic-document-categories', 'generic-document-attributes']) ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon fa fa-file-text"></i>
                                                <span class="nav-main-link-name">Generic Document Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">

                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-documents' ? 'active' : '' }}"
                                                        href="{{ route('generic-documents.index') }}">
                                                        <span class="nav-main-link-name">Generic Documents</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="nav-main-item {{ isset($activeMenu) && in_array($activeMenu, ['generic-document-categories', 'generic-document-attributes']) ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-cog"></i>
                                                        <span class="nav-main-link-name">Settings</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">

                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-document-categories' ? 'active' : '' }}"
                                                                href="{{ route('generic-document-categories.index') }}">
                                                                <span class="nav-main-link-name">Generic Document
                                                                    Categories</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-document-attributes' ? 'active' : '' }}"
                                                                href="{{ route('generic-document-attributes.index') }}">
                                                                <span class="nav-main-link-name">Generic Document
                                                                    Attributes</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    @endcanany
                                    {{-- ------------------------------End GenericDocument Management-------------------------- --}}


                                    {{-- -------------------------Ticket Management------------------------- --}}
                                    @canany(['ticket-management'])
                                        <li
                                            class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'tickets' || $activeMenu == 'departments' || $activeMenu == 'subcenters') ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon si si-users"></i>
                                                <span class="nav-main-link-name">Ticket Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                @canany(['user-ticket-management'])
                                                    <li class="nav-main-item">
                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'tickets' ? 'active' : '' }}"
                                                            href="{{ route('tickets.index') }}">
                                                            <span class="nav-main-link-name">User Tickets</span>
                                                        </a>
                                                    </li>
                                                @endcanany

                                                @canany('admin-ticket-management')
                                                    <li class="nav-main-item">
                                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'tickets' ? 'active' : '' }}"
                                                            href="{{ route('admin.tickets.index') }}">
                                                            <span class="nav-main-link-name">Admin Tickets</span>
                                                        </a>
                                                    </li>
                                                @endcanany
                                            </ul>
                                        </li>
                                    @endcanany
                                    {{-- -------------------------Invoice Management------------------------- --}}
                                    @canany(['invoice-management'])
                                        <li
                                            class="nav-main-item {{ request()->routeIs('invoices.*', 'vat-taxes.*') ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon fa fa-file-invoice-dollar"></i>
                                                <span class="nav-main-link-name">Invoice Management</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link {{ request()->routeIs('invoices.dashboard') ? 'active' : '' }}"
                                                        href="{{ route('invoices.dashboard') }}">
                                                        <span class="nav-main-link-name">Dashboard</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item {{ request()->routeIs('invoices.rent.*', 'invoices.vehicle.*', 'invoices.index') ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                                        <span class="nav-main-link-name">Invoices</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ request()->routeIs('invoices.rent.*') ? 'active' : '' }}" href="{{ route('invoices.rent.index') }}">
                                                                <span class="nav-main-link-name">Rent Invoices</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ request()->routeIs('invoices.vehicle.*') ? 'active' : '' }}" href="{{ route('invoices.vehicle.index') }}">
                                                                <span class="nav-main-link-name">Vehicle Invoices</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ request()->routeIs('invoices.index') || (request()->routeIs('invoices.show', 'invoices.create', 'invoices.edit') && !request()->routeIs('invoices.rent.*', 'invoices.vehicle.*')) ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                                                                <span class="nav-main-link-name">All Invoices</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li
                                                    class="nav-main-item {{ request()->routeIs('vat-taxes.*') ? 'open' : '' }}">
                                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                        aria-haspopup="true" aria-expanded="false" href="#">
                                                        <i class="nav-main-link-icon fa fa-cog"></i>
                                                        <span class="nav-main-link-name">Settings</span>
                                                    </a>
                                                    <ul class="nav-main-submenu">
                                                        <li class="nav-main-item">
                                                            <a class="nav-main-link {{ request()->routeIs('vat-taxes.*') ? 'active' : '' }}"
                                                                href="{{ route('vat-taxes.index') }}">
                                                                <span class="nav-main-link-name">VAT/TAX</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    @endcanany

                                    {{-- ----------------------Vendors------------------- --}}
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ (isset($activeMenu) && $activeMenu == 'vendors') || request()->routeIs('maintenance.vendors.*') ? 'active' : '' }}"
                                            href="{{ route('maintenance.vendors.index') }}">
                                            <i class="nav-main-link-icon fa fa-store"></i>
                                            <span class="nav-main-link-name">Vendors</span>
                                        </a>
                                    </li>

                                    {{-- ----------------------Settings------------------- --}}
                                    @canany(['create-role', 'edit-role', 'delete-role', 'create-user', 'edit-user',
                                        'delete-user'])
                                        <li
                                            class="nav-main-item {{ (isset($activeMenu) && in_array($activeMenu, ['roles', 'users'])) || request()->routeIs('roles.*', 'users.*') ? 'open' : '' }}">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu"
                                                aria-haspopup="true" aria-expanded="false" href="#">
                                                <i class="nav-main-link-icon fa fa-cog"></i>
                                                <span class="nav-main-link-name">Settings</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                @canany(['create-role', 'edit-role', 'delete-role'])
                                                    <li class="nav-main-item">
                                                        <a class="nav-main-link {{ (isset($activeMenu) && $activeMenu == 'roles') || request()->routeIs('roles.*') ? 'active' : '' }}"
                                                            href="{{ route('roles.index') }}">
                                                            <span class="nav-main-link-name">Roles</span>
                                                        </a>
                                                    </li>
                                                @endcanany

                                                @canany(['create-user', 'edit-user', 'delete-user'])
                                                    <li class="nav-main-item">
                                                        <a class="nav-main-link {{ (isset($activeMenu) && $activeMenu == 'users') || request()->routeIs('users.*') ? 'active' : '' }}"
                                                            href="{{ route('users.index') }}">
                                                            <span class="nav-main-link-name">Users</span>
                                                        </a>
                                                    </li>
                                                @endcanany
                                            </ul>
                                        </li>
                                    @endcanany

                                    {{-- --------------------End Settings------------------ --}}

                                </ul>
                            </div>
                            <!-- END Side Navigation -->
                        </div>
                        <!-- END Sidebar Scrolling -->
                    </nav>
                    <!-- END Sidebar -->
