<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $templates = EmailTemplate::all();
        return view('admin.email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:email_templates',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        EmailTemplate::create($request->all());

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template created successfully!');
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.show', compact('emailTemplate'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:email_templates,slug,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->update($request->all());

        return redirect()->route('admin.email-templates.show', $emailTemplate)->with('success', 'Email template updated successfully!');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')->with('success', 'Email template deleted successfully!');
    }
}