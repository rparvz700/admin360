@extends('Partials.app', ['activeMenu' => 'tickets'])
@section('title') Add Ticket @endsection
@section('styles')
<!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; top: 0; width: 100%; height: 100%; 
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 700px;
            border-radius: 8px;
            position: relative;
        }
        #map { height: 300px; width: 100%; border-radius: 4px; margin-bottom: 15px; }
        
        
    </style>
@endsection
@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create New Ticket</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticketForm">
                @csrf

                <!-- Ticket Type Selection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label required">Ticket Type</label>
                        <select name="ticket_type" id="ticket_type" class="form-select @error('ticket_type') is-invalid @enderror" required>
                            <option value="">Select Ticket Type</option>
                            <option value="vehicle_support" {{ old('ticket_type') == 'vehicle_support' ? 'selected' : '' }}>Vehicle Support Request</option>
                            <option value="asset_request" {{ old('ticket_type') == 'asset_request' ? 'selected' : '' }}>New Asset Request</option>
                            <option value="asset_repair" {{ old('ticket_type') == 'asset_repair' ? 'selected' : '' }}>Asset Repair Request</option>
                        </select>
                        @error('ticket_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Priority</label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company['id'] }}" {{ old('company_id') == $company['id'] ? 'selected' : '' }}>
                                    {{ $company['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Project Name</label>
                        <select name="project_name" class="form-select @error('project_name') is-invalid @enderror">
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project['id'] }}" {{ old('project_name') == $project['id'] ? 'selected' : '' }}>
                                    {{ $project['project_name'] }}   
                                </option>
                            @endforeach
                        </select>
                        @error('project_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label required">Description</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Vehicle Support Fields -->
                <div id="vehicle_fields" class="type-specific-fields" style="display: none;">
                    <h5 class="mb-3">Vehicle Support Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Vehicle Type</label>
                            <select name="vehicle_type_id" class="form-select @error('vehicle_type_id') is-invalid @enderror">
                                <option value="">Select Vehicle Type</option>
                                @foreach($vehicleTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->type_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Passenger Count</label>
                            <input type="number" name="passenger_count" class="form-control @error('passenger_count') is-invalid @enderror" value="{{ old('passenger_count', 1) }}" min="1">
                            @error('passenger_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Trip Start Date & Time</label>
                            <input type="datetime-local" name="trip_start_datetime" class="form-control @error('trip_start_datetime') is-invalid @enderror" value="{{ old('trip_start_datetime') }}">
                            @error('trip_start_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Trip End Date & Time</label>
                            <input type="datetime-local" name="trip_end_datetime" class="form-control @error('trip_end_datetime') is-invalid @enderror" value="{{ old('trip_end_datetime') }}">
                            @error('trip_end_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dynamic Trip Locations Grid -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label required">Trip Locations</label>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <div id="trip_locations_container">
                                        <!-- Trip location rows will be added here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success" id="add_trip_location">
                                        <i class="fas fa-plus"></i> Add Stopage
                                    </button>
                                </div>
                            </div>
                            @error('trip_locations')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- The Modal -->
                    <div id="mapModal" class="modal">
                        <div class="modal-content">
                            <h3>Select Location</h3>
                            <p class="info">Click on the map to drop a pin.</p>
                            <div id="map"></div>
                            <div class="btn-group">
                                <button type ="button" class="btn btn-secondary" id="closeModal">Cancel</button>
                                <button type ="button" class="btn btn-primary" id="confirmLocation">Confirm Location</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label required">Trip Purpose</label>
                            <textarea name="trip_purpose" rows="3" class="form-control @error('trip_purpose') is-invalid @enderror">{{ old('trip_purpose') }}</textarea>
                            @error('trip_purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Asset Request Fields -->
                <div id="asset_request_fields" class="type-specific-fields" style="display: none;">
                    <h5 class="mb-3">Asset Request Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Asset Category</label>
                            <select name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($assetCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('asset_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('asset_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Requested Asset Name</label>
                            <input type="text" name="requested_asset_name" class="form-control @error('requested_asset_name') is-invalid @enderror" value="{{ old('requested_asset_name') }}">
                            @error('requested_asset_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Floor</label>
                            <select name="floor_id" class="form-select @error('floor_id') is-invalid @enderror">
                                <option value="">Select Floor</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                        {{ $floor->building->site_name ?? '' }} - {{ $floor->floor_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('floor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Location within Floor</label>
                            <input type="text" name="location_within_floor" class="form-control @error('location_within_floor') is-invalid @enderror" value="{{ old('location_within_floor') }}" placeholder="e.g., Room 301, Desk A">
                            @error('location_within_floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Asset Specifications</label>
                            <textarea name="asset_specifications" rows="3" class="form-control @error('asset_specifications') is-invalid @enderror" placeholder="Provide any specific requirements or specifications">{{ old('asset_specifications') }}</textarea>
                            @error('asset_specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Asset Repair Fields -->
                <div id="asset_repair_fields" class="type-specific-fields" style="display: none;">
                    <h5 class="mb-3">Asset Repair Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Floor</label>
                            <select name="repair_floor_id" id="repair_floor_id" class="form-select @error('floor_id') is-invalid @enderror">
                                <option value="">Select Floor</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                        {{ $floor->building->site_name ?? '' }} - {{ $floor->floor_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('floor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Location within Floor</label>
                            <input type="text" name="repair_location_within_floor" id="repair_location_within_floor" class="form-control @error('location_within_floor') is-invalid @enderror" value="{{ old('location_within_floor') }}" placeholder="e.g., Room 301, Desk A">
                            @error('location_within_floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Asset Category Filter</label>
                            <select id="repair_category_filter" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($assetCategories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional filter to narrow down assets</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Search Asset</label>
                            <input type="text" id="repair_asset_search" class="form-control" placeholder="Search by tag or name...">
                            <small class="text-muted">Quick search for assets</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label required">Select Asset</label>
                            <select name="asset_id" id="repair_asset_id" class="form-select @error('asset_id') is-invalid @enderror" size="8">
                                <option value="">Select Floor First</option>
                            </select>
                            @error('asset_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-2">
                                <span class="badge bg-info" id="asset_count_badge">0 assets available</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="text-muted">You can upload multiple files (PDF, Images, Word documents)</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Submit Ticket
                        </button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let tripLocationIndex = 0;

    /* ============================
    GLOBAL MAP STATE
    ============================ */
    let map = null;
    let marker = null;
    let activeInput = null;
    let selectedLat = null;
    let selectedLng = null;
    let selectedAddr = null;
    const confirmBtn = document.getElementById('confirmLocation');

    // All assets data for filtering
    const allAssets = @json($assets);
    let filteredRepairAssets = [];

    // Trip Location Management
    function addTripLocation(startLocation = '', endLocation = '') {
        const container = document.getElementById('trip_locations_container');
        const index = tripLocationIndex++;
        
        const row = document.createElement('div');
        row.className = 'trip-location-row mb-2';
        row.dataset.index = index;
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-1">
                    <span class="badge bg-info">Stop ${index + 1}</span>
                </div>
                <div class="col-md-5">
                    <textarea
                        name="trip_locations[${index}][start]"
                        id="trip_location_start_${index}"
                        class="form-control location-input"
                        placeholder="Start Location (e.g., Office)"
                        rows="2"
                        required>${startLocation}</textarea>
                </div>

                <div class="col-md-5">
                    <textarea
                        name="trip_locations[${index}][end]"
                        id="trip_location_end_${index}"
                        class="form-control location-input"
                        placeholder="End Location (e.g., Airport)"
                        rows="2"
                        required>${endLocation}</textarea>
                </div>

                <div class="col-md-1">
                    <button type="button"
                            class="btn btn-danger btn-sm w-20 remove-trip-location"
                            data-index="${index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

        `;
        
        container.appendChild(row);
        
        // Add event listener to remove button
        row.querySelector('.remove-trip-location').addEventListener('click', function() {
            removeTripLocation(this.dataset.index);
        });

        row.querySelectorAll('.location-input').forEach(input => {
            input.addEventListener('click', () => openMapModal(input));
        });

    }

    /* ============================
    MAP INITIALIZATION
    ============================ */
    function initMap() {
        if (map) return;

        map = L.map('map').setView([23.7516691, 90.3901753], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        map.on('click', e => {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    /* ============================
    PLACE MARKER + GEOCODE
    ============================ */
    function placeMarker(lat, lng) {

        selectedLat = lat;
        selectedLng = lng;

        // Disable confirm button while loading
        confirmBtn.disabled = true;
        confirmBtn.innerText = 'Fetching address...';

        if (!marker) {
            marker = L.marker([lat, lng]).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
        }

        fetch(`{{ route('api.reverse-geocode') }}?lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                selectedAddr = data.display_name;
                marker.bindPopup(selectedAddr).openPopup();
                // Enable button once data arrives
                confirmBtn.disabled = false;
                confirmBtn.innerText = 'Confirm Location';
            })
            .catch(err => {
                console.error(err);

                confirmBtn.disabled = false;
                confirmBtn.innerText = 'Confirm Location';
                alert('Failed to fetch address. Try again.');
            });
    }

    /* ============================
    OPEN MAP MODAL
    ============================ */
    function openMapModal(input) {
        confirmBtn.disabled = true;
        confirmBtn.innerText = 'Select a point';
        
        activeInput = input;
        selectedAddr = null;

        document.getElementById('mapModal').style.display = 'block';

        setTimeout(() => {
            initMap();
            map.invalidateSize();

            const match = input.value.match(/Lat:\s*(-?\d+\.\d+).*Lng:\s*(-?\d+\.\d+)/);

            if (match) {
                const lat = parseFloat(match[1]);
                const lng = parseFloat(match[2]);
                map.setView([lat, lng], 15);
                placeMarker(lat, lng);
            } else {
                map.setView([23.7516691, 90.3901753], 13);
                if (marker) marker.remove();
                marker = null;
            }
        }, 100);
    }

    /* ============================
    CONFIRM LOCATION
    ============================ */
    document.getElementById('confirmLocation').onclick = () => {

        if (!activeInput || !selectedAddr) {
            alert('Please select a location on the map');
            return;
        }

        activeInput.value =
            `${selectedAddr} (Lat: ${selectedLat.toFixed(6)}, Lng: ${selectedLng.toFixed(6)})`;

        document.getElementById('mapModal').style.display = 'none';
    };

    /* ============================
    CLOSE MODAL
    ============================ */
    document.getElementById('closeModal').onclick = () =>
        document.getElementById('mapModal').style.display = 'none';

    window.onclick = e => {
        if (e.target === document.getElementById('mapModal')) {
            document.getElementById('mapModal').style.display = 'none';
        }
    };

    /* ============================
    END MAP FUNCTIONS
    ============================ */
    
    function removeTripLocation(index) {
        const row = document.querySelector(`.trip-location-row[data-index="${index}"]`);
        if (row) {
            row.remove();
        }
        
        // Ensure at least one location exists
        if (document.querySelectorAll('.trip-location-row').length === 0) {
            addTripLocation();
        }
    }
    
    // Add trip location button
    document.getElementById('add_trip_location').addEventListener('click', function() {
        addTripLocation();
    });

    document.getElementById('ticket_type').addEventListener('change', function() {
        // Hide all type-specific fields
        document.querySelectorAll('.type-specific-fields').forEach(el => {
            el.style.display = 'none';
            // Disable required validation for hidden fields
            el.querySelectorAll('input, select, textarea').forEach(input => {
                input.removeAttribute('required');
            });
        });

        // Show selected type fields
        const selectedType = this.value;
        if (selectedType === 'vehicle_support') {
            const fields = document.getElementById('vehicle_fields');
            fields.style.display = 'block';
            
            // Set required for vehicle fields
            fields.querySelector('select[name="vehicle_type_id"]').setAttribute('required', 'required');
            fields.querySelector('input[name="passenger_count"]').setAttribute('required', 'required');
            fields.querySelector('input[name="trip_start_datetime"]').setAttribute('required', 'required');
            fields.querySelector('input[name="trip_end_datetime"]').setAttribute('required', 'required');
            fields.querySelector('textarea[name="trip_purpose"]').setAttribute('required', 'required');
            
            // Initialize with one trip location if empty
            if (document.querySelectorAll('.trip-location-row').length === 0) {
                addTripLocation();
            }
        } else if (selectedType === 'asset_request') {
            const fields = document.getElementById('asset_request_fields');
            fields.style.display = 'block';
            fields.querySelector('select[name="floor_id"]').setAttribute('required', 'required');
            fields.querySelector('input[name="location_within_floor"]').setAttribute('required', 'required');
            fields.querySelector('select[name="asset_category_id"]').setAttribute('required', 'required');
            fields.querySelector('input[name="requested_asset_name"]').setAttribute('required', 'required');
        } else if (selectedType === 'asset_repair') {
            const fields = document.getElementById('asset_repair_fields');
            fields.style.display = 'block';
            fields.querySelector('select[name="repair_floor_id"]').setAttribute('required', 'required');
            fields.querySelector('input[name="repair_location_within_floor"]').setAttribute('required', 'required');
            fields.querySelector('select[name="asset_id"]').setAttribute('required', 'required');
        }
    });

    // Filter and render repair assets
    function filterAndRenderRepairAssets() {
        const floorId = document.getElementById('repair_floor_id').value;
        const categoryFilter = document.getElementById('repair_category_filter').value;
        const searchTerm = document.getElementById('repair_asset_search').value.toLowerCase();
        const assetSelect = document.getElementById('repair_asset_id');
        
        // Clear current options
        assetSelect.innerHTML = '';
        
        if (!floorId) {
            assetSelect.innerHTML = '<option value="">Select Floor First</option>';
            assetSelect.size = 1;
            updateAssetCount(0);
            return;
        }
        
        // Filter assets by floor
        filteredRepairAssets = allAssets.filter(asset => {
            const matchesFloor = asset.floor_id == floorId;
            const matchesCategory = !categoryFilter || asset.category_id == categoryFilter;
            const matchesSearch = !searchTerm || 
                asset.asset_tag.toLowerCase().includes(searchTerm) ||
                asset.asset_name.toLowerCase().includes(searchTerm) ||
                (asset.category && asset.category.category_name.toLowerCase().includes(searchTerm));
            
            return matchesFloor && matchesCategory && matchesSearch;
        });
        
        if (filteredRepairAssets.length === 0) {
            assetSelect.innerHTML = '<option value="">No assets found with current filters</option>';
            assetSelect.size = 1;
            updateAssetCount(0);
            return;
        }
        
        // Add filtered assets to dropdown
        assetSelect.size = Math.min(filteredRepairAssets.length + 1, 8);
        
        // Add header option
        const headerOption = document.createElement('option');
        headerOption.disabled = true;
        headerOption.selected = true;
        headerOption.textContent = `--- Select Asset (${filteredRepairAssets.length} found) ---`;
        assetSelect.appendChild(headerOption);
        
        filteredRepairAssets.forEach(asset => {
            const option = document.createElement('option');
            option.value = asset.id;
            
            // Format: TAG - NAME (CATEGORY) [LOCATION]
            let optionText = `${asset.asset_tag} - ${asset.asset_name}`;
            if (asset.category) {
                optionText += ` (${asset.category.category_name})`;
            }
            if (asset.location_within_floor) {
                optionText += ` [${asset.location_within_floor}]`;
            }
            
            option.textContent = optionText;
            assetSelect.appendChild(option);
        });
        
        updateAssetCount(filteredRepairAssets.length);
    }
    
    // Update asset count badge
    function updateAssetCount(count) {
        const badge = document.getElementById('asset_count_badge');
        badge.textContent = `${count} asset${count !== 1 ? 's' : ''} available`;
        
        if (count === 0) {
            badge.className = 'badge bg-secondary';
        } else if (count < 5) {
            badge.className = 'badge bg-warning';
        } else {
            badge.className = 'badge bg-success';
        }
    }

    // Event listeners for repair asset filtering
    document.getElementById('repair_floor_id').addEventListener('change', filterAndRenderRepairAssets);
    document.getElementById('repair_category_filter').addEventListener('change', filterAndRenderRepairAssets);
    
    // Debounce search input
    let searchTimeout;
    document.getElementById('repair_asset_search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterAndRenderRepairAssets, 300);
    });
    
    // Auto-fill location when asset is selected for repair
    document.getElementById('repair_asset_id').addEventListener('change', function() {
        const assetId = this.value;
        const locationInput = document.getElementById('repair_location_within_floor');
        
        if (!assetId) {
            return;
        }
        
        const selectedAsset = allAssets.find(asset => asset.id == assetId);
        
        if (selectedAsset && selectedAsset.location_within_floor) {
            locationInput.value = selectedAsset.location_within_floor;
        }
    });

    // Trigger on page load if old value exists
    if (document.getElementById('ticket_type').value) {
        document.getElementById('ticket_type').dispatchEvent(new Event('change'));
        
        // Trigger repair asset filtering if repair type is selected
        if (document.getElementById('ticket_type').value === 'asset_repair') {
            setTimeout(filterAndRenderRepairAssets, 100);
        }
    }
</script>


@endsection