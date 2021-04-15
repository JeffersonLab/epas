<template>
    <b-container fluid class="plant-items">
        <b-card title="Table Data Filters" class="plant-item-filters">
            <b-form>
                <b-form-row>
                    <b-col>
                        <b-form-group label-cols-lg="3" label="Data Source"
                                      description="Limit display to data source">
                            <b-select v-model="filterForm.dataSource" :options="filters.dataSourceOptions"></b-select>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group label-cols-lg="3" label="Search Text"
                                      description="Search for text in applicable fields">
                            <b-input v-model="filterForm.search"></b-input>
                        </b-form-group>
                    </b-col>
                </b-form-row>
                <div class="buttons">
                    <b-button type="submit" variant="primary"
                              @click.prevent.stop="applyFilters"
                              title="Apply filters and update table below" >
                        Apply
                    </b-button>
                    <b-button type="submit" variant="primary"
                            @click.prevent.stop="downloadExcel"
                            title="Apply filters and download results as Excel file">
                        Download
                    </b-button>
                </div>
            </b-form>
        </b-card>
        <v-client-table class="plant-items-table"
            :data="plantItems"
            :columns="columns"
            :options="options">

            <template v-slot:isPlantItem="props">
                {{ formatBoolean(props.row.isPlantItem) }}
            </template>

            <template v-slot:isIsolationPoint="props">
                {{ formatBoolean(props.row.isIsolationPoint) }}
            </template>

            <template v-slot:plantId="props">
                <inertia-link :href="route('plant_items.item',[props.row.id])">
                    {{props.row.plantId}}
                </inertia-link>
            </template>
        </v-client-table>
    </b-container>
</template>

<script>

import Vue from "vue";
import {ClientTable} from 'vue-tables-2';

Vue.use(ClientTable, {}, false, 'bootstrap4');
import PlantItemLayout from './PlantItemLayout'

export default {
    name: "PlantItemTable",
    props: {
        request: {required: true},
        filters: {required: true},
        plantItems: {required: true, type: Array},
    },
    mounted() {
        this.filterForm.dataSource = this.request.dataSource ? this.request.dataSource : null
        this.filterForm.search = this.request.search ? this.request.search : null
    },
    layout: PlantItemLayout,
    data: function () {
        return {

            filterForm: this.$inertia.form({
                dataSource: null,
                search: '',
            }),

            // Defines the complete list of columns for the table
            columns: ['plantParentId', 'plantId', 'description', 'location',
                'plantGroup', 'isPlantItem', 'isIsolationPoint', 'dataSource'],

            // Table config options
            //see node_modules/vue-tables-2/compiled/config/defaults.js for complete list
            // or maybe https://matanya.gitbook.io/vue-tables-2/options-api
            options: {
                perPage: 50,
                texts: {
                    filterPlaceholder: 'Find in table..'
                },
                sortable: ['plantId', 'plantParentId', 'description', 'location', 'plantGroup', 'dataSource'],
                sortIcon: {
                    base: 'fa',
                    up: 'fa-sort-up',
                    down: 'fa-sort-down',
                    is: 'fa-sort'
                },
                filterable: ['plantId', 'plantParent_id', 'description', 'location', 'plantGroup', 'dataSource'],
                columnsDropdown: true,
                columnsDisplay: {
                    id: 'not_mobile',
                },
            }, // options


        }
    },

    methods: {
        formatBoolean(value) {
            if (value === undefined || value === null) {
                return ''
            }
            return value == 1 ? 'YES' : 'NO'
        },
        applyFilters() {
            this.filterForm.get(this.route('plant_items.table'));
        },
        downloadExcel() {
            window.location.assign(this.route('plant_items.excel',this.filterForm.data()));
        },
    }
}
</script>

<style scoped>
.plant-item-filters {
    margin-top: 1em;
    border: 1px solid;
}

.plant-item-filters fieldset {
    border: none;
}

.plant-items {
    width: 90%;
}

.plant-items-table {
    margin-top: 15px;
    width: 100%;
}
</style>
