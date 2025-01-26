<?php

namespace Lwekuiper\StatamicMailchimp\Http\Controllers;

use Illuminate\Http\Request;
use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as BlueprintContract;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\CP\CpController;

class FormConfigController extends CpController
{
    public function index(Request $request)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        $site = $this->getSite($request);

        $forms = FormFacade::all()
            ->mapWithKeys(fn ($form) => [
                $form->handle() => [
                    'title' => $form->title(),
                    'edit_url' => cp_route('mailchimp-pro.edit', ['form' => $form->handle(), 'site' => $site]),
                ]
            ]);

        $formConfigs = FormConfig::whereLocale($site)
            ->mapWithKeys(fn ($formConfig) => [
                $formConfig->handle() => [
                    'list_id' => $formConfig->listId(),
                    'tag_id' => $formConfig->tagId(),
                    'delete_url' => $formConfig->deleteUrl(),
                ]
            ]);

        $viewData = [
            'formConfigs' => $forms->mergeRecursive($formConfigs)->values(),
            'locale' => $site,
            'localizations' => Site::all()->map(fn ($localization) => [
                'handle' => $localization->handle(),
                'name' => $localization->name(),
                'active' => $localization->handle() === $site,
                'url' => cp_route('mailchimp-pro.index', ['site' => $localization->handle()]),
            ])->values()->all(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-mailchimp::index', $viewData);
    }

    public function edit(Request $request, Form $form)
    {
        $site = $this->getSite($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields();

        if ($formConfig = FormConfig::find($form->handle(), $site)) {
            $fields = $fields->addValues($formConfig->fileData());
        }

        $fields = $fields->preProcess();

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('mailchimp-pro.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route('mailchimp-pro.index', ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'locale' => $site,
            'localizations' => Site::all()->map(fn ($localization) => [
                'handle' => $localization->handle(),
                'name' => $localization->name(),
                'active' => $localization->handle() === $site,
                'url' => cp_route('mailchimp-pro.edit', ['form' => $form->handle(), 'site' => $localization->handle()]),
            ])->values()->all(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-mailchimp::edit', $viewData);
    }

    public function update(Request $request, Form $form)
    {
        $site = $this->getSite($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values()->all();

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig = $formConfig
            ->emailField($values['email_field'])
            ->consentField($values['consent_field'])
            ->listId($values['list_id'])
            ->tagId($values['tag_id'])
            ->mergeFields($values['merge_fields']);

        $formConfig->save();

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        $site = $this->getSite($request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        $formConfig->delete();

        return response('', 204);
    }

    /**
     * Get the site based on the request.
     */
    private function getSite(Request $request): string
    {
        return $request->site ?? Site::selected()->handle();
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
    {
        return Blueprint::find('statamic-mailchimp::config');
    }
}
