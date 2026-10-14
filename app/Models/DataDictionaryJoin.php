<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataDictionaryJoin extends Model
{
    use HasFactory;

    protected $table = 'data_dictionary_joins';

    protected $fillable = [
        'from_table',
        'to_table',
        'from_column',
        'to_column',
        'join_type',
        'description',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * Get complete join adjacency map for BFS search.
     * Both forward and backward edges are supported so BFS can traverse relationships in either direction.
     *
     * @return array
     */
    public static function getJoinAdjacencyMap(): array
    {
        $joins = static::orderBy('priority', 'asc')->get();
        $map = [];

        foreach ($joins as $join) {
            // Forward edge (from_table -> to_table)
            $map[$join->from_table][] = [
                'target_table' => $join->to_table,
                'from_table'   => $join->from_table,
                'from_column'  => $join->from_column,
                'to_table'     => $join->to_table,
                'to_column'    => $join->to_column,
                'join_type'    => $join->join_type ?: 'LEFT',
                'priority'     => $join->priority,
            ];

            // Reverse edge (to_table -> from_table)
            $map[$join->to_table][] = [
                'target_table' => $join->from_table,
                'from_table'   => $join->to_table,
                'from_column'  => $join->to_column,
                'to_table'     => $join->from_table,
                'to_column'    => $join->from_column,
                'join_type'    => $join->join_type ?: 'LEFT',
                'priority'     => $join->priority,
            ];
        }

        return $map;
    }
}
