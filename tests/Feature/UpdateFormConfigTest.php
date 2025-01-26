<?php

namespace Lwekuiper\StatamicMailchimp\Tests\Feature;

use Statamic\Facades\Form;
use Statamic\Facades\User;
use PHPUnit\Framework\Attributes\Test;
use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Lwekuiper\StatamicMailchimp\Tests\FakesRoles;
use Lwekuiper\StatamicMailchimp\Tests\TestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class UpdateFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_updates_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test')->makeSuper())->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listId(1)->consentField('consent')->tagId(1);
        $formConfig->save();

        $this
            ->from('/here')
            ->actingAs($user)
            ->patchJson($formConfig->updateUrl(), [
                'email_field' => 'email',
                'list_id' => [2],
                'consent_field' => 'consent',
                'tag_id' => [2]
            ])
            ->assertSuccessful();

        $this->assertCount(1, FormConfig::all());
        $formConfig = FormConfig::find('test_form', 'default');
        $this->assertEquals(2, $formConfig->listId());
    }
}
