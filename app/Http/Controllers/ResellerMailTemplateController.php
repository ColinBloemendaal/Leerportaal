<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Mail\UpdateResellerMailTemplate;
use App\Contracts\Repositories\ResellerMailTemplateRepository;
use App\Enums\MailTemplateType;
use App\Http\Requests\Mail\UpdateResellerMailTemplateRequest;
use App\Http\Resources\ResellerMailTemplateResource;
use App\Services\Mail\MailTemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerMailTemplateController extends Controller
{
    public function index(ResellerMailTemplateRepository $templates): Response
    {
        $this->authorize('viewAny', 'App\Models\ResellerMailTemplate');

        $types = array_map(
            fn (MailTemplateType $type): array => [
                'type' => $type->value,
                'label' => $type->label(),
                'overridden' => $templates->findForCurrentReseller($type) !== null,
            ],
            MailTemplateType::cases(),
        );

        return Inertia::render('Settings/EmailTemplates/Index', ['types' => $types]);
    }

    public function edit(
        MailTemplateType $type,
        ResellerMailTemplateRepository $templates,
        MailTemplateRenderer $renderer,
    ): Response {
        $this->authorize('viewAny', 'App\Models\ResellerMailTemplate');

        $override = $templates->findForCurrentReseller($type);

        return Inertia::render('Settings/EmailTemplates/Edit', [
            'type' => $type->value,
            'label' => $type->label(),
            'placeholders' => $type->placeholders(),
            'defaultSubject' => $type->defaultSubject(),
            'override' => $override !== null ? new ResellerMailTemplateResource($override) : null,
            'preview' => $renderer->renderPreview($type, $override),
        ]);
    }

    public function update(
        MailTemplateType $type,
        UpdateResellerMailTemplateRequest $request,
        UpdateResellerMailTemplate $updateResellerMailTemplate,
    ): RedirectResponse {
        $updateResellerMailTemplate($request->toDto());

        return to_route('settings.email-templates.edit', $type)->with('success', __('Email template updated.'));
    }
}
