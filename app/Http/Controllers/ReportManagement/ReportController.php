<?php

namespace App\Http\Controllers\ReportManagement;

use App\Exports\CustomReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportBuilderRequest;
use App\Models\DataDictionary;
use App\Models\DataDictionaryJoin;
use App\Models\ReportTemplate;
use App\Services\DataDictionarySyncService;
use App\Services\ReportQueryCompiler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-reports')->only(['hub', 'preview']);
        $this->middleware('permission:export-reports')->only(['export']);
        $this->middleware('permission:manage-report-templates')->only(['builder', 'saveTemplate', 'deleteTemplate']);
        $this->middleware('permission:manage-data-dictionary')->only([
            'dictionary', 'dictionaryList', 'updateDictionaryField', 'syncDictionary', 'joins', 'saveJoin', 'deleteJoin'
        ]);
    }

    /**
     * Display the Reports Hub (Catalog & Instant Viewer for general users).
     */
    public function hub(Request $request)
    {
        if (!Schema::hasTable('report_templates')) {
            return view('ReportManagement.hub', [
                'activeMenu'       => 'reports-hub',
                'templates'        => collect(),
                'sharedTemplates'  => collect(),
                'myTemplates'      => collect(),
                'initialTemplate'  => null,
            ]);
        }

        // Fetch templates visible to current user (shared + owned private)
        $templates = ReportTemplate::visibleTo(Auth::id())
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        $sharedTemplates = $templates->where('is_shared', true)->values();
        $myTemplates     = $templates->where('created_by', Auth::id())->where('is_shared', false)->values();

        $selectedId = $request->query('template');
        $initialTemplate = null;
        if ($selectedId) {
            $initialTemplate = $templates->firstWhere('id', (int)$selectedId);
        }

        return view('ReportManagement.hub', [
            'activeMenu'       => 'reports-hub',
            'templates'        => $templates,
            'sharedTemplates'  => $sharedTemplates,
            'myTemplates'      => $myTemplates,
            'initialTemplate'  => $initialTemplate,
        ]);
    }

    /**
     * Display the visual Report Builder interface.
     */
    public function builder(Request $request)
    {
        if (!Schema::hasTable('data_dictionaries')) {
            return view('ReportManagement.builder', [
                'activeMenu'      => 'report-builder',
                'dictionary'      => collect(),
                'templates'       => collect(),
                'tables'          => collect(),
                'initialTemplate' => null,
            ]);
        }

        // Auto-seed dictionary on first run if dictionary is empty
        if (DataDictionary::count() === 0) {
            app(DataDictionarySyncService::class)->sync();
        }

        // Fetch all reportable fields grouped by module then by table
        $dictionary = DataDictionary::reportable()
            ->orderBy('module_name')
            ->orderBy('table_name')
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get()
            ->groupBy(['module_name', 'table_name']);

        // Fetch templates visible to current user (shared + owned private)
        $templates = ReportTemplate::visibleTo(Auth::id())
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        // Available tables list with humanized labels
        $tables = DataDictionary::select('table_name', 'module_name')
            ->distinct()
            ->orderBy('module_name')
            ->orderBy('table_name')
            ->get()
            ->map(function ($t) {
                return [
                    'table_name'  => $t->table_name,
                    'module_name' => $t->module_name,
                    'label'       => Str::headline($t->table_name),
                ];
            });

        $selectedId = $request->query('template');
        $initialTemplate = null;
        if ($selectedId) {
            $initialTemplate = $templates->firstWhere('id', (int)$selectedId);
        }

        return view('ReportManagement.builder', [
            'activeMenu'      => 'report-builder',
            'dictionary'      => $dictionary,
            'templates'       => $templates,
            'tables'          => $tables,
            'initialTemplate' => $initialTemplate,
        ]);
    }

    /**
     * Compile AST and return JSON preview (capped at 50 rows).
     */
    public function preview(ReportBuilderRequest $request, ReportQueryCompiler $compiler): JsonResponse
    {
        try {
            $config = $request->all();
            if ($request->filled('template_id')) {
                $template = ReportTemplate::visibleTo(Auth::id())->findOrFail($request->input('template_id'));
                $config = $this->resolveTemplateConfigWithRuntimeFilters($template, $request);
            }

            $compiled = $compiler->compile($config);

            $query   = $compiled['query'];
            $headers = $compiled['headers'];
            $sql     = $compiled['sql'];

            $isDataTable = $request->has('draw');

            if ($isDataTable) {
                // 1. Calculate unfiltered base record count using count subquery
                try {
                    $countRow = DB::selectOne("SELECT COUNT(*) as agg_count FROM ({$query->toSql()}) as count_base", $query->getBindings());
                    $totalCount = (int) ($countRow->agg_count ?? 0);
                } catch (\Throwable $ce) {
                    $totalCount = (clone $query)->count();
                }

                // 2. Global search across columns if search[value] is present
                $searchVal = trim($request->input('search.value', ''));
                if ($searchVal !== '') {
                    $query->where(function ($subQ) use ($searchVal, $headers) {
                        foreach ($headers as $h) {
                            if (empty($h['aggregate'])) {
                                $subQ->orWhereRaw("CAST({$h['table']}.{$h['column']} AS TEXT) ILIKE ?", ["%{$searchVal}%"]);
                            }
                        }
                    });
                }

                // 3. Calculate filtered record count
                $filteredCount = $totalCount;
                if ($searchVal !== '') {
                    try {
                        $filteredRow = DB::selectOne("SELECT COUNT(*) as agg_count FROM ({$query->toSql()}) as count_filtered", $query->getBindings());
                        $filteredCount = (int) ($filteredRow->agg_count ?? 0);
                    } catch (\Throwable $ce) {
                        $filteredCount = (clone $query)->count();
                    }
                }

                // 4. Server-side column sorting
                $orderColIdx = $request->input('order.0.column');
                $orderDir    = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                if ($orderColIdx !== null) {
                    // Column 0 in DataTables is index '#'
                    $dataIdx = ((int) $orderColIdx) - 1;
                    if ($dataIdx >= 0 && isset($headers[$dataIdx])) {
                        $target = $headers[$dataIdx];
                        if (!empty($target['aggregate'])) {
                            $query->reorder($target['key'], $orderDir);
                        } else {
                            $query->reorder("{$target['table']}.{$target['column']}", $orderDir);
                        }
                    }
                }

                // 5. Server-side pagination
                $start  = max(0, (int) $request->input('start', 0));
                $length = (int) $request->input('length', 25);
                if ($length > 0) {
                    $query->skip($start)->take($length);
                }

                $rows = $query->get();

                return response()->json([
                    'draw'            => (int) $request->input('draw', 1),
                    'recordsTotal'    => $totalCount,
                    'recordsFiltered' => $filteredCount,
                    'data'            => $rows,
                    'headers'         => $headers,
                    'base_table'      => $compiled['base_table'],
                    'sql'             => $sql,
                    'success'         => true,
                ]);
            }

            // Non-DataTables preview (used by Report Builder Sandbox)
            try {
                $countRow = DB::selectOne("SELECT COUNT(*) as agg_count FROM ({$query->toSql()}) as count_base", $query->getBindings());
                $totalCount = (int) ($countRow->agg_count ?? 0);
            } catch (\Throwable $ce) {
                $totalCount = (clone $query)->count();
            }

            $rows = (clone $query)->limit(50)->get();

            return response()->json([
                'success'       => true,
                'headers'       => $headers,
                'rows'          => $rows,
                'sql'           => $sql,
                'preview_count' => count($rows),
                'total_count'   => $totalCount,
                'base_table'    => $compiled['base_table'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export custom query to CSV or Excel without row limits.
     * Uses chunked streaming to prevent memory exhaustion.
     */
    public function export(ReportBuilderRequest $request, ReportQueryCompiler $compiler)
    {
        try {
            $config = $request->all();
            $template = null;
            if ($request->filled('template_id')) {
                $template = ReportTemplate::visibleTo(Auth::id())->findOrFail($request->input('template_id'));
                $config = $this->resolveTemplateConfigWithRuntimeFilters($template, $request);
            }

            $compiled = $compiler->compile($config);
            $query    = $compiled['query'];
            $headers  = $compiled['headers'];
            $format   = strtolower($request->input('export_format', 'csv'));
            $filename = ($template ? Str::slug($template->name) : 'report') . '_' . date('Ymd_His');

            // Apply active table search filter if present
            $searchVal = trim($request->input('search_value', $request->input('search.value', '')));
            if ($searchVal !== '') {
                $query->where(function ($subQ) use ($searchVal, $headers) {
                    foreach ($headers as $h) {
                        if (empty($h['aggregate'])) {
                            $subQ->orWhereRaw("CAST({$h['table']}.{$h['column']} AS TEXT) ILIKE ?", ["%{$searchVal}%"]);
                        }
                    }
                });
            }

            // Apply active column sorting if present
            $sortColIdx = $request->input('export_sort_column', $request->input('order.0.column'));
            $sortDir    = strtolower($request->input('export_sort_direction', $request->input('order.0.dir', 'asc'))) === 'desc' ? 'desc' : 'asc';
            if ($sortColIdx !== null && $sortColIdx !== '') {
                $dataIdx = ((int) $sortColIdx) - 1;
                if ($dataIdx >= 0 && isset($headers[$dataIdx])) {
                    $target = $headers[$dataIdx];
                    if (!empty($target['aggregate'])) {
                        $query->reorder($target['key'], $sortDir);
                    } else {
                        $query->reorder("{$target['table']}.{$target['column']}", $sortDir);
                    }
                }
            }

            if ($format === 'excel' || $format === 'xlsx') {
                return Excel::download(
                    new CustomReportExport($query, $headers),
                    "{$filename}.xlsx"
                );
            }

            // Streamed CSV with UTF-8 BOM
            $response = new StreamedResponse(function () use ($query, $headers) {
                $handle = fopen('php://output', 'w');

                // Write UTF-8 Byte Order Mark for Excel compatibility
                fwrite($handle, "\xEF\xBB\xBF");

                // Header row
                $headerTitles = array_map(function ($h) {
                    $alias = $h['alias'];
                    if (!empty($h['aggregate'])) {
                        $agg = strtoupper($h['aggregate']);
                        return "{$alias} ({$agg})";
                    }
                    return $alias;
                }, $headers);

                fputcsv($handle, $headerTitles);

                // Stream rows using cursor for flat memory footprint with zero offset overhead
                foreach ($query->cursor() as $row) {
                    $line = [];
                    foreach ($headers as $h) {
                        $key = $h['key'];
                        $val = $row->$key ?? null;

                        if ($h['data_type'] === 'decimal' && is_numeric($val)) {
                            $line[] = number_format((float)$val, 2, '.', '');
                        } else {
                            $line[] = $val ?? '';
                        }
                    }
                    fputcsv($handle, $line);
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Save or update a Report Template.
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'query_config' => 'required|array',
            'is_shared'    => 'nullable|boolean',
            'template_id'  => 'nullable|integer',
        ]);

        $templateId = $request->input('template_id');
        $isShared   = (bool)$request->input('is_shared', false);

        if ($templateId) {
            $template = ReportTemplate::findOrFail($templateId);

            // Only creator or Super Admin can edit
            if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to overwrite this template.',
                ], 403);
            }

            $template->update([
                'name'         => $request->input('name'),
                'description'  => $request->input('description'),
                'query_config' => $request->input('query_config'),
                'is_shared'    => $isShared,
            ]);
        } else {
            $template = ReportTemplate::create([
                'name'         => $request->input('name'),
                'description'  => $request->input('description'),
                'query_config' => $request->input('query_config'),
                'is_shared'    => $isShared,
                'created_by'   => Auth::id(),
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Template saved successfully!',
            'template' => $template->load('creator:id,name'),
        ]);
    }

    /**
     * Delete a Report Template.
     */
    public function deleteTemplate($id): JsonResponse
    {
        $template = ReportTemplate::findOrFail($id);

        if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this template.',
            ], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Trigger schema introspection and data dictionary sync.
     */
    public function syncDictionary(DataDictionarySyncService $syncService): JsonResponse
    {
        $result = $syncService->sync();

        return response()->json([
            'success' => true,
            'message' => 'Data Dictionary synchronized successfully.',
            'metrics' => $result,
        ]);
    }

    /**
     * Data Dictionary Admin Management View.
     */
    public function dictionary(Request $request)
    {
        if ($request->ajax()) {
            return $this->dictionaryList($request);
        }

        if (!Schema::hasTable('data_dictionaries') || !Schema::hasTable('data_dictionary_joins')) {
            return view('ReportManagement.dictionary', [
                'activeMenu'   => 'data-dictionary',
                'modules'      => collect(),
                'tables'       => collect(),
                'joins'        => collect(),
                'totalColumns' => 0,
            ]);
        }

        $modules      = DataDictionary::select('module_name')->distinct()->orderBy('module_name')->pluck('module_name');
        $tables       = DataDictionary::select('table_name')->distinct()->orderBy('table_name')->pluck('table_name');
        $joins        = DataDictionaryJoin::orderBy('from_table')->get();
        $totalColumns = DataDictionary::count();

        return view('ReportManagement.dictionary', [
            'activeMenu'   => 'data-dictionary',
            'modules'      => $modules,
            'tables'       => $tables,
            'joins'        => $joins,
            'totalColumns' => $totalColumns,
        ]);
    }

    /**
     * DataTables Server-Side Processing feed for Data Dictionary.
     */
    public function dictionaryList(Request $request)
    {
        $query = DataDictionary::query();

        if ($request->filled('module_name') && $request->module_name !== 'all') {
            $query->where('module_name', $request->module_name);
        }

        if ($request->filled('table_name') && $request->table_name !== 'all') {
            $query->where('table_name', $request->table_name);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->filterColumn('table_col', function ($q, $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('column_name', 'like', "%{$keyword}%")
                        ->orWhere('table_name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('table_col', function ($q, $order) {
                $q->orderBy('table_name', $order)->orderBy('column_name', $order);
            })
            ->filterColumn('display_label', function ($q, $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('display_name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('display_label', function ($q, $order) {
                $q->orderBy('display_name', $order);
            })
            ->filterColumn('module_badge', function ($q, $keyword) {
                $q->where('module_name', 'like', "%{$keyword}%");
            })
            ->orderColumn('module_badge', function ($q, $order) {
                $q->orderBy('module_name', $order);
            })
            ->filterColumn('data_type_badge', function ($q, $keyword) {
                $q->where('data_type', 'like', "%{$keyword}%");
            })
            ->orderColumn('data_type_badge', function ($q, $order) {
                $q->orderBy('data_type', $order);
            })
            ->addColumn('module_badge', function ($row) {
                return '<span class="badge bg-primary text-white fw-semibold px-2 py-1">' . e($row->module_name) . '</span>';
            })
            ->addColumn('table_col', function ($row) {
                return '<div class="fw-semibold text-dark">' . e($row->column_name) . '</div>' .
                       '<div class="text-muted font-monospace fs-xs">' . e($row->table_name) . '</div>';
            })
            ->addColumn('display_label', function ($row) {
                $desc = $row->description ? '<div class="text-muted fs-xs text-truncate" style="max-width: 250px;" title="' . e($row->description) . '">' . e($row->description) . '</div>' : '';
                return '<div class="fw-bold">' . e($row->display_name) . '</div>' . $desc;
            })
            ->addColumn('data_type_badge', function ($row) {
                return '<span class="badge bg-light text-dark border">' . strtoupper(e($row->data_type)) . '</span>';
            })
            ->addColumn('reportable_badge', function ($row) {
                return $row->is_reportable
                    ? '<span class="badge bg-success text-white"><i class="fa fa-check me-1"></i> Yes</span>'
                    : '<span class="badge bg-danger text-white"><i class="fa fa-xmark me-1"></i> Hidden</span>';
            })
            ->addColumn('filterable_badge', function ($row) {
                return $row->is_filterable
                    ? '<span class="badge bg-info text-white"><i class="fa fa-check me-1"></i> Yes</span>'
                    : '<span class="badge bg-secondary text-white"><i class="fa fa-xmark me-1"></i> No</span>';
            })
            ->addColumn('aggregatable_badge', function ($row) {
                return $row->is_aggregatable
                    ? '<span class="badge bg-warning text-dark"><i class="fa fa-calculator me-1"></i> Yes</span>'
                    : '<span class="badge bg-light text-muted border">—</span>';
            })
            ->addColumn('actions', function ($row) {
                $dataField = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                return '<button type="button" class="btn btn-xs btn-alt-primary btn-edit-field" data-field=\'' . $dataField . '\'>' .
                       '<i class="fa fa-pen-to-square me-1"></i> Edit' .
                       '</button>';
            })
            ->rawColumns(['module_badge', 'table_col', 'display_label', 'data_type_badge', 'reportable_badge', 'filterable_badge', 'aggregatable_badge', 'actions'])
            ->make(true);
    }

    /**
     * Update an individual Data Dictionary field configuration.
     */
    public function updateDictionaryField(Request $request, $id): JsonResponse
    {
        $field = DataDictionary::findOrFail($id);

        $validated = $request->validate([
            'display_name'    => 'required|string|max:255',
            'description'     => 'nullable|string',
            'module_name'     => 'required|string|max:100',
            'is_reportable'   => 'required|boolean',
            'is_filterable'   => 'required|boolean',
            'is_aggregatable' => 'required|boolean',
            'sort_order'      => 'nullable|integer',
        ]);

        $field->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully.',
            'field'   => $field,
        ]);
    }

    /**
     * Save or update a Join Relationship.
     */
    public function saveJoin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_table'  => 'required|string|max:255',
            'to_table'    => 'required|string|max:255',
            'from_column' => 'required|string|max:255',
            'to_column'   => 'required|string|max:255',
            'join_type'   => 'required|string|in:LEFT,INNER',
            'description' => 'nullable|string|max:255',
            'priority'    => 'nullable|integer',
            'join_id'     => 'nullable|integer',
        ]);

        if (!empty($validated['join_id'])) {
            $join = DataDictionaryJoin::findOrFail($validated['join_id']);
            $join->update($validated);
        } else {
            $join = DataDictionaryJoin::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Join relationship saved successfully.',
            'join'    => $join,
        ]);
    }

    /**
     * Delete a Join Relationship.
     */
    public function deleteJoin($id): JsonResponse
    {
        $join = DataDictionaryJoin::findOrFail($id);
        $join->delete();

        return response()->json([
            'success' => true,
            'message' => 'Join relationship removed successfully.',
        ]);
    }

    /**
     * Merge runtime date filters into a template's query configuration.
     */
    protected function resolveTemplateConfigWithRuntimeFilters(ReportTemplate $template, Request $request): array
    {
        $config = $template->query_config ?? [];

        $dateFrom = $request->input('runtime_date_from');
        $dateTo   = $request->input('runtime_date_to');
        $dateCol  = $request->input('runtime_date_column');

        // If no explicit date column is specified, try to find one from template or date_columns_summary
        if (($dateFrom || $dateTo) && empty($dateCol)) {
            $existingDateCol = $this->findExistingDateFilterColumn($config['filters'] ?? []);
            if ($existingDateCol) {
                $dateCol = $existingDateCol;
            } else {
                $dateCol = $this->findFirstDateSelectedField($config['selected_fields'] ?? []);
            }

            if (empty($dateCol) && !empty($template->date_columns_summary)) {
                $firstSummary = $template->date_columns_summary[0];
                $dateCol = "{$firstSummary['table']}.{$firstSummary['column']}";
            }
        }

        if (($dateFrom || $dateTo) && !empty($dateCol)) {
            $parts = explode('.', $dateCol, 2);
            if (count($parts) === 2) {
                $table = $parts[0];
                $col   = $parts[1];

                // Check if column is datetime or timestamp to correctly handle end-of-day boundary
                $dict = DataDictionary::where('table_name', $table)->where('column_name', $col)->first();
                $isTimestamp = $dict && in_array(strtolower($dict->data_type), ['datetime', 'timestamp'], true);

                $formattedFrom = $dateFrom;
                $formattedTo   = $dateTo;

                if ($isTimestamp) {
                    if ($formattedFrom && strlen($formattedFrom) === 10) {
                        $formattedFrom = "{$formattedFrom} 00:00:00";
                    }
                    if ($formattedTo && strlen($formattedTo) === 10) {
                        $formattedTo = "{$formattedTo} 23:59:59";
                    }
                }

                if (!isset($config['filters']) || !is_array($config['filters'])) {
                    $config['filters'] = ['logical_operator' => 'AND', 'conditions' => []];
                }
                if (!isset($config['filters']['conditions']) || !is_array($config['filters']['conditions'])) {
                    $config['filters']['conditions'] = [];
                }

                // If template already has a filter condition for this column, override its value or operator
                $foundExisting = false;
                foreach ($config['filters']['conditions'] as &$cond) {
                    if (isset($cond['table'], $cond['column']) && $cond['table'] === $table && $cond['column'] === $col) {
                        if ($formattedFrom && $formattedTo) {
                            $cond['operator'] = 'between';
                            $cond['value'] = "{$formattedFrom},{$formattedTo}";
                        } elseif ($formattedFrom) {
                            $cond['operator'] = '>=';
                            $cond['value'] = $formattedFrom;
                        } elseif ($formattedTo) {
                            $cond['operator'] = '<=';
                            $cond['value'] = $formattedTo;
                        }
                        $foundExisting = true;
                        break;
                    }
                }
                unset($cond);

                if (!$foundExisting) {
                    if ($formattedFrom && $formattedTo) {
                        $config['filters']['conditions'][] = [
                            'table'    => $table,
                            'column'   => $col,
                            'operator' => 'between',
                            'value'    => "{$formattedFrom},{$formattedTo}",
                        ];
                    } elseif ($formattedFrom) {
                        $config['filters']['conditions'][] = [
                            'table'    => $table,
                            'column'   => $col,
                            'operator' => '>=',
                            'value'    => $formattedFrom,
                        ];
                    } elseif ($formattedTo) {
                        $config['filters']['conditions'][] = [
                            'table'    => $table,
                            'column'   => $col,
                            'operator' => '<=',
                            'value'    => $formattedTo,
                        ];
                    }
                }
            }
        }

        return $config;
    }

    protected function findExistingDateFilterColumn(array $group): ?string
    {
        $conditions = $group['conditions'] ?? [];
        foreach ($conditions as $item) {
            if (isset($item['logical_operator'], $item['conditions'])) {
                $nested = $this->findExistingDateFilterColumn($item);
                if ($nested) return $nested;
            } elseif (isset($item['table'], $item['column'])) {
                $dict = DataDictionary::where('table_name', $item['table'])
                    ->where('column_name', $item['column'])
                    ->first();
                if ($dict && in_array($dict->data_type, ['date', 'datetime', 'timestamp'], true)) {
                    return "{$item['table']}.{$item['column']}";
                }
            }
        }
        return null;
    }

    protected function findFirstDateSelectedField(array $fields): ?string
    {
        foreach ($fields as $f) {
            if (!empty($f['table']) && !empty($f['column'])) {
                $type = $f['data_type'] ?? null;
                if (in_array($type, ['date', 'datetime', 'timestamp'], true)) {
                    return "{$f['table']}.{$f['column']}";
                }
                $dict = DataDictionary::where('table_name', $f['table'])
                    ->where('column_name', $f['column'])
                    ->first();
                if ($dict && in_array($dict->data_type, ['date', 'datetime', 'timestamp'], true)) {
                    return "{$f['table']}.{$f['column']}";
                }
            }
        }
        return null;
    }
}
