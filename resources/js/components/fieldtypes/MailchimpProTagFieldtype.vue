<template>
    <div class="mailchimp-pro-tag-fieldtype-wrapper">
        <small class="help-block text-grey-60" v-if="!list">{{ __('Select audience') }}</small>

        <v-select
            append-to-body
            v-if="showFieldtype && list"
            v-model="selected"
            :clearable="true"
            :options="tags"
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
            tags: [],
            selected: null,
            showFieldtype: true,
        }
    },

    watch: {
        list(list) {
            this.showFieldtype = false;

            this.refreshTags();

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
        this.refreshTags();
    },

    methods: {
        refreshTags() {
            this.$axios
                .get(cp_url(`/mailchimp-pro/tags/${this.list}`))
                .then(response => {
                    this.tags = response.data ?? [];
                })
                .catch(() => { this.tags = []; });
        }
    }
};
</script>
