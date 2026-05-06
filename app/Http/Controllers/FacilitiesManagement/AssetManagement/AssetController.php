<?php

namespace App\Http\Controllers\FacilitiesManagement\AssetManagement;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAttribute;
use App\Models\AssetAttributeValue;
use App\Models\Location;
use App\Models\Project;
use App\Models\TableSetting;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\DataTables;

class AssetController extends Controller
{
    // List all assets
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $categoryName = $request->get('category_id');
            $categoryId = 'all';

            if ($categoryName && $categoryName !== 'all') {
                $category = AssetCategory::where('category_name', $categoryName)->first();

                if (!$category) {
                    return DataTables::of(Asset::query()->whereRaw('1 = 0'))
                        ->addIndexColumn()
                        ->with('dynamic_attributes', [])
                        ->make(true);
                }

                $categoryId = $category->id;
            }

            $attributes = [];
            if (is_numeric($categoryId)) {
                $attributes = \App\Models\AssetAttribute::where('category_id', $categoryId)->get();
            }

            $query = Asset::with(['category', 'floor.building', 'parent', 'project', 'attributeValues']);

            if ($categoryId !== 'all') {
                $query->where('category_id', $categoryId);
            }

            $dataTable = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('category', fn($asset) => $asset->category->category_name ?? '')
                ->addColumn('project', fn($asset) => $asset->project->name ?? '')
                ->addColumn('building_floor', fn($asset) => $asset->floor->building->site_name ?? '')
                ->addColumn('site_code', fn($asset) => $asset->floor->building->code ?? '')
                ->addColumn('subcenter', fn($asset) => $asset->floor->building->location->subcenter ?? '')
                // ->addColumn('subcenter', function ($asset) use ($upazillaMap) {
                //     $key = strtolower(trim($asset->floor->building->district ?? '')) . '|' . strtolower(trim($asset->floor->building->upazila ?? ''));
                //     return $upazillaMap[$key] ?? '';
                // })
                ->addColumn('floor', fn($asset) => $asset->floor->floor_label ?? '')
                ->addColumn('parent', fn($asset) => $asset->parent ? ($asset->parent->asset_tag . ' - ' . $asset->parent->asset_name) : '')
                ->editColumn('status', function ($row) {
                    return '<span class="badge bg-' . ($row->status == 'active' ? 'success' : 'danger') . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('actions', function ($asset) {
                    return view('FacilitiesManagement.AssetManagement.Assets.partials.actions', compact('asset'))->render();
                });

            // 3. Dynamically add Attribute Columns
            foreach ($attributes as $attr) {
                $dataTable->addColumn('attr_' . $attr->id, function ($asset) use ($attr) {
                    // Find the value for this specific attribute
                    $val = $asset->attributeValues->where('attribute_id', $attr->id)->where('asset_id', $asset->id)->first();
                    return $val ? $val->value : '-';
                });
            }

            // 4. Attach attribute metadata to the JSON response
            return $dataTable->with('dynamic_attributes', $attributes)
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        $assetSettings = TableSetting::where('table_identifier', 'like', 'assets_table_%')
                                                    ->get()
                                                    ->pluck('settings', 'table_identifier');

        return view('FacilitiesManagement.AssetManagement.Assets.index', compact('assetSettings'));
    }

    // Show create asset form
    public function create(Request $request)
    {
        $categories = AssetCategory::all();
        $floors = \App\Models\PropertiesFloor::all();
        $assets = Asset::all();
        $attributes = \App\Models\AssetAttribute::all();
        $projects = Project::where('status', 1)->get();

        $preselectedCategoryId = null;
        if ($request->has('category')) {
            $cat = AssetCategory::where('category_name', $request->category)->first();
            if ($cat) {
                $preselectedCategoryId = $cat->id;
            }
        }

        return view('FacilitiesManagement.AssetManagement.Assets.create', compact('categories', 'floors', 'assets', 'attributes', 'projects', 'preselectedCategoryId'));
    }

    // Store new asset
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:assets',
            'asset_name' => 'required',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable',
            'model' => 'nullable',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'floor_id' => 'nullable|exists:properties_floors,id',
            'location_within_floor' => 'nullable',
            'parent_id' => 'nullable|exists:assets,id',
            'status' => 'required',
            'project_id' => 'nullable',
        ]);
        $asset = Asset::create($validated);
        // Store attribute values

        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attributeId => $value) {

                if ($value !== null && $value !== '') {
                    \App\Models\AssetAttributeValue::create([
                        'asset_id' => $asset->id,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }
        }
        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    // Show asset details
    public function show($id)
    {
        $asset = Asset::with(['category', 'floor', 'parent', 'children', 'attributeValues.attribute'])->findOrFail($id);
        return view('FacilitiesManagement.AssetManagement.Assets.show', compact('asset'));
    }

    // Show edit asset form
    public function edit($id)
    {
        $asset = Asset::with(['attributeValues'])->findOrFail($id);
        $categories = AssetCategory::all();
        $floors = \App\Models\PropertiesFloor::all();
        $assets = Asset::all();
        $attributes = \App\Models\AssetAttribute::all();
        $projects = Project::where('status', 1)->get();
        return view('FacilitiesManagement.AssetManagement.Assets.edit', compact('asset', 'categories', 'floors', 'assets', 'attributes', 'projects'));
    }

    // Update asset
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:assets,asset_tag,' . $id,
            'asset_name' => 'required',
            'category_id' => 'required|exists:asset_categories,id',
            'brand' => 'nullable',
            'model' => 'nullable',
            'serial_number' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'floor_id' => 'nullable|exists:properties_floors,id',
            'location_within_floor' => 'nullable',
            'parent_id' => 'nullable|exists:assets,id',
            'status' => 'required',
            'project_id' => 'nullable',
        ]);
        $asset = Asset::findOrFail($id);
        $asset->update($validated);
        // Update attribute values
        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attributeId => $value) {
                if ($value !== null && $value !== '') {
                    \App\Models\AssetAttributeValue::updateOrCreate(
                        [
                            'asset_id' => $asset->id,
                            'attribute_id' => $attributeId,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                } else {
                    // If value is empty, delete the attribute value if exists
                    \App\Models\AssetAttributeValue::where('asset_id', $asset->id)
                        ->where('attribute_id', $attributeId)
                        ->delete();
                }
            }
        }
        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    // Delete asset
    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }


    private function callAPI($method, $url, $data, $accessToken = null)
    {
        try{
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            switch ($method) {
                case "GET":
                    curl_setopt($curl, CURLOPT_POST, 0);
                    break;

                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }

            // OPTIONS:
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $accessToken
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            // EXECUTE:
            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (!$result) {
                throw new \Exception("API responded with failure. HTTP Code: $httpCode. Response: " . $result);
                // die("Connection Failure");
            }
            curl_close($curl);
            return $result;

        }catch (\Exception $e) {
            Log::channel('custom_api_error')->error('Externel API call error : ' . $e->getMessage(), [
                'apiUrl' => $url,
                'class' => 'Helper',
                'function' => 'callAPI',
                'timestamp' => now(),
            ]);
            // return null;
            throw $e;
        }
    }


    public function getHistory($id)
    {
        $history = Helpers::getHistory(Asset::class, $id);
        return $history;
    }

}
