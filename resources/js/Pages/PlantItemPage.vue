<template>
    <b-container class="plant-items">
        <b-row>
            <!-- The tree in left column -->
            <b-col class="overflow-auto">
                <h2>Plant Items</h2>
                <plant-item-tree ref="treetop" :plant-items="plantItems"></plant-item-tree>
            </b-col>

            <!-- The detail in right column -->
            <b-col class="overflow-auto">
                <div :class="{fixed:true, scrolling:isScrollbable}">
                <plant-item-detail
                    v-if="isPlantItemShown"
                    :plant-item="plantItem"
                    :form-field-options="formFieldOptions"
                    :view="viewMode">
                </plant-item-detail>
                </div>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
import PlantItemLayout from './PlantItemLayout'


export default {
    name: "PlantItemPage",
    props: {
        plantItems: {required: true},
        formFieldOptions: {required: true},
    },
    layout: PlantItemLayout,
    components: {

    },
    mounted() {
        // Descendant elements will communicate certain actions
        // by emitting $root events.  Here we register
        // methods to handle those events
        this.$root.$on('plantItemClick', this.fetchDetails)
        this.$root.$on('viewModeChange', this.setViewMode)
        this.$root.$on('plantItemDelete',this.onDelete)
    },
    computed: {
      // We want detail panel hidden when there is no content
      isPlantItemShown(){
          return ! _.isEmpty(this.plantItem);
      },
      isScrollbable(){
        // Should only need scroll bars in expanded view mode
        return this.viewMode === 'expanded'
      }
    },
    data() {
        return {
            plantItem: {},      // Plant item selected for detail view
            viewMode: 'brief',
        }
    },
    methods: {
        // Used to empty out our plantItem data when notified
        // that it was deleted.
        onDelete(id){
            if (this.plantItem.id == id){
                this.plantItem = {};
            }
        },
        // Allows user to change view modes (brief | expanded)
        setViewMode(value){
          this.viewMode = value
        },
        // Fetch the plantItem for which user has expressed in interest in
        // seeing the details.
        fetchDetails(id) {
            this.$http.get(this.route('api.plant_items.item',{id}))
                .then((result) => {      // success
                    this.plantItem = result.data;
                }, (error) => {
                    console.log(error)
                    this.$bvToast.toast('Unable to retrieve plant item data',{title:'Error', variant:'danger'}) ;
                }).catch((exception) => {
                this.$bvToast.toast('An error occured during plant item data retrieval',{title:'Error', variant:'danger'}) ;
                console.log(exception)
            })
        }

    }
}
</script>

<style scoped>
.plant-items {
    margin-left: 1em;
    margin-top: 1em;
    width: 95%;
    height: 100vh;
}

.plant-items .fixed {
    position: fixed;
    width: 640px;
    height: 900px;
}

.scrolling {
    overflow-y: scroll;

}

</style>
