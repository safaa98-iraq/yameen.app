<?php

namespace App\Http\Controllers;

use App\Support\Content\ContentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function __construct(private readonly ContentRepository $content) {}

    public function fix(Request $request): View
    {
        $software = $request->route('software');
        $page = $this->content->get($software ? 'fix-'.$software : 'fix');

        return view('pages.fix', [
            'page' => $page,
            'software' => $software,
            'softwareName' => $software ? config('yameen.software.'.$software) : null,
        ]);
    }

    public function pdfFix(): View
    {
        return view('pages.pdf-fix', ['page' => $this->content->get('pdf-fix')]);
    }

    public function clean(): View
    {
        return view('pages.clean', ['page' => $this->content->get('clean')]);
    }
}
