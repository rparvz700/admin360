                    
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
            <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close"
                href="javascript:void(0)">
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

                {{-- -------------------------Users------------------------- --}}
                <li
                    class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'users' || $activeMenu == 'departments' || $activeMenu == 'subcenters') ? 'open' : '' }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                        aria-expanded="false" href="#">
                        <i class="nav-main-link-icon si si-users"></i>
                        <span class="nav-main-link-name">User Settings</span>
                    </a>
                    <ul class="nav-main-submenu">
                        @canany(['create-user', 'edit-user', 'delete-user'])
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'users' ? 'active' : '' }}"
                                    href="{{ route('users.index') }}">
                                    <span class="nav-main-link-name">Users</span>
                                </a>
                            </li>
                        @endcanany
                        @canany(['create-department', 'edit-department', 'delete-department'])
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'departments' ? 'active' : '' }}"
                                    href="{{ route('departments.index') }}">
                                    <span class="nav-main-link-name">Departments</span>
                                </a>
                            </li>
                        @endcanany
                        @canany(['create-subcenter', 'edit-subcenter', 'delete-subcenter'])
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'subcenters' ? 'active' : '' }}"
                                    href="{{ route('subcenters.index') }}">
                                    <span class="nav-main-link-name">Subcenters</span>
                                </a>
                            </li>
                        @endcanany
                    </ul>
                </li>
                {{-- -------------------------Facilities Management------------------------- --}}
                <li class="nav-main-item {{ isset($activeMenu) && $activeMenu == 'buildings' ? 'open' : '' }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                        aria-expanded="false" href="#">
                        <i class="nav-main-link-icon fa fa-building"></i>
                        <span class="nav-main-link-name">Facilities Management</span>
                    </a>
                    <ul class="nav-main-submenu">
                        
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon fa fa-home"></i>
                                <span class="nav-main-link-name">Property Management</span>
                            </a>
                            <ul class="nav-main-submenu">
                                @canany(['create-building', 'edit-building', 'delete-building'])
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'buildings' ? 'active' : '' }}" href="{{ route('buildings.index') }}">
                                            <span class="nav-main-link-name">Buildings</span>
                                        </a>
                                    </li>
                                @endcanany
                                @canany(['create-floor', 'edit-floor', 'delete-floor'])
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'floors' ? 'active' : '' }}" href="{{ route('floors.index') }}">
                                            <span class="nav-main-link-name">Floors</span>
                                        </a>
                                    </li>
                                @endcanany
                                @canany(['create-agreement', 'edit-agreement', 'delete-agreement'])
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'agreements' ? 'active' : '' }}" href="{{ route('agreements.index') }}">
                                            <span class="nav-main-link-name">Agreements</span>
                                        </a>
                                    </li>
                                @endcanany
                                @canany(['create-rent', 'edit-rent', 'delete-rent'])
                                    <li class="nav-main-item">
                                        <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'rent' ? 'active' : '' }}" href="{{ route('rent.index') }}">
                                            <span class="nav-main-link-name">Rent</span>
                                        </a>
                                    </li>
                                @endcanany
                            </ul>
                        </li>
                       
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon fa fa-box"></i>
                                <span class="nav-main-link-name">Asset Management</span>
                            </a>
                            <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'assets' ? 'active' : '' }}" href="{{ route('assets.index') }}">
                                <span class="nav-main-link-name">Assets</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon fa fa-cog"></i>
                                <span class="nav-main-link-name">Settings</span>
                            </a>
                            <ul class="nav-main-submenu">
                                <li class="nav-main-item">
                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'asset-categories' ? 'active' : '' }}" href="{{ route('asset-categories.index') }}">
                                        <span class="nav-main-link-name">Asset Categories</span>
                                    </a>
                                </li>
                                <li class="nav-main-item">
                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'asset-attributes' ? 'active' : '' }}" href="{{ route('asset-attributes.index') }}">
                                        <span class="nav-main-link-name">Asset Attributes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                {{-- ------------------------------End Facilities Management-------------------------- --}}

                {{-- -------------------------Vehicle Management------------------------- --}}
                <li class="nav-main-item {{ isset($activeMenu) && (in_array($activeMenu, ['drivers', 'vehicle-types', 'vehicles']) ? 'open' : '') }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                        <i class="nav-main-link-icon fa fa-car"></i>
                        <span class="nav-main-link-name">Vehicle Management</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'drivers' ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                                <span class="nav-main-link-name">Drivers</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'vehicles' ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                <span class="nav-main-link-name">Vehicles</span>
                            </a>
                        </li>
                        
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon fa fa-cog"></i>
                                <span class="nav-main-link-name">Settings</span>
                            </a>
                            <ul class="nav-main-submenu">
                                <li class="nav-main-item">
                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'vehicle-types' ? 'active' : '' }}" href="{{ route('vehicle-types.index') }}">
                                        <span class="nav-main-link-name">Vehicle Types</span>
                                    </a>
                                </li>
                                
                            </ul>
                        </li>
                    </ul>
                </li>
                {{-- -------------------------Ticket Management------------------------- --}}
                <li
                    class="nav-main-item {{ isset($activeMenu) && ($activeMenu == 'tickets' || $activeMenu == 'departments' || $activeMenu == 'subcenters') ? 'open' : '' }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                        aria-expanded="false" href="#">
                        <i class="nav-main-link-icon si si-users"></i>
                        <span class="nav-main-link-name">Ticket Management</span>
                    </a>
                    <ul class="nav-main-submenu">
                        
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'tickets' ? 'active' : '' }}"
                                    href="{{ route('tickets.index') }}">
                                    <span class="nav-main-link-name">User Tickets</span>
                                </a>
                            </li>
                       
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'tickets' ? 'active' : '' }}"
                                    href="{{ route('admin.tickets.index') }}">
                                    <span class="nav-main-link-name">Admin Tickets</span>
                                </a>
                            </li>
                    </ul>
                </li>
                    {{-- ------------------------------Start GenericDocument Management-------------------------- --}}
                <li class="nav-main-item {{ isset($activeMenu) && (in_array($activeMenu, ['generic-documents', 'generic-document-categories', 'generic-document-attributes']) ? 'open' : '') }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                        <i class="nav-main-link-icon fa fa-file-text"></i>
                        <span class="nav-main-link-name">Generic Document Management</span>
                    </a>
                    <ul class="nav-main-submenu">
                        
                        <li class="nav-main-item">
                            <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-documents' ? 'active' : '' }}" href="{{ route('generic-documents.index') }}">
                                <span class="nav-main-link-name">Generic Documents</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon fa fa-cog"></i>
                                <span class="nav-main-link-name">Settings</span>
                            </a>
                            <ul class="nav-main-submenu">
                                
                                <li class="nav-main-item">
                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-document-categories' ? 'active' : '' }}" href="{{ route('generic-document-categories.index') }}">
                                        <span class="nav-main-link-name">Generic Document Categories</span>
                                    </a>
                                </li>
                                <li class="nav-main-item">
                                    <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'generic-document-attributes' ? 'active' : '' }}" href="{{ route('generic-document-attributes.index') }}">
                                        <span class="nav-main-link-name">Generic Document Attributes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                {{-- ------------------------------End GenericDocument Management-------------------------- --}}

                {{-- ----------------------Settings------------------- --}}
                <li class="nav-main-item {{ isset($activeMenu) && $activeMenu == 'roles' ? 'open' : '' }}">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                        aria-expanded="false" href="#">
                        <i class="nav-main-link-icon fa fa-cog"></i>
                        <span class="nav-main-link-name">Settings</span>
                    </a>
                    <ul class="nav-main-submenu">
                        @canany(['create-role', 'edit-role', 'delete-role'])
                            <li class="nav-main-item">
                                <a class="nav-main-link {{ isset($activeMenu) && $activeMenu == 'roles' ? 'active' : '' }}"
                                    href="{{ route('roles.index') }}">
                                    <span class="nav-main-link-name">Roles</span>
                                </a>
                            </li>
                        @endcanany
                    </ul>
                </li>

                {{-- --------------------End Settings------------------ --}}

            </ul>
        </div>
        <!-- END Side Navigation -->
    </div>
    <!-- END Sidebar Scrolling -->
</nav>
<!-- END Sidebar -->
