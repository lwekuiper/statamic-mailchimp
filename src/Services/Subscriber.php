<?php

namespace Lwekuiper\StatamicMailchimp\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Lwekuiper\StatamicMailchimp\Facades\Mailchimp;
use Statamic\Facades\Site;
use Statamic\Forms\Submission;
use Statamic\Support\Arr;

class Subscriber
{
    private Collection $data;

    private Collection $config;

    public static function fromSubmission(Submission $submission): ?self
    {
        if (! $form = $submission->form()) {
            return null;
        }

        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        if (! $formConfig = FormConfig::find($form->handle(), $site->handle())) {
            return null;
        }

        return new self($submission->data(), $formConfig->fileData());
    }

    public function __construct($data, ?array $config = null)
    {
        $this->data = collect($data);
        $this->config = collect($config);
    }

    public function email(): string
    {
        return $this->data->get($this->config->get('email_field', 'email'));
    }

    public function hasConsent(): bool
    {
        if (! $field = $this->config->get('consent_field')) {
            return true;
        }

        return filter_var(
            Arr::get(Arr::wrap($this->data->get($field, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function subscribe(): void
    {
        if ($this->config->isEmpty()) {
            return;
        }

        if (! $this->hasConsent()) {
            return;
        }

        $list_id = $this->config->get('list_id');
        $subscriber_hash = Mailchimp::subscriberHash($this->email());

        Mailchimp::put("lists/$list_id/members/$subscriber_hash", $this->getOptions());

        if (! Mailchimp::success()) {
            Log::error(Mailchimp::getLastError());
            Log::error(Mailchimp::getLastResponse());
        }
    }

    protected function getOptions(): array
    {
        $options = [
            'email_address' => $this->email(),
            'status_if_new' => 'subscribed',
            'email_type' => 'html',
            'tags' => Arr::wrap($this->config->get('tag_id')),
        ];

        if (count($mergeData = $this->getMergeData())) {
            $options['merge_fields'] = $mergeData;
        }

        return $options;
    }

    private function getMergeData(): array
    {
        return collect($this->config->get('merge_fields', []))
            ->map(function ($item) {
                if (is_null($fieldData = $this->data->get($item['statamic_field']))) {
                    return [];
                }

                return [
                    $item['mailchimp_field'] => is_array($fieldData) ? implode('|', $fieldData) : $fieldData,
                ];
            })
            ->collapse()
            ->all();
    }
}
