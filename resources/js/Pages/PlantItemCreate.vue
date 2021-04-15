<template>
    <b-container fluid class="plant-items">
        <b-row>
            <b-col cols="12">
                <plant-item-detail
                    :plant-item="plantItem"
                    :form-field-options="formFieldOptions"
                    :view="viewMode"
                    :edit="true"
                />
            </b-col>
        </b-row>
    </b-container>
</template>

<script>

import PlantItemLayout from './PlantItemLayout'

export default {
    name: "PlantItemCreate",
    props: {
        plantItem: {required: true},
        formFieldOptions: {required: true}
    },
    layout: PlantItemLayout,
    components: {

    },
    mounted() {
        this.$root.$on('viewModeChange', this.setViewMode);
    },
    data() {
        return {
            viewMode: 'brief',
        }
    },
    computed: {
    },
    methods: {
        setViewMode(value) {
            this.viewMode = value
        },
    },
}
</script>

<style scoped>

</style>
