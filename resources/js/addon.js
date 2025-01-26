import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import MergeFieldsField from './components/fieldtypes/MailchimpProMergeFieldsFieldtype.vue';
import TagField from './components/fieldtypes/MailchimpProTagFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('mailchimp-listing', Listing);
    Statamic.$components.register('mailchimp-publish-form', PublishForm);
    Statamic.$components.register('mailchimp_pro_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('mailchimp_pro_tag-fieldtype', TagField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});
