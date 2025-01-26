<?php

namespace Lwekuiper\StatamicMailchimp\Exceptions;

class FormConfigNotFoundException extends \Exception
{
    protected $formConfig;

    public function __construct($formConfig)
    {
        parent::__construct("Form Config [{$formConfig}] not found");

        $this->formConfig = $formConfig;
    }
}
