<?php

namespace Lwekuiper\StatamicMailchimp\Stache;

use Lwekuiper\StatamicMailchimp\Data\FormConfig;
use Lwekuiper\StatamicMailchimp\Data\FormConfigCollection;
use Lwekuiper\StatamicMailchimp\Exceptions\FormConfigNotFoundException;
use Statamic\Stache\Stache;

class FormConfigRepository
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('mailchimp-pro');
    }

    public function make(): FormConfig
    {
        return new FormConfig;
    }

    public function all(): FormConfigCollection
    {
        $keys = $this->store->paths()->keys();

        return FormConfigCollection::make($this->store->getItems($keys));
    }

    public function find(string $form, string $site): ?FormConfig
    {
        return $this->store->getItem("$form::$site");
    }

    public function findOrFail(string $form, string $site): FormConfig
    {
        $formConfig = $this->find($form, $site);

        if (! $formConfig) {
            throw new FormConfigNotFoundException("$form::$site");
        }

        return $formConfig;
    }

    public function whereForm($handle): FormConfigCollection
    {
        $keys = $this->store
            ->index('handle')
            ->items()
            ->filter(fn ($value) => $value == $handle)
            ->keys();

        $items = $this->store->getItems($keys)->filter(fn ($item) => $item->site());

        return FormConfigCollection::make($items);
    }

    public function whereLocale($site): FormConfigCollection
    {
        $keys = $this->store
            ->index('locale')
            ->items()
            ->filter(fn ($value) => $value == $site)
            ->keys();

        return FormConfigCollection::make($this->store->getItems($keys));
    }

    public function save(FormConfig $formConfig): bool
    {
        $this->store->save($formConfig);

        return true;
    }

    public function delete(FormConfig $formConfig): bool
    {
        $this->store->delete($formConfig);

        return true;
    }
}
