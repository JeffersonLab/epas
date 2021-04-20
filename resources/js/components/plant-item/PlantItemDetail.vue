<template>
    <div class="plant-item-detail">
        <!-- Menubar with controls for toggling display mode and editing    -->
        <b-navbar size="sm" class="menu-bar" toggleable="lg" type="dark" variant="light">
            <b-navbar-brand>{{ brandLabel }}</b-navbar-brand>
            <b-navbar-nav class="ml-auto">
                <b-nav-item>
                    <b-button v-if="isEditable"
                              size="sm"
                              title="Edit Plant Item"
                              @click="enableEdit()">
                        <b-icon-pencil-square/>
                    </b-button>
                </b-nav-item>
                <b-nav-form>
                    <b-nav-item>
                        <b-button v-if="editMode"
                                  size="sm" variant="danger"
                                  title="Close form and discard any unsaved edits"
                                  @click="cancelEdit()">
                            <b-icon-x-square/>
                        </b-button>
                    </b-nav-item>
                </b-nav-form>
                <b-nav-form>
                    <b-button v-if="isFertile"
                              size="sm"
                              :title="`Add a new child plant item beneath ${brandLabel}`"
                              @click="createChild()">
                        <b-icon-plus-square/>
                    </b-button>
                </b-nav-form>
                <b-nav-form>
                    <b-nav-item>
                        <b-button v-if="isDeletable"
                                  size="sm" title="Delete Plant Item"
                                  @click="confirmDelete()">
                            <b-icon-trash/>
                        </b-button>
                    </b-nav-item>
                </b-nav-form>
                <b-nav>
                    <b-nav-item-dropdown right>
                        <template #button-content>
                            <b-button size="sm" title="Configure detail view">
                                <b-icon-gear/>
                            </b-button>
                        </template>
                        <b-dropdown-form>
                            <b-form-radio-group
                                size="sm"
                                v-model="displayMode"
                                :options="displayModeOptions"
                                name="display-mode"
                                buttons
                            ></b-form-radio-group>
                        </b-dropdown-form>
                    </b-nav-item-dropdown>
                </b-nav>
            </b-navbar-nav>
        </b-navbar>

        <!-- Container displays either the detail or form view    -->
        <b-container v-if="plantItem" fluid class="plant-item-detail">
            <plant-item-detail-form v-if="editMode"
                                    :fields="fields"
                                    v-model="formData"
                                    :form-field-options="formFieldOptions"/>

            <plant-item-detail-view v-if="!editMode"
                                    :fields="fields"
                                    :plant-item="formData"/>
        </b-container>
    </div>
</template>

<script>
import PlantItemDetailForm from "./PlantItemDetailForm";
import PlantItemDetailView from "./PlantItemDetailView";

export default {
    name: "PlantItemDetail",
    components: {PlantItemDetailForm, PlantItemDetailView},
    props: {
        edit: {required: false, default: false},
        view: {required: true},
        formFieldOptions: {required: true},
        plantItem: {required: true, type: Object},
    },
    created() {
        // We copy the plantItem property into local formData
        // so that it will be editable if/when necessary.
        this.formData = Object.assign({}, this.plantItem);
        this.editMode = this.edit;
    },
    watch: {
        // We will need to watch for the plantItem changing because the user
        // clicks some other tree item.  We will need to display that item now
        // rather than the previous contents of formData.
        plantItem() {
            this.formData = Object.assign({isolationPoints: []}, this.plantItem);
        }
    },
    computed: {
        canEdit() {
            return this.plantItem.can.update
        },
        canCreate() {
            return this.plantItem.can.create
        },
        canDelete() {
            return this.plantItem.can.delete
        },
        isSaved(){
            return this.plantItem.id > 0
        },
        isEditable() {   // if user has permission *AND* not already editing!
            return this.canEdit && !this.editMode
        },
        isDeletable() {
            return this.canDelete && this.isSaved && !this.editMode
        },
        isFertile() {  // Can it have a child?
            return this.canCreate && this.isSaved && ! this.editMode
        },
        displayMode: {
            get() {
                return this.view
            },
            set(viewMode) {
                this.$root.$emit('viewModeChange', viewMode)
            }
        },
        brandLabel() {
            // ePAS convention is to prefix description with functionalLocation
            // if functionalLocation is set.
            if (this.formData.functionalLocation) {
                return this.formData.functionalLocation + ' - '
                    + this.formData.description
            }
            return this.formData.description
        },
        fields() {        // The fields that should be shown based on view mode
            if (this.view === 'brief') {
                return this.briefFields
            }
            return this.allFields
        },
        allFields() {
            return this.briefFields.concat(this.expandedFields)
        },
    },
    data() {
        return {
            formData: {
                isolationPoints: []     // must be an array!
            },
            editMode: false,
            briefFields: [
                'plantParentId', 'plantId', 'functionalLocation', 'description',
                'location', 'plantType', 'plantGroup', 'isPlantItem', 'isIsolationPoint',
                'isConfinedSpace', 'isSafetySystem', 'circuitVoltage',
                'methodOfProving', 'isolationPoints', 'dataSource',
            ],
            expandedFields: [
                'assetManagementId', 'barcodeId', 'code', 'defaultIsolationCondition',                 'drawingReference', 'isLimitedAuthority', 'isPassingValve',                            'isTemporaryItem', 'plantType', 'updatedAt'
            ],
            displayModeOptions: ['brief', 'expanded'],
        }
    },
    methods: {
        createChild(){
          this.resetFormData()
          this.formData.plantParentId = this.plantItem.plantId
          this.enableEdit()
        },
        enableEdit() {
            this.editMode = true
        },
        cancelEdit() {
            this.editMode = false
        },
        saveAndCloseEdit() {
            this.editMode = false
        },
        confirmDelete() {
            let message = `Really delete term "${this.plantItem.plantId}"?`
            this.$bvModal.msgBoxConfirm(message, {
                variant: 'danger',
                okVariant: 'danger',
                okTitle: 'YES',
                cancelTitle: 'NO',
                title: 'Confirm Deletion of ' + this.plantItem.plantId,
                centered: true,
            })
            .then(value => {
                if (value) {
                    this.deleteItem()
                }
            })
            .catch(err => {
                console.log(err)
                this.$bvToast.toast('Plant item was not deleted',
                    {title: 'Error', variant: 'danger'})
            })
        },
        // Call API to persist deletion
        deleteItem() {
            let url = this.route('api.plant_items.delete',[this.plantItem.id], false)
            this.$http.delete(url)
                .then((result) => {      // success
                    this.$bvToast.toast('Plant item was deleted', {title: 'Success', variant: 'success'})
                    this.resetFormData()
                    this.$root.$emit('plantItemDelete', this.plantItem.id)
                }, (error) => {
                    console.log(error)
                    this.$bvToast.toast('Plant item was not deleted', {title: 'Error', variant: 'danger'})
                }).catch((exception) => {
                this.$bvToast.toast('An error occured while trying to delete plant item', {title: 'Error', variant: 'danger'})
                console.log(exception)
            })
        },
        resetFormData() {
            this.formData = {
                isolationPoints: []     // must be an array!
            }
        }
    },

}
</script>

<style scoped>
.isolation-point {
    font-style: italic;
}

.navbar-brand {
    color: #ca0000;
}

.navbar-brand:hover {
    color: #ca0000;
}
</style>
