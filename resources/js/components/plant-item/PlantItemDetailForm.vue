<template>
    <b-form ref="form">
        <!-- Note that the order of fields in the table below is determined -->
        <!-- by the fields property and not the order that the form templates-->
        <!-- are specified below.-->
        <b-table small stacked caption-top caption="Fields with highlighting are required" :items="tableData" :fields="fields">

            <template v-slot:head(description)="data">
                <span class="required">data.label</span>
            </template>


            <!-- Text Area Fields-->
            <template v-slot:cell(description)="row">
                <b-textarea required rows="2"
                            :disabled="saving"
                            v-model="formData.description" />
            </template>

            <!-- Input Fields-->
<!--            <template v-slot:cell(plantParentId)="row">-->
<!--                <b-input required-->
<!--                         :disabled="saving"-->
<!--                         v-model="formData.plantParentId" />-->
<!--            </template>-->
            <template v-slot:cell(plantId)="row">
                <b-input required
                         :disabled="saving || isUpdating"
                         v-model="formData.plantId" />
            </template>
            <template v-slot:cell(location)="row">
                <b-input :disabled="saving" v-model="formData.location"></b-input>
            </template>
            <template v-slot:cell(plantType)="row">
                <b-input :disabled="saving" v-model="formData.plantType"></b-input>
            </template>
            <template v-slot:cell(assetManagementId)="row">
                <b-input :disabled="saving" v-model="formData.assetManagementId"></b-input>
            </template>
            <template v-slot:cell(barcodeId)="row">
                <b-input :disabled="saving" v-model="formData.barcodeId"></b-input>
            </template>
            <template v-slot:cell(code)="row">
                <b-input :disabled="saving" v-model="formData.code"></b-input>
            </template>
            <template v-slot:cell(drawingReference)="row">
                <b-input :disabled="saving" v-model="formData.drawingReference"></b-input>
            </template>
            <template v-slot:cell(defaultIsolationCondition)="row">
                <b-input :disabled="saving" v-model="formData.defaultIsolationCondition"></b-input>
            </template>

            <!-- Non-Boolean Select Fields-->
            <template v-slot:cell(plantGroup)="row">
                <b-select :disabled="saving" v-model="formData.plantGroup"
                          required
                          :options="formFieldOptions.plantGroupOptions"/>
            </template>
            <template v-slot:cell(methodOfProving)="row">
                <b-select :disabled="saving" v-model="formData.methodOfProving"
                          :options="formFieldOptions.methodOfProvingOptions"/>
            </template>
            <template v-slot:cell(circuitVoltage)="row">
                <b-select :disabled="saving" v-model="formData.circuitVoltage"
                          :options="formFieldOptions.circuitVoltageOptions"/>
            </template>


            <!-- Boolean Select Fields-->
            <template v-slot:cell(isIsolationPoint)="row">
                <b-select :disabled="saving" v-model="formData.isIsolationPoint" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isPlantItem)="row">
                <b-select :disabled="saving" v-model="formData.isPlantItem" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isConfinedSpace)="row">
                <b-select :disabled="saving" v-model="formData.isConfinedSpace" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isSafetySystem)="row">
                <b-select :disabled="saving" v-model="formData.isSafetySystem" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isLimitedAuthority)="row">
                <b-select :disabled="saving" v-model="formData.isLimitedAuthority" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isPassingValve)="row">
                <b-select :disabled="saving" v-model="formData.isPassingValve" :options="booleanOptions"></b-select>
            </template>
            <template v-slot:cell(isTemporaryItem)="row">
                <b-select :disabled="saving" v-model="formData.isTemporaryItem" :options="booleanOptions"></b-select>
            </template>

            <!-- Custom Fields-->
            <template v-slot:cell(isolationPoints)="row">
                <isolation-point-select
                    v-model="formData.isolationPoints"
                    :disabled="saving"
                    form-label="" />
            </template>

            <template v-slot:cell(plantParentId)="row">
                <plant-item-select
                    v-model="formData.plantParentId"
                    :disabled="saving"
                    form-label="" />
            </template>

            <!-- Other (Read Only) Fields-->
            <template v-slot:cell(dataSource)="row">
                <inertia-link
                    :href="route('plant_items.table',
                        {'data_source':row.item.dataSource}
                    )">
                    {{ row.item.dataSource }}
                </inertia-link>
            </template>
            <!--            <template v-slot:cell(isolationPoints)="row">-->
            <!--                <p class="isolation-point" v-for="point in row.item.isolationPoints">-->
            <!--                    {{point.description}}-->
            <!--                </p>-->
            <!--            </template>-->
        </b-table>

        <b-alert :show="hasErrors" variant="danger"
                 v-for="(message, index) in errorMessages"
                 :key="index">
            {{ message }}
        </b-alert>

        <b-alert :show="hasSaveStatus" :variant="saveStatus">{{ saveMessage }}</b-alert>

        <b-form-row>
            <b-col cols="8">

            </b-col>
            <b-col>
                <b-button :disabled="saving" variant="danger" @click="resetForm()" size="sm" title="Reset">
                    Reset Form
                </b-button>
            </b-col>
            <b-col>
                <b-button :disabled="saving" variant="primary" @click="submitForm()" size="sm" title="Save">
                    <b-spinner v-if="saving" label="Spinning"></b-spinner>
                    Save Form
                </b-button>
            </b-col>

        </b-form-row>

    </b-form>
</template>


<script>

import IsolationPointSelect from "./IsolationPointSelect";
import PlantItemSelect from "./PlantItemSelect";

export default {
    name: "PlantItemDetailForm",
    components: {IsolationPointSelect, PlantItemSelect},
    props: {
        fields: {required: true},             // Which fields will be actively shown
        formFieldOptions: {required: true},   // Options from server for various drop-downs
        value: {type: Object, required: true} // A PlantItem object
    },
    created() {
        //Create a local editable copy of the received PlantItem that
        //we can update via form fields.
        this.formData = Object.assign({}, this.value)
    },
    watch: {
        // Watch the value prop. We will load it into the local editable
        // copy in lieu of the existing item.  This can allow the user
        // to keep the form open and load/save, load/save repeatedly with
        // fewer clicks.
        value() {
            this.resetForm()
        },
    },
    data() {
        return {
            saving: false,      // Is the form actively being saved/submitted
            saveStatus: '',     // Empty string or a bootstrap varian name "success", "danger", etc.
            saveMessage: '',    // A brief message to display indicating success/failure of save
            errors: {},         // Errors object returned from server.
            formData: {},       // Locally editable PlantItem fields
            booleanOptions: [   // For YES/NO/NULL drop-downs
                {value: '', text: ''},
                {value: 1, text: 'YES'},
                {value: 0, text: 'NO'}
            ],

        }
    },
    computed: {

        tableData: {  // b-table requires an array be passed in
            get() {
                return [this.value] // We only have one row that we will present vertically
            },
        },
        isCreating(){
          return ! this.formData.id // Lack of an id means creating a new item
        },
        isUpdating(){
            return ! this.isCreating
        },
        hasSaveStatus() {
            return !_.isEmpty(this.saveStatus)
        },
        hasErrors() {
            return !_.isEmpty(this.errors)
        },
        errorMessages() {
            //unpack the nested laravel MessageBag into a flat array of strings
            return _.flatten(_.values(this.errors)
                .map((currentValue, index) => currentValue))
        }
    },

    methods: {
        resetForm() {
            this.formData = Object.assign({}, this.value)
            this.initStatus()
        },
        saveUrl(){      // Returns appropriate URL for update or create
          if (this.formData.id) {
              return this.route('api.plant_items.update',
                  {id: this.formData.id})
          } else{
              return this.route('api.plant_items.store')
          }
        },
        submitForm(){
            if (this.$refs.form.checkValidity()) {
                this.saveForm()
            }else{
                this.$refs.form.reportValidity();
            }
        },

        saveForm() {    // Post formData to server

            this.prepareForSave()
            let method = this.formData.id ? 'put' : 'post'
            this.$http[method](this.saveUrl(), this.formData)
                .then((result) => {      // success
                    this.concludeSave('success', 'Saved')
                    this.$bvToast.toast('Plant Item Saved',{title:'Success', variant:'success'})
                    this.formData = result.data
                    this.$emit('input',this.formData)
                    let eventName = (method === 'put') ? 'plantItemUpdate' : 'plantItemCreate'
                    this.$root.$emit(eventName, this.formData)
                }, (error) => {
                    if (error.response.data.message) {
                        this.concludeSave('danger', error.response.data.message)
                    } else {
                        this.concludeSave('danger', 'Save failed with an unspecified error')
                    }
                    if (error.response.data.errors) {
                        this.errors = error.response.data.errors
                    }
                }).catch((exception) => {
                this.concludeSave('danger', 'Failed to save. See console log for exception data.')
                console.log(exception)
            })
        },
        prepareForSave() {       // set status and error fields before attempting save
            this.initStatus()
            this.saving = true
        },
        concludeSave(status, message) {  // set status and error fields after save finishes.
            this.saving = false;
            this.saveMessage = message
            this.saveStatus = status
        },
        initStatus() {                   // initialize status and error fields.
            this.errors = {}
            this.saveStatus = ''
            this.saveMessage = ''
        }
    }
}
</script>

<style scoped>
/* style all elements with a required attribute */
:required {
    border: #b0d4f1 2px solid;
}
</style>
