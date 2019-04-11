<template>
    <default-field :field="field">
        <template slot="field">
            <a
                class="inline-block font-bold cursor-pointer mr-2 mb-3 animate-text-color select-none"
                :class="{ 'text-60': localeKey !== currentLocale, 'text-primary border-b-2': localeKey === currentLocale }"
                :key="`a-${localeKey}`"
                v-for="(localeName, localeKey) in field.locales"
                @click="changeTab(localeKey)"
            >
                {{ localeName }}
            </a>

            <div v-model="allRows">
                <draggable
                    v-for="(localeRows, localeKey) in allRows"
                    v-model="allRows[localeKey]"
                    v-if="localeKey === currentLocale"
                    :key="localeKey"
                    handle=".js-row-move"
                >
                    <sub-field-row
                        v-for="(row, index) in localeRows"
                        v-model="localeRows[index]"
                        :key="index"
                        :index="index"
                        :field="field"
                        @delete-row="deleteRow(index, currentLocale)"
                    ></sub-field-row>
                </draggable>
            </div>

            <button
                class="btn btn-default btn-primary"
                @click.prevent="addNewRow(currentLocale)"
                v-text="addButtonText"
                :class="{ 'cursor-not-allowed opacity-50': hasReachedMaximumRows(currentLocale) }"
            ></button>

            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
        </template>
    </default-field>
</template>

<script>

import draggable from 'vuedraggable'
import SubFieldRow from './rows/SubFieldRow.vue';
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { EventBus } from '../event-bus';

export default {
    mixins: [FormField, HandlesValidationErrors],

    components: {
        draggable,
        SubFieldRow
    },

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            locales: Object.keys(this.field.locales),
            currentLocale: null,
			value: '',
            allRows: {}
        }
    },

    mounted() {
        this.currentLocale = this.locales[0] || null;

        EventBus.$on('localeChanged', locale => {
            if (this.currentLocale !== locale) {
                this.changeTab(locale, true);
            }
        });
    },

    computed: {
        addButtonText() {
            return (this.field.add_button_text)
                ? this.field.add_button_text
                : 'Add row'
        }
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || '';

            this.$nextTick(() => {
                this.allRows = (this.value)
                    ? JSON.parse(this.value)
                    : {};

                for (const [locale, rows] of Object.entries(this.allRows)) {
                    if (this.shouldAddInitialRows(locale)) {
                        let count = this.field.initial_rows - rows.length;
                        for (let i = 1; i <= count; i++) {
                            this.addNewRow(locale);
                        }
                    }
                }
             });
         },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '');
        },

        hasReachedMaximumRows(locale) {
            if (this.field.maximum_rows) {
                return this.allRows[locale].length >= this.field.maximum_rows;
            }

            return false;

        },

        shouldAddInitialRows(locale) {
            return (this.field.initial_rows) && (this.field.initial_rows > this.allRows[locale].length);
        },

        addNewRow(locale) {
            if (! this.hasReachedMaximumRows(locale)) {
                for (const rows of Object.values(this.allRows)) {
                    let newRow = this.field.sub_fields
                        .map(subField => subField.name)
                        .reduce((o, key) => ({...o, [key]: null}), {});

                    rows.push(newRow);
                }
            }
        },

        deleteRow(index, locale) {
            for (const rows of Object.values(this.allRows)) {
                rows.splice(index, 1);
            }
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value[this.currentLocale] = value
        },

        changeTab(locale, dontEmit) {
            if (this.currentLocale !== locale){
                if (!dontEmit){
                    EventBus.$emit('localeChanged', locale);
                }

                this.currentLocale = locale;
            }
        },

        handleTab(e) {
            const currentIndex = this.locales.indexOf(this.currentLocale)
            if (!e.shiftKey) {
                if (currentIndex < this.locales.length - 1) {
                    e.preventDefault();
                    this.changeTab(this.locales[currentIndex + 1]);
                }
            } else {
                if (currentIndex > 0) {
                    e.preventDefault();
                    this.changeTab(this.locales[currentIndex - 1]);
                }
            }
        }
    },

    watch: {
        'allRows': {
            handler: function (newRows) {
                this.value = JSON.stringify(newRows);
            },
            deep: true
        }
    }
}
</script>
