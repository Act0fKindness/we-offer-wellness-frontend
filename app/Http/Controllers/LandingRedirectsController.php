<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;

class LandingRedirectsController extends Controller
{
    public function offeringHandle(string $type, string $handle)
    {
        $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
        if (!in_array(strtolower($type), $allowed, true)) abort(404);
        $p = Product::query()->where('handle', $handle)
            ->orWhere(fn($q)=>$q->where('id', is_numeric($handle) ? (int)$handle : 0))
            ->first();
        if (!$p) abort(404);
        $slug = Str::slug($p->title ?: (string)$p->id);
        return redirect('/'.strtolower($type).'/'.$p->id.'-'.$slug, 301);
    }

    public function experiencesIndex()
    {
        return redirect('/gifts', 301);
    }

    public function experienceIndex()
    {
        return redirect('/gifts', 301);
    }

    public function experienceSlug(string $slug)
    {
        return redirect('/experiences/'.ltrim($slug, '/'), 301);
    }

    public function experiencesSlug(string $slug)
    {
        $s = strtolower($slug);
        if (str_contains($s, 'sound-bath')) return redirect('/events/sound-bath', 301);
        if ($s === 'reiki') return redirect('/therapies/reiki', 301);
        if (str_contains($s, 'breathwork')) return redirect('/events/breathwork-workshops', 301);
        if (str_contains($s, 'retreat')) return redirect('/retreats', 301);
        return redirect('/therapies/'.$s, 301);
    }
}

