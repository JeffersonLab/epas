<template>
    <b-container fluid class="plant-items">
        <b-form class="plant-items-form" @submit.prevent="onSubmit" @reset="onReset">
            <h1>Plant Item Spreadsheet Upload Form</h1>
            <hr />
        <div class="row">
            <div class="col">
                <div class="upload-feedback" v-if="errors && errors.form">
                    <b-card>
                        <b-card-text><pre class="errors">{{errors.form}}</pre></b-card-text>
                    </b-card>
                </div>
                <fieldset class="file-upload">
                    <!-- Elements to upload Excel Spreadsheet -->
                    <legend>Choose Spreadsheet</legend>
                    <b-form-file
                        required
                        v-model="form.file"
                        ref="file-input"
                        :state="Boolean(form.file)"
                        placeholder="Choose a file or drop it here..."
                        drop-placeholder="Drop file here..."
                    ></b-form-file>
                    <p class="description">Be sure to give your spreadsheet a meaningful name -- it will become the data
                        source field for the items imported from it.</p>
                    <div id="upload-feedback" v-if="errors && errors.file">
                        <b-card>
                            <b-card-text><pre class="errors">{{errors.file}}</pre></b-card-text>
                        </b-card>
                    </div>
                </fieldset>
            </div>
            <div class="col">
                <h2>Spreadsheet Help</h2>
                <p>To maximize the likelihood that your spreadsheet will upload successfully:</p>
                <ul>
                    <li>Fill your data into the <a href="/epas/plant_item_template.xlsx">template spreadsheet</a></li>
                    <li>Make sure the file is an Excel spreadsheet in .xlsx format.</li>
                    <li>Leave no blank rows before header row.</li>
                    <li>Use only valid column names in header row.</li>
                    <li>Leave no blank/incomplete rows in the middle of data.</li>
                    <li>Reference only valid plant_id values in your plant_parent_id and isolation_point_plant_id columns.
                    </li>
                    <li>List parent plant items you are creating ahead of any child plant items that reference them.
                    </li>
                </ul>

            </div>
        </div>

        <div class="row">
            <div class="col">
                <fieldset>
                    <!-- Elements to upload Excel Spreadsheet -->
                    <legend>Plant Group</legend>
                    <b-form-select v-model="form.plantGroup" :options="formFieldData.plantGroupOptions"></b-form-select>
                </fieldset>
            </div>
            <div class="col">
                <h2>Plant Group Help</h2>
                <p>For ePAS, each plant item must specify that it belongs to a "Plant Group".  In lieu of specifying a value for reach row in the spreadsheet, a value can be specified here.  If a value is specified here, it will override any plant group values in the spreadsheet.</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <fieldset>
                    <!-- Elements to upload Excel Spreadsheet -->
                    <legend>Options</legend>
                    <b-form-select v-model="form.replaceOption" :options="formFieldData.replaceOptions"></b-form-select>
                </fieldset>
            </div>
            <div class="col">
                <h2>Options Help</h2>
                <dt>Do Not Replace</dt>
                <dd>If the spreadsheet name matches one that already exists or contains any plant_id values that already exist, the file will be rejected.</dd>
                <dt>Replace Spreadsheet</dt>
                <dd>Any plant items whose data source matches the current spreadsheet file name will be removed and then the contents of the current spreadsheet will be added in their place.</dd>
            </div>
        </div>
            <hr />
            <div class="buttons">
                <b-button type="submit" variant="primary">Submit</b-button>
                <b-button type="reset" variant="danger">Reset</b-button>
            </div>
        </b-form>

    </b-container>
</template>

<script>
import PlantItemLayout from './PlantItemLayout'
export default {
    name: "PlantItemUploadForm",
    props: {
        formFieldData: Object,
    },
    layout: PlantItemLayout,
    computed: {
      errors() { return this.$page.props.errors ? this.$page.props.errors : null }
    },
    data() {
        return {
            form: this.$inertia.form({
                file: null,
                plantGroup: '',
                replaceOption: 'keep',
            }),
            feedback: null,
        }
    },
    methods: {
        onSubmit(event) {
            event.preventDefault()
            this.clearFeedback()
            if (this.form.file) {
                this.form.post('/plant-items/upload')
                }else{
                    alert('No file selected for upload.')
                }
            },
        handleHttpError(error, defaultMessage){
            if (error.response){
                alert("The spreadsheet failed to upload because of errors")
                this.feedback = error.response.data ? error.response.data.message : defaultMessage
            } else if (error.request) {
                // The request was made but no response was received
                alert(error.request);
            }else{
                alert('Error', error.message);

            }
        },
        onReset(event) {
            this.$refs['file-input'].reset()
            event.preventDefault()
            this.clearForm()
        },
        clearFeedback(){
          this.feedback = null
        },
        clearForm(){
            // Reset our form values
            this.form.file = null
            this.form.plantGroup = ''
            this.form.replaceOption = 'keep'
            // Trick to reset/clear native browser form validation state
            this.show = false
            this.$nextTick(() => {
                this.show = true
            })
        }
    }
}
</script>

<style scoped>
.plant-items {
    width: 95%;
}
.plant-items-form {
    margin-top: 15px;
    width: 95%;
}
.buttons{
    margin-left: 1em;
}
.errors{
    color: red;
}
</style>
