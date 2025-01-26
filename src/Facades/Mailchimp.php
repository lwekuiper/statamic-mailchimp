<?php

namespace Lwekuiper\StatamicMailchimp\Facades;

use Illuminate\Support\Facades\Facade;

class Mailchimp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DrewM\MailChimp\MailChimp::class;
    }
}
