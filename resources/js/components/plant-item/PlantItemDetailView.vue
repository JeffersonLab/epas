<template>
    <b-table small stacked :items="tableData" :fields="fields">
        <!-- Note that the order of fields in the table is determined -->
        <!-- by the fields property and not the order that the form templates-->
        <!-- are specified below.-->

        <!-- Boolean Display Fields-->
        <template v-slot:cell(isIsolationPoint)="row">
            {{formatBoolean(row.item.isIsolationPoint)}}
        </template>
        <template v-slot:cell(isPlantItem)="row">
            {{formatBoolean(row.item.isPlantItem)}}
        </template>
        <template v-slot:cell(isConfinedSpace)="row">
            {{formatBoolean(row.item.isConfinedSpace)}}
        </template>
        <template v-slot:cell(isSafetySystem)="row">
            {{formatBoolean(row.item.isSafetySystem)}}
        </template>
        <template v-slot:cell(isLimitedAuthority)="row">
            {{formatBoolean(row.item.isLimitedAuthority)}}
        </template>
        <template v-slot:cell(isPassingValve)="row">
            {{formatBoolean(row.item.isPassingValve)}}
        </template>
        <template v-slot:cell(isTemporaryItem)="row">
            {{formatBoolean(row.item.isTemporaryItem)}}
        </template>

        <!-- Display data source as link to table view-->
        <template v-slot:cell(dataSource)="row">
            <a target="_blank"
                :href="route('plant_items.table',{'data_source':row.item.dataSource})">
                {{row.item.dataSource}}
            </a>
        </template>

        <!-- Isolation points may be multiple-->
        <template v-slot:cell(isolationPoints)="row">
            <ul>
                <li class="isolation-point" v-for="point in row.item.isolationPoints">
                    <a target="_blank" :href="route('plant_items.item',[point.id])">
                        {{point.plantId}}
                    </a>
                </li>
            </ul>
        </template>


    </b-table>
</template>

<script>
export default {
    name: "PlantItemDetailView",
    props:{
        fields: {type: Array, required: true},    // Which fields will be actively shown
        plantItem: {type: Object, required: true} // A PlantItem object
    },
    computed: {
        tableData: {  // b-table requires an array be passed in
            get() {
                return [this.plantItem] // We only have one row that we will present vertically
            },
        },
    },
    methods:{
        formatBoolean(value) {
            if (value === undefined || value === null) {  // Null values are not NO
                return ''
            }
            if (typeof value === "boolean"){
                return value === true ? 'YES' : 'NO'
            }
            return String(value) === '1' ? 'YES' : 'NO' // Numeric or string "1" & "0" are acceptable
        },
    }
}
</script>

<style scoped>

</style>
