<template>
    <b-container fluid class="plant-items">
        <b-form class="plant-items-form" @submit.prevent="onSubmit" @reset="onReset">
            <h1>Isolation Point Spreadsheet Upload Form</h1>
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
                <p>Formatting:</p>
                <ul>
                    <li>See the <a href="/epas/isolation_point_template.xlsx">template spreadsheet</a> for example of correct layout</li>
                    <li>Make sure column names in header row are <i>plant id</i> and <i>isolation point plant id</i>. Other columns will be ignored.</li>
                    <li>Leave no blank rows before header row.</li>
                    <li>Leave no blank/incomplete rows in the middle of data.</li>
                    <li>May only reference existing plant_id values in your plant_parent_id and isolation_point_plant_id columns.
                    </li>
                    <li>Make sure the file is an Excel spreadsheet in .xlsx format.</li>
                </ul>

            </div>
        </div>

        <div class="row">
            <div class="col">
                <fieldset>
                    <!-- Elements to upload Excel Spreadsheet -->
                    <legend>Sheet Number</legend>
                   <b-form-spinbutton id="tab-sb" v-model="form.sheet" min="1" max="5"></b-form-spinbutton>
                </fieldset>
            </div>
            <div class="col">
                <h2>Sheet Number Help</h2>
                <p>Specify the number of the worksheet in the .xlsx file that contains isolation point data
                to be assigned.  The value defaults to 2 because a typical use pattern is to place plant items in
                sheet 1 and (extra) isolation point assignments in sheet 2.</p>
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
                <dt>Preserve Existing</dt>
                <dd>Add new isolation points from the spreadsheet while keeping any existing assignments.</dd>
                <dt>Replace Existing</dt>
                <dd>First remove existing isolation point records and then update listed plant items with new data from the spreadsheet.</dd>
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
    name: "IsolationPointsUploadForm",
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
                sheet: 2,
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
                this.form.post('/plant-items/upload-isolation-points')
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
            this.form.sheet = 2
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
