<template>
    <div class="form-inline mt-2">
        <v-select
            class="align-middle filters-select"
            v-model="selection"
            placeholder="type to search.."
            :options="items"
            :clearable="true"
            :filterable="false"
            :searchable="true"
            :clearSearchOnSelect="true"
            label="plantId"
            :multiple="multiple"
            @search="newQuery"
            @change="resetOptions"
        >
            <template slot="no-options">{{noOptionsText}}</template>

            <template v-slot:option="option">
                {{ formattedOption(option) }}
            </template>

        </v-select>
    </div>
</template>

<script>
    /**
     * A component to select one or more isolation points.
     *
     */
    import vSelect from 'vue-select';
    export default {
        name: "IsolationPointSelect",
        props: {
            /**
             * Array of plant item objects containing id and plantId properties
             *
             * @model
             */
            value: {type: Array, default: null},
            /**
             * The name of a URL route to query
             */
            routeName: {type: String, default: 'api.plant_items.data.isolation_points'},
            /**
             * Whether to allow multiple selections or not.
             */
            multiple : {type: Boolean, default: true},

        },
        components: {
            vSelect
        },
        data(){
            return {
                query: '',
                items: [],
            }
        },
        computed:{
            selection: {
                get(){
                    return this.value;
                },
                set(v) {
                    this.$emit('input', v);
                }
            },
            noOptionsText(){
                if (this.query.length < 2){
                    return 'Start typing an isolation point plant id to see options.\n'
                }
                return 'Sorry, no matching isolation points\n'
            }
        },
        methods:{
            // When the query value changes, fetch new results from
            // the API - in practice this action should be debounced
            newQuery(search, loading) {
                this.query = search
                if (search.length > 1){
                    loading(true);
                    axios.get(this.route(this.routeName,{plantIdLike:search}, false))
                        .then((res) => {
                            if (Array.isArray(res.data)){
                                this.items = res.data // data is axios property
                            }else{
                                this.items = [];
                            }
                            loading(false);
                        })
                }else{
                    this.items = [];
                    loading(false);
                }

            },
            formattedOption(option){
                    return option.plantId
            },
            resetOptions(){
                this.items = []
                this.query = ''
                this.newQuery(this.query,  ()=>{})
            }

        }


    }
</script>

<style scoped>
.filters-select {
    width: 100%;
}
</style>
