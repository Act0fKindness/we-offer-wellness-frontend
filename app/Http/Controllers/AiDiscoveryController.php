<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class AiDiscoveryController extends Controller
{
    public function llms()
    {
        return $this->serveText(public_path('llms.txt'));
    }

    public function llmsSmall()
    {
        return $this->serveText(public_path('llms-small.txt'));
    }

    public function llmsFull()
    {
        return $this->serveText(public_path('llms-full.txt'));
    }

    public function policy()
    {
        return $this->serveText(public_path('.well-known/ai.txt'));
    }

    private function serveText(string $path)
    {
        if (! File::isFile($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
