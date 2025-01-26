<?php

namespace Lwekuiper\StatamicMailchimp\Listeners;

use Lwekuiper\StatamicMailchimp\Services\Subscriber;
use Statamic\Events\SubmissionCreated;

class AddFromSubmission
{
    public function handle(SubmissionCreated $event)
    {
        Subscriber::fromSubmission($event->submission)?->subscribe();
    }
}
