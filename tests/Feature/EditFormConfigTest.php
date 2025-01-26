<?php

namespace Lwekuiper\StatamicMailchimp\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Lwekuiper\StatamicMailchimp\Tests\FakesRoles;
use Lwekuiper\StatamicMailchimp\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EditFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_edit_form_config_page()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listId(1)->consentField('consent')->tagId(1);
        $formConfig->save();

        Http::fake(); // Fake any HTTP requests to the Mailchimp API.

        $this->actingAs($user)
            ->get($formConfig->editUrl())
            ->assertOk()
            ->assertViewHas('values', collect([
                'email_field' => 'email',
                'list_id' => [1],
                'consent_field' => 'consent',
                'tag_id' => 1,
                'merge_fields' => [],
            ]));
    }
}
