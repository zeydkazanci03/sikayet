<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class SafeController extends Controller
{
    /**
     * Database queries'lerini safely execute et
     * Fail olursa empty array return et
     */
    protected function safeQuery($callback, $default = [])
    {
        try {
            return $callback();
        } catch (QueryException $e) {
            Log::warning("Database query failed: " . $e->getMessage());
            return $default;
        } catch (\Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * View oluştur - collection boş olsa da yüklensin
     */
    protected function safeView($view, $data = [])
    {
        // Empty collections için safeguards
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $data[$key] = collect([]);
            }
        }
        return view($view, $data);
    }
}
