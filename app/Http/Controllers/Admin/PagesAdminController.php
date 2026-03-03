<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PagesAdminController extends Controller
{
    /**
     * Create a JSON backup of all pages to storage/app/backups/pages-<timestamp>.json
     * If `?download=1` is present, returns the file as a download response.
     */
    public function backup(Request $request)
    {
        $this->ensureAuthorized();

        $dir = 'backups';
        $ts = now()->format('Ymd-His');
        $name = "pages-{$ts}.json";
        $path = $dir.'/'.$name;

        $pages = Page::query()->orderBy('id')->get();
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'count' => $pages->count(),
            'pages' => $pages->map(function(Page $p){
                return $p->getAttributes();
            })->values()->all(),
        ];

        Storage::makeDirectory($dir);
        Storage::put($path, json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

        if ($request->boolean('download')) {
            return Storage::download($path, $name, [
                'Content-Type' => 'application/json',
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Pages backup created',
            'path' => $path,
            'count' => $payload['count'],
        ]);
    }

    /**
     * Clear pages after writing a backup. Requires a confirmation token.
     * - POST body must include `confirm` equal to env('PAGES_CLEAR_TOKEN') to proceed.
     * - If `dry=1`, performs a dry run and returns counts without mutating.
     */
    public function clear(Request $request)
    {
        $this->ensureAuthorized();

        $token = (string) env('PAGES_CLEAR_TOKEN', '');
        $confirm = (string) $request->input('confirm', '');
        $dry = $request->boolean('dry');

        if ($token === '') {
            return response()->json([
                'ok' => false,
                'error' => 'PAGES_CLEAR_TOKEN is not set in environment.',
            ], 400);
        }
        if (!hash_equals($token, $confirm)) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid confirmation token.',
            ], 403);
        }

        $count = Page::query()->count();
        $backupResp = $this->backup(new Request());
        if ($backupResp->getStatusCode() >= 300) {
            return response()->json([
                'ok' => false,
                'error' => 'Backup failed; aborting clear.',
            ], 500);
        }

        if ($dry) {
            return response()->json([
                'ok' => true,
                'dry' => true,
                'message' => 'Dry run: would clear pages after backup',
                'count' => $count,
            ]);
        }

        // Destructive action: delete all rows
        Page::query()->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Pages cleared after backup',
            'cleared' => $count,
        ]);
    }

    private function ensureAuthorized(): void
    {
        // Simple guard: require authenticated user. You can extend with gates/policies as needed.
        if (!Auth::check()) {
            abort(403);
        }
    }
}

