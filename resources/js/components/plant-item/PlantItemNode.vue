<template>
    <li>
        <template ref="subtree" v-if="hasChildren">
        <span @click="toggle()"
              :class="{caret: hasChildren, 'caret-down': isExpanded}">
            <b-link :class="{isolation: isIsolationPoint}"
                    @click="$root.$emit('plantItemClick',plantItem.id)">
                <span v-if="plantItem.functionalLocation">{{ plantItem.functionalLocation }} - </span>
                    {{plantItem.description}}
            </b-link>
        </span>
        <plant-item-tree
            v-show="isExpanded && isPopulated"
            :plant-items="sortedChildren">
        </plant-item-tree>
        <ul v-show="isExpanded && !isPopulated"><li>Loading...</li></ul>
        </template>

        <template v-else>
            <b-link
                :class="{isolation: isIsolationPoint}"
                @click="$root.$emit('plantItemClick',plantItem.id)">
                <span v-if="plantItem.functionalLocation" >
                    {{ plantItem.functionalLocation }} -
                </span>
                {{plantItem.description}}
            </b-link>
        </template>

    </li>

</template>

<script>
import PlantItemTree from "./PlantItemTree";
export default {
    name: "PlantItemNode",
    components: {PlantItemTree},
    props: {
        plantItem: {required: true, type: Object},
    },
    mounted() {
        // When a plant item is created, it might need to
        // be added as one of our children
        this.$root.$on('plantItemCreate',this.addNode)

        // Similarly, a deleted item may have been one of our children
        this.$root.$on('plantItemDelete',this.removeChild)
    },
    data() {
        return {
            isExpanded: false,
            isPopulated: false,
            children: [],
        }
    },
    computed: {
      hasChildren(){
          if (this.isPopulated && this.children.length < 1){
              // Children must have been populated and then removed, so ignore
              // plantItem.hasChildren that came from the server.
              return false
          }
          return this.plantItem.hasChildren || this.children.length > 0
      },
      isIsolationPoint() {
          return this.formatBoolean(this.plantItem.isIsolationPoint) === 'YES'
      },
      sortedChildren(){
          return _.sortBy(this.children, [
              function(o) {
                // Put hasChildren ahead of not hasChildren
                return o.hasChildren ? 1 : 2 },
              ], 'plantId')
      }
    },
    methods: {
        addNode(data) {
            if (this.plantItem.plantId === data.plantParentId){
                this.populate()
            }
        },
        removeChild(id) {
            this.children = _.reject(this.children,o => o.id == id)
        },
        toggle(){
            // Make an async call to get data for child nodes tree
            if (this.plantItem.hasChildren && !this.isPopulated){
                this.populate()
            }
            this.isExpanded = !this.isExpanded
        },
        populate(){
            this.$http.get('/api/plant-items/children', {
                params: {
                    'plant_parent_id': this.plantItem.plantId
                }
            })
            .then((result) => {      // success
                    //console.log(result.data)
                    this.children = result.data;
                    this.isPopulated = true;
                    this.isExpanded = true;
                }, (error) => {
                    let message = 'Failed to retrieve data';
                    //this.makeErrorToast(message, error)
                }).catch( (exception) => {
                  console.log(exception)
            })
        },
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

.isolation{
    font-style: italic;
}

/* Style the caret/arrow */
.caret {
    cursor: pointer;
    user-select: none; /* Prevent text selection */
}

/* Create the caret/arrow with a unicode, and style it */
.caret::before {
    content: "\25B6";
    color: black;
    display: inline-block;
    margin-right: 6px;
}

/* Create the caret/arrow with a unicode, and style it */
.caret-substitute::before {
    content: "\00AF";
    color: black;
    display: inline-block;
    margin-right: 6px;
}

/* Rotate the caret/arrow icon when clicked on (using JavaScript) */
.caret-down::before {
    transform: rotate(90deg);
}
</style>
