<?php

namespace Lwekuiper\StatamicMailchimp\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicMailchimp\Stache\FormConfigRepository;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormConfigRepository::class;
    }
}
