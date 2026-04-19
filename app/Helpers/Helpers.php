<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

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


    public static function callAPI($method, $url, $data, $accessToken = null)
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

}