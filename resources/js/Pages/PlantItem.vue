<template>
    <b-container fluid class="plant-items">
        <b-row>
            <b-col cols="12">
        <plant-item-detail
            :plant-item="plantItem"
            :form-field-options="formFieldOptions"
            :view="viewMode"
        />
            </b-col>
        </b-row>
    </b-container>
</template>

<script>

import PlantItemLayout from './PlantItemLayout'

export default {
name: "PlantItem",
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
            viewMode: 'expanded',
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
.plant-items {
    margin-left: 1em;
    margin-top: 1em;
    width: 95%;
}
</style>
