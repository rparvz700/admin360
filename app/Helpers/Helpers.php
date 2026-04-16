<?php

namespace App\Helpers;


class Helpers
{

    public static function getHistory($model, $id)
    {
        $model = $model::findOrFail($id);
        $activities = $model->activities()->with('causer')->latest()->get();

        return $activities->map(function ($activity) {
            $details = [];
            $newValues = $activity->changes['attributes'] ?? [];
            $oldValues = $activity->changes['old'] ?? [];

            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? null;

                $details[] = [
                    'field' => ucfirst(str_replace('_', ' ', $field)),
                    'from'  => \App\Services\LogResolverService::resolve($field, $oldValue),
                    'to'    => \App\Services\LogResolverService::resolve($field, $newValue),
                ];
            }

            return [
                'user' => $activity->causer->name ?? 'System',
                'date' => $activity->created_at->format('d M Y, h:i A'),
                'changes' => $details
            ];
        });
    }

}