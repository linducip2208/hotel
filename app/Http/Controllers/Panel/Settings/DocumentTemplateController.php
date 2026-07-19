<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.settings.doc-templates', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:folio,invoice,beo,contract,registration_card,email_confirmation',
            'locale' => 'nullable|string',
            'header_html' => 'nullable|string',
            'body_html' => 'required|string',
            'footer_html' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);
        DocumentTemplate::create($data + ['property_id' => app('current_property')->id, 'is_active' => true]);
        return back();
    }
}
