<?php

namespace Lwekuiper\StatamicMailchimp;

use DrewM\MailChimp\MailChimp;
use Lwekuiper\StatamicMailchimp\Data\NewsletterAudienceCollection;
use Lwekuiper\StatamicMailchimp\Drivers\NewsletterDriver;
use Lwekuiper\StatamicMailchimp\Fieldtypes\MailchimpProAudience;
use Lwekuiper\StatamicMailchimp\Fieldtypes\MailchimpProMergeFields;
use Lwekuiper\StatamicMailchimp\Fieldtypes\MailchimpProTag;
use Lwekuiper\StatamicMailchimp\Fieldtypes\StatamicFormFields;
use Lwekuiper\StatamicMailchimp\Listeners\AddFromSubmission;
use Lwekuiper\StatamicMailchimp\Stache\FormConfigRepository;
use Lwekuiper\StatamicMailchimp\Stache\FormConfigStore;
use Statamic\Statamic;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Form;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        MailchimpProAudience::class,
        MailchimpProMergeFields::class,
        MailchimpProTag::class,
        StatamicFormFields::class,
    ];

    protected $listen = [
        SubmissionCreated::class => [AddFromSubmission::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
        'hotFile' => __DIR__.'/../resources/dist/hot',
    ];

    public function register()
    {
        $this->app->singleton(FormConfigRepository::class, function () {
            return new FormConfigRepository($this->app['stache']);
        });

        $this->app->singleton(MailChimp::class, function () {
            return new MailChimp(config('mailchimp.api_key'));
        });

        $this->publishes([
            __DIR__.'/../config/mailchimp-pro.php' => config_path('statamic/mailchimp-pro.php'),
        ], 'statamic-mailchimp-pro-config');
    }

    public function bootAddon()
    {
        Nav::extend(function ($nav) {
            $nav->create('Mailchimp Pro')
                ->section('Tools')
                ->route('mailchimp-pro.index')
                ->can('index', Form::class)
                ->icon('<svg width="800px" height="800px" viewBox="0 0 24 24" role="img" xmlns="http://www.w3.org/2000/svg"><title>MailChimp icon</title><path fill="currentColor" d="M17.98 11.341c.165-.021.332-.021.497 0 .089-.205.104-.558.024-.942-.12-.571-.28-.917-.613-.863-.333.054-.346.468-.226 1.039.067.321.186.596.319.766zM15.12 11.793c.239.105.385.174.442.114.037-.038.026-.11-.03-.203-.118-.193-.36-.388-.617-.497a1.677 1.677 0 0 0-1.634.196c-.16.117-.31.28-.29.378.008.032.031.056.087.064.132.015.591-.217 1.12-.25.374-.023.684.094.922.199zm-.48.274c-.31.05-.481.152-.591.247-.094.082-.152.173-.152.237l.024.057.051.02c.07 0 .228-.064.228-.064a1.975 1.975 0 0 1 1-.104c.155.018.23.028.263-.026.01-.015.023-.049-.008-.1-.073-.118-.387-.317-.814-.266zM17.015 13.073c.21.104.442.063.518-.09.076-.155-.034-.364-.245-.467-.21-.104-.442-.063-.518.09-.076.155.034.364.245.467zm1.355-1.186c-.171-.003-.314.185-.317.421-.004.235.131.428.302.431.171.003.314-.185.318-.42.003-.235-.132-.428-.303-.432zM6.866 16.13c-.042-.053-.112-.037-.18-.021a.646.646 0 0 1-.16.022.347.347 0 0 1-.292-.148c-.078-.12-.073-.299.012-.504l.04-.092c.138-.308.368-.825.11-1.317-.194-.37-.511-.602-.892-.65a1.145 1.145 0 0 0-.983.355c-.379.418-.438.988-.364 1.19.027.073.069.094.099.098.065.009.16-.038.22-.2l.017-.052c.026-.085.076-.243.157-.37a.688.688 0 0 1 .953-.2c.266.175.368.5.255.811-.059.161-.154.468-.133.72.043.512.357.717.638.74.274.01.466-.145.514-.258.03-.066.005-.107-.01-.125v.001zM22.691 15.194c-.01-.037-.078-.286-.172-.586l-.19-.51c.375-.563.381-1.066.332-1.35-.054-.353-.2-.654-.496-.964-.295-.312-.9-.63-1.75-.868l-.445-.124c-.002-.018-.023-1.053-.043-1.497-.013-.32-.041-.822-.196-1.315-.185-.669-.507-1.253-.91-1.627 1.11-1.152 1.803-2.422 1.801-3.511-.003-2.095-2.571-2.73-5.736-1.416l-.67.285a666.1 666.1 0 0 0-1.23-1.207C9.376-2.65-1.905 9.912 1.701 12.964l.789.668a3.885 3.885 0 0 0-.22 1.793c.085.84.517 1.644 1.218 2.266.665.59 1.54.965 2.389.964 1.403 3.24 4.61 5.228 8.37 5.34 4.034.12 7.42-1.776 8.84-5.182.093-.24.486-1.317.486-2.267 0-.956-.539-1.352-.882-1.352zm-16.503 2.55a1.94 1.94 0 0 1-.374.027c-1.218-.033-2.534-1.131-2.665-2.435-.145-1.44.59-2.548 1.89-2.81a2.22 2.22 0 0 1 .547-.04c.729.04 1.803.6 2.048 2.191.217 1.408-.128 2.843-1.446 3.068zm-1.36-6.08c-.81.157-1.524.617-1.96 1.252-.261-.218-.747-.64-.833-.804-.697-1.325.76-3.902 1.778-5.357C6.33 3.159 10.268.437 12.093.931c.296.084 1.278 1.224 1.278 1.224s-1.823 1.013-3.514 2.426c-2.278 1.757-3.999 4.311-5.03 7.083zm12.787 5.542a.072.072 0 0 0 .042-.071.067.067 0 0 0-.074-.06s-1.908.283-3.711-.379c.196-.639.718-.408 1.508-.344a11.01 11.01 0 0 0 3.64-.394c.816-.235 1.888-.698 2.722-1.356.28.618.38 1.298.38 1.298s.217-.039.399.073c.171.106.297.326.211.895-.175 1.063-.626 1.926-1.384 2.72a5.698 5.698 0 0 1-1.663 1.244 7.018 7.018 0 0 1-1.085.46c-2.858.935-5.784-.093-6.727-2.3a3.582 3.582 0 0 1-.19-.522c-.401-1.455-.06-3.2 1.007-4.299.065-.07.132-.153.132-.256 0-.087-.055-.178-.102-.243-.373-.542-1.666-1.466-1.406-3.254.186-1.285 1.308-2.189 2.353-2.135l.265.015c.453.027.848.085 1.222.101.624.027 1.185-.064 1.85-.619.224-.187.404-.35.708-.401.032-.005.111-.034.27-.026a.892.892 0 0 1 .456.146c.533.355.608 1.215.636 1.845.016.36.059 1.228.074 1.478.034.57.183.65.486.75.17.057.329.099.562.164.705.199 1.123.4 1.387.659.158.161.23.333.253.497.084.608-.47 1.359-1.938 2.041-1.605.746-3.55.935-4.895.785l-.471-.053c-1.076-.145-1.689 1.247-1.044 2.201.416.615 1.55 1.015 2.683 1.015 2.6 0 4.598-1.111 5.341-2.072l.06-.085c.036-.055.006-.085-.04-.054-.607.416-3.304 2.069-6.19 1.571 0 0-.35-.057-.67-.182-.254-.099-.786-.344-.85-.891 2.328.721 3.793.039 3.793.039zm-3.688-.436l.001.001v-.002zM9.473 6.74c.895-1.036 1.996-1.936 2.982-2.441.034-.017.07.02.052.053-.079.142-.23.447-.277.677a.04.04 0 0 0 .061.042c.614-.419 1.681-.868 2.618-.925.04-.003.06.049.027.074-.154.119-.293.258-.411.413a.04.04 0 0 0 .031.064c.657.005 1.584.235 2.188.575.04.023.012.102-.034.092-.914-.21-2.41-.37-3.964.01-1.387.339-2.446.862-3.218 1.425-.04.029-.086-.023-.055-.06z"/></svg>')
                ->children(function () {
                    return Form::all()->sortBy->title()->map(function ($form) {
                        return Nav::item($form->title())
                            ->url(cp_route('mailchimp-pro.edit', $form->handle()))
                            ->can('edit', $form);
                    });
                });
        });

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'statamic-mailchimp-pro-config',
            ]);
        });

        $formConfigStore = new FormConfigStore();
        $formConfigStore->directory(base_path('resources/mailchimp'));
        app(Stache::class)->registerStore($formConfigStore);
    }
}
