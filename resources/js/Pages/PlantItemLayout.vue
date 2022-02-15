<template>
    <div>
        <b-navbar class="menu-bar" toggleable="lg" type="dark" variant="info">
            <b-navbar-brand href="/plant-items">Plant Items</b-navbar-brand>

            <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

            <b-collapse id="nav-collapse" is-nav>
                <b-navbar-nav>
                    <b-nav-item>
                        <inertia-link :href="route('plant_items.index')">
                            Tree
                        </inertia-link>
                    </b-nav-item>
                    <b-nav-item>
                        <inertia-link :href="route('plant_items.table')">
                            Table
                        </inertia-link>
                    </b-nav-item>
                    <b-nav-item-dropdown class="forms-dropdown" text="Forms" right>
                        <!--
                        We have to use regular links to forms in order to avoid CORS
                         problems when the authentication method is shibboleth and
                         the user needs to get redirected to login before continuing.
                         -->
                        <b-dropdown-item  :href="route('plant_items.create')">
                            New Plant Item
                        </b-dropdown-item>
                        <b-dropdown-item :href="route('plant_items.upload_plant_items_form')">
                            Upload Plant Items
                        </b-dropdown-item>
                        <b-dropdown-item :href="route('plant_items.upload_isolation_points_form')">
                          Upload Isolation Points
                        </b-dropdown-item>

                    </b-nav-item-dropdown>
                </b-navbar-nav>

                <!-- Right aligned nav items -->
                <b-navbar-nav class="ml-auto">
                    <b-nav-form @submit.stop.prevent="doSearch()">
                        <b-form-input size="sm" class="mr-sm-2" placeholder="Search Plant Items"
                                      v-model="searchForm.search"
                                      @focus="$event.target.select()"></b-form-input>
                        <b-button size="sm" class="my-2 my-sm-0" type="submit">Search</b-button>
                    </b-nav-form>

                    <!-- Ziggy can't handle closure based routes!-->
                    <b-nav-item href="/login" right v-if="! isAuthenticated">
                        <b-button size="sm">
                            <b-icon icon="power"></b-icon>
                            Login
                        </b-button>
                    </b-nav-item>
                    <!-- Ziggy can't handle closure based routes!-->
                    <b-nav-item href="/logout" right v-if="isAuthenticated">
                        <b-button size="sm">
                            <b-icon icon="power"></b-icon>
                            Logout {{ currentUser.username }}
                        </b-button>
                    </b-nav-item>

                </b-navbar-nav>
            </b-collapse>
        </b-navbar>

        <div v-if="$page.props.flash" class="alerts">
            <b-alert variant="success" :show="hasSuccess" dismissible>
                {{ $page.props.flash.success }}
            </b-alert>
            <b-alert variant="warning" :show="hasWarning" dismissible>
                {{ $page.props.flash.warning }}
            </b-alert>
        </div>


        <div class="primary-content">
            <slot/>
        </div>
    </div>
</template>

<script>


export default {
    name: "PlantItemLayout",
    strict: true,
    props: [

    ],
    data() {
        return {
            searchForm: this.$inertia.form({
                search: ''
            }),
        }
    },
    components: {},
    computed: {
        currentUser(){
            return this.$page.props.currentUser
        },
        isAuthenticated() {
            return (this.currentUser != null )
        },
        hasSuccess() {
            return this.$page.props.flash.success ? true : false;
        },
        hasWarning() {
            return this.$page.props.flash.warning ? true : false;
        }
    },
    methods: {
        doSearch: function () {
            this.searchForm.get(this.route('plant_items.table'))
        },

    }
}
</script>

<style>
.forms-dropdown span{
    color: white;
}
</style>

<style scoped>
#login-status {
    text-align: right;
    padding-right: 1rem;
}

div.primary-content {
    margin-bottom: 1em;
    margin-left: 1em;
}

.nav-link a{
    color: white;
}

.nav-link a:hover {
    color: white;
    font-weight: bold;
}


.menu-bar, .alerts {
    width: 95%;
    margin: auto;
}
</style>
