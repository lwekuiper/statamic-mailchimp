<template>
    <div class="mailchimp-pro-merge-fields-fieldtype-wrapper">
        <small class="help-block text-grey-60" v-if="!list">{{ __('Select audience') }}</small>

        <v-select
            append-to-body
            v-if="showFieldtype && list"
            v-model="selected"
            :clearable="true"
            :options="fields"
            :reduce="(option) => option.id"
            :placeholder="__('Choose...')"
            :searchable="true"
            @input="$emit('input', $event)"
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fields: [],
            selected: null,
            showFieldtype: true,
        }
    },

    watch: {
        list(list) {
            this.showFieldtype = false;

            this.refreshFields();

            this.$nextTick(() => this.showFieldtype = true);
        }
    },

    computed: {

        formValues() {
            return this.$store.state.publish[this.storeName].values;
        },

        list() {
            return data_get(this.formValues, 'list_id.0');
        },

    },

    mounted() {
        this.selected = this.value;
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            this.$axios
                .get(cp_url(`/mailchimp-pro/merge-fields/${this.list}`))
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        }
    }
};
</script>
