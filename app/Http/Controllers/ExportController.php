<?php

namespace App\Http\Controllers;

use App\Models\Font;
use App\Support\FontCatalog;
use Illuminate\Contracts\View\View;

class ExportController extends Controller
{
    public function __construct(private readonly FontCatalog $fonts) {}

    public function export(): View
    {
        $fonts = $this->fonts->arabic()->map(fn (Font $f) => $f->toClient() + [
            'ttf' => collect($f->weights)->mapWithKeys(fn ($w) => [$w => $f->fileUrl($w, 'ttf')])->filter()->all(),
        ])->filter(fn ($f) => $f['ttf'])->values();

        return view('pages.export', ['fonts' => $fonts, 'default' => $this->fonts->defaultForApps()?->slug]);
    }

    public function batch(): View
    {
        return view('pages.batch');
    }
}
