<template>
    <div class="statamic-form-fields-fieldtype-wrapper">
        <v-select
            append-to-body
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
        }
    },

    watch: {
        value: {
            immediate: true,
            handler(value) {
                this.selected = value;
            },
        },
    },

    computed: {
        form() {
            return StatamicConfig.urlPath.split('/')[1] ?? '';
        },
    },

    mounted() {
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            this.$axios
                .get(cp_url(`/mailchimp-pro/form-fields/${this.form}`))
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        },
    }
};
</script>
