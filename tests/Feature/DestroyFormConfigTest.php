<?php

namespace Lwekuiper\StatamicMailchimp\Tests\Feature;

use Lwekuiper\StatamicMailchimp\Facades\FormConfig;
use Lwekuiper\StatamicMailchimp\Tests\FakesRoles;
use Lwekuiper\StatamicMailchimp\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class DestroyFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_deletes_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listId(1)->consentField('consent')->tagId(1);
        $formConfig->save();

        $this->assertCount(1, FormConfig::all());

        $this->actingAs($user)
            ->delete($formConfig->deleteUrl())
            ->assertNoContent();

        $this->assertCount(0, FormConfig::all());
    }
}
