<template>
    <panel-item :field="field">
        <template slot="value">
            <a
                class="inline-block font-bold cursor-pointer mr-2 mb-3 animate-text-color select-none"
                :class="{ 'text-60': localeKey !== currentLocale, 'text-primary border-b-2': localeKey === currentLocale }"
                :key="`a-${localeKey}`"
                v-for="(localeName, localeKey) in field.locales"
                @click="changeTab(localeKey)"
            >
                {{ localeName }}
            </a>

            <div
                v-for="(localeRows, localeKey) in allRows"
                v-if="localeKey === currentLocale"
            >
                <ul
                    class="list-reset mb-4"
                    v-for="(row, index) in localeRows"
                >
                    <li class="flex items-top mb-1" v-for="subRow in row">
                        <span class="block w-1/6">{{ subRow.label }}:</span>
                        <span class="block"><strong>{{ subRow.value }}</strong></span>
                    </li>
                </ul>
            </div>

        </template>
    </panel-item>
</template>

<script>

export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    data() {
        return {
            currentLocale: Object.keys(this.field.locales)[0]
        }
    },

    computed: {
        allRows() {
            let allRows = JSON.parse(this.field.value);

            for (const [locale, rows] of Object.entries(allRows)) {
                allRows[locale] = allRows[locale].map(row => {
                    let keys = Object.keys(row);

                    return keys.map(key => {
                        let subField = this.field.sub_fields.find(field => field.name === key);
                        let value = (['select'].some(type => type === subField.type))
                            ? subField.options[row[key]]
                            : row[key];
                        return {
                            label: subField.label,
                            value: value
                        }
                    });
                });
            }
            
            return allRows;
        }
    },

    methods: {
        changeTab(locale) {
            this.currentLocale = locale
        }
    }
}
</script>
