<?php

namespace Lwekuiper\StatamicMailchimp\Fieldtypes;

use DrewM\MailChimp\MailChimp;
use Statamic\Fieldtypes\Relationship;
use Statamic\Support\Arr;

class MailchimpProAudience extends Relationship
{
    private ?MailChimp $mailchimp = null;

    public function __construct()
    {
        if (config('mailchimp.api_key')) {
            $this->mailchimp = app(MailChimp::class);
        }
    }

    public function getIndexItems($request)
    {
        return collect(Arr::get($this->callApi('lists', ['count' => 100]), 'lists', []))
            ->map(fn ($list) => ['id' => $list['id'], 'title' => $list['name']]);
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        if (! $list = $this->callApi("lists/{$id}")) {
            return [];
        }

        if (! $id = Arr::get($list, 'id')) {
            return [];
        }

        return [
            'id' => $id,
            'title' => Arr::get($list, 'name'),
        ];
    }

    protected function callApi(string $endpoint, array $data = []): ?array
    {
        return optional($this->mailchimp)->get($endpoint, $data);
    }
}
