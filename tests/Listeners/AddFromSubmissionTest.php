<?php

namespace Lwekuiper\StatamicMailchimp\Tests\Listeners;

use ReflectionMethod;
use Statamic\Facades\Form;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Lwekuiper\StatamicMailchimp\Data;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SubmissionCreated;
use Lwekuiper\StatamicMailchimp\Tests\TestCase;
use Lwekuiper\StatamicMailchimp\Facades\Mailchimp;
use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Lwekuiper\StatamicMailchimp\Services\Subscriber;
use Lwekuiper\StatamicMailchimp\Listeners\AddFromSubmission;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddFromSubmissionTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_should_handle_submission_created_event()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $event = new SubmissionCreated($submission);

        $this->mock(AddFromSubmission::class)->shouldReceive('handle')->with($event)->once();

        Event::dispatch($event);
    }

    #[Test]
    public function it_returns_true_when_consent_field_is_not_configured()
    {
        $listener = new AddFromSubmission([]);

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_configured_consent_field_is_false()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $formConfig = ['consent_field' => 'consent'];
        $submissionData = ['consent' => false];

        $listener = new AddFromSubmission($submissionData, $formConfig);

        $hasConsent = $listener->hasConsent();

        $this->assertFalse($hasConsent);
    }

    #[Test]
    public function it_returns_true_when_configured_consent_field_is_true()
    {
        $formConfig = ['consent_field' => 'consent'];
        $submissionData = ['consent' => true];

        $listener = new AddFromSubmission($submissionData, $formConfig);

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_form_config_is_missing()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $listener = new AddFromSubmission($submission->data());

        $formConfig = $listener->getFormConfig($submission);

        $this->assertNull($formConfig);
    }

    #[Test]
    public function it_returns_true_when_form_config_is_present()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $listener = new AddFromSubmission($submission->data());

        $result = $listener->getFormConfig($submission);

        $this->assertInstanceOf(Data\FormConfig::class, $result);
    }

    #[Test]
    public function it_creates_a_subscriber_from_submission()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'custom_field' => 'Custom Value',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listId('5644ba3d09')->tagId('Tag 1');
        $formConfig->mergeFields([
            ['statamic_field' => 'first_name', 'mailchimp_field' => 'FNAME'],
            ['statamic_field' => 'last_name', 'mailchimp_field' => 'LNAME'],
            ['statamic_field' => 'custom_field', 'mailchimp_field' => 'CUSTOMFIELD'],
        ]);
        $formConfig->save();

        $subscriber = Subscriber::fromSubmission($submission);

        $this->assertNotNull($subscriber);
        $this->assertEquals('john@example.com', $subscriber->email());
    }

    #[Test]
    public function it_correctly_prepares_merge_data_for_sync_contact()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'custom_field' => 'Custom Value',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listId('5644ba3d09')->tagId('Tag 1');
        $formConfig->mergeFields([
            ['statamic_field' => 'first_name', 'mailchimp_field' => 'FNAME'],
            ['statamic_field' => 'last_name', 'mailchimp_field' => 'LNAME'],
            ['statamic_field' => 'custom_field', 'mailchimp_field' => 'CUSTOMFIELD'],
        ]);
        $formConfig->save();

        $subscriber = Subscriber::fromSubmission($submission);

        $reflectionMethod = new ReflectionMethod(Subscriber::class, 'getMergeData');
        $reflectionMethod->setAccessible(true);
        $mergeData = $reflectionMethod->invoke($subscriber);

        $this->assertEquals([
            "FNAME" => "John",
            "LNAME" => "Doe",
            "CUSTOMFIELD" => "Custom Value",
        ], $mergeData);
    }

}
