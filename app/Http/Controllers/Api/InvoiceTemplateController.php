<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use App\Services\InvoiceTemplateBindingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceTemplateController extends Controller
{
    public function __construct(
        protected InvoiceTemplateBindingService $binding,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = InvoiceTemplate::query()->with('role');
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        $templates = $query->orderBy('type')->orderBy('name')->paginate($request->integer('per_page', 15));
        return response()->json($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|string|in:' . implode(',', array_keys(InvoiceTemplate::types())),
            'logo_path' => 'nullable|string|max:512',
            'colors' => 'nullable|array',
            'header_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'is_default' => 'boolean',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        if (!empty($validated['is_default'])) {
            InvoiceTemplate::where('type', $validated['type'])->update(['is_default' => false]);
        }
        $template = InvoiceTemplate::create($validated);
        $this->saveVersion($template, 'Initial version', $template->getAttributes());
        return response()->json($template->load('role'), 201);
    }

    public function show(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $invoiceTemplate->load('role');
        return response()->json($invoiceTemplate);
    }

    public function update(Request $request, InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'type' => 'sometimes|string|in:' . implode(',', array_keys(InvoiceTemplate::types())),
            'logo_path' => 'nullable|string|max:512',
            'colors' => 'nullable|array',
            'header_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'is_default' => 'boolean',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        if (!empty($validated['is_default'])) {
            InvoiceTemplate::where('type', $invoiceTemplate->type)->where('id', '!=', $invoiceTemplate->id)->update(['is_default' => false]);
        }
        $invoiceTemplate->update($validated);
        $invoiceTemplate->increment('version');
        $invoiceTemplate->refresh();
        $this->saveVersion($invoiceTemplate, $request->input('version_comment', 'Update'), $invoiceTemplate->getAttributes());
        return response()->json($invoiceTemplate->load('role'));
    }

    public function destroy(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $invoiceTemplate->delete();
        return response()->json(null, 204);
    }

    public function versions(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $versions = $invoiceTemplate->versions()->orderByDesc('version')->paginate(10);
        return response()->json($versions);
    }

    /**
     * Get active template for type (and optionally role). For role assignment.
     */
    public function forType(Request $request, string $type): JsonResponse
    {
        $query = InvoiceTemplate::where('type', $type)->where('is_active', true);
        if ($request->filled('role_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('role_id', $request->input('role_id'))->orWhereNull('role_id');
            });
        }
        $template = $query->orderByDesc('is_default')->orderByDesc('role_id')->first();
        return response()->json($template ?? null);
    }

    /**
     * Render invoice with template (bind data dynamically).
     */
    public function render(Invoice $invoice, InvoiceTemplate $invoice_template): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        $html = $this->binding->bind($invoice, $invoice_template);
        return response()->json(['html' => $html]);
    }

    protected function saveVersion(InvoiceTemplate $template, string $comment, ?array $attrs = null): void
    {
        $t = $attrs ? (new InvoiceTemplate)->forceFill($attrs) : $template;
        InvoiceTemplateVersion::create([
            'invoice_template_id' => $template->id,
            'version' => (int) ($template->version ?? $t->version ?? 1),
            'snapshot' => [
                'header_html' => $t->header_html,
                'footer_html' => $t->footer_html,
                'body_html' => $t->body_html,
                'colors' => $t->colors,
            ],
            'comment' => $comment,
            'user_id' => auth()->id(),
        ]);
    }
}
