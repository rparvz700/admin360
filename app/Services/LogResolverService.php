<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\LogFieldMapping;

class LogResolverService
{
    public static function resolve($fieldName, $id)
    {
        if (!$id) return 'None';

        // Clear cache if you just changed the DB
        // Cache::forget('log_field_mappings'); 

        $mappings = Cache::remember('log_field_mappings', 86400, function () {
            return LogFieldMapping::all()->keyBy('field_name')->toArray();
        });

        if (isset($mappings[$fieldName])) {
            $mapping = $mappings[$fieldName];
            
            // FIX: Use stripslashes to remove extra backslashes and replace double with single
            $modelClass = str_replace('\\\\', '\\', $mapping['related_model']);
            $column = $mapping['display_column'];

            // Double check if the class exists now
            if (class_exists($modelClass)) {
                try {
                    $record = $modelClass::find($id);
                    return $record ? $record->{$column} : "Deleted Record ($id)";
                } catch (\Exception $e) {
                    return "Error ($id)";
                }
            } else {
                // Helpful debugging for your logs
                \Log::error("LogResolver Error: Class '$modelClass' not found for field '$fieldName'");
                return "Config Error ($id)";
            }
        }

        return $id;
    }
}