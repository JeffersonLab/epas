<template>
  <ul class="treeview" v-show="isVisible">
    <plant-item-node v-for="node in sortedNodes" :plant-item="node" :key="node.plant_id" ref="`tree-node-${node.id}`"></plant-item-node>
  </ul>
</template>

<script>
// Note that we mustn't import Plant-Item Node here!  Doing so would create a circular-reference
// problem between PlantItemTree and PlantItemNode.  See beforCreate for the work-around.

export default {
    name: "PlantItemTree",
    props: {
        plantItems: {required: true },
    },
    beforeCreate: function () {
        // workaround to handle circular dependancy between PlantItemNode, PlantItemTree
        // @see https://vuejs.org/v2/guide/components-edge-cases.html#Circular-References-Between-Components
        this.$options.components.PlantItemNode = require('./PlantItemNode').default
    },
    created() {
        //Place the read-only plantItems property into a local nodes that can be modified
        this.nodes = Object.assign({}, this.plantItems);
    },
    watch: {
        // If the plantItems property changes, we need to update local nodes copy
        plantItems() {
            this.nodes = Object.assign({}, this.plantItems);
        }
    },
    mounted() {
        // When a plant item is deleted, scan the local nodes
        // to see if it belongs to us.  If so, remove it from nodes list.
        this.$root.$on('plantItemDelete',this.removeNode)

        // When a plant item is updated, its label in the tree
        // view might need to be updated.
        this.$root.$on('plantItemUpdate',this.updateNode)

    },
    data() {
        return {
            isVisible: true,
            nodes: {},
        }
    },
    computed: {
        sortedNodes(){
            return _.sortBy(this.nodes, [
                function(o) {
                    // Put hasChildren ahead of not hasChildren
                    return o.hasChildren ? 1 : 2 },
            ], 'plantId')
        }
    },
    methods: {
        removeNode(id){
            this.nodes = _.reject(this.nodes,o => o.id == id)
        },
        updateNode(data){
            let found = _.find(this.nodes, o => o.id == data.id)
            if (found){
                found = Object.assign(found, data)
            }
        }

    }
}
</script>

<style scoped>
/* Remove default bullets */
ul, #myUL {
    list-style-type: none;
}

/* Remove margins and padding from the parent ul */
#myUL {
    margin: 0;
    padding: 0;
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

/* Rotate the caret/arrow icon when clicked on (using JavaScript) */
.caret-down::before {
    transform: rotate(90deg);
}

/* Hide the nested list */
.nested {
    display: none;
}

/* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
.active {
    display: block;
}
</style>
