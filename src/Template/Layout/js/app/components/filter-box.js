/**
 * Filter Box View component
 *
 * allows to filter a list of objects with a text field, properties and a pagination toolbar
 *
 * <filter-box-view> component
 *
 * @prop {String} objectsLabel
 * @prop {String} placeholder
 * @prop {Boolean} showFilterButtons
 * @prop {Object} initFilter
 * @prop {Object} relationTypes relation types available for relation (left/right)
 * @prop {Array} filterList custom filters to show
 * @prop {Object} pagination
 * @prop {String} configPaginateSizes
 */

import { DEFAULT_PAGINATION, DEFAULT_FILTER } from 'app/mixins/paginated-content';
import InputDynamicAttributes from 'app/components/input-dynamic-attributes';
import merge from 'deepmerge';

export default {
    components: {
        InputDynamicAttributes
    },

    props: {
        objectsLabel: {
            type: String,
            default: "Objects"
        },
        placeholder: {
            type: String,
            default: "Search"
        },
        showFilterButtons: {
            type: Boolean,
            default: true
        },
        initFilter: {
            type: Object,
            default: () => {
                return {
                    q: "",
                    filter: {
                        type: ""
                    }
                };
            }
        },
        relationTypes: {
            type: Object
        },
        filterList: {
            type: Array
        },
        pagination: {
            type: Object,
            default: () => DEFAULT_PAGINATION
        },
        configPaginateSizes: {
            type: String,
            default: "[10]"
        }
    },

    data() {
        return {
            queryFilter: {}, // QueryFilter Object
            timer: null,
            pageSize: this.pagination.page_size // pageSize value for pagination page size
        };
    },

    created() {
        // merge default filters with initFilter
        let customFilters = this.loadCustomFilters();
        this.queryFilter = merge.all([
            DEFAULT_FILTER,
            this.queryFilter,
            customFilters,
            this.initFilter
        ]);
    },

    computed: {
        paginateSizes() {
            return JSON.parse(this.configPaginateSizes);
        },

        /**
         * get relation's right object types
         *
         * @returns {Array} array of object types
         */
        rightTypes() {
            return (this.relationTypes && this.relationTypes.right) || [];
        },

        /**
         * check which navigation layout needs to be rendered
         *
         * @return {void}
         */
        isFullPaginationLayout() {
            return (
                this.pagination.page_count > 1 &&
                this.pagination.page_count <= 7
            );
        }
    },

    watch: {
        /**
         * watch initFilter and assign it to queryFilter
         *
         * @param {Object} value filter object
         *
         * @returns {void}
         */
        initFilter(value) {
            this.queryFilter = merge(this.queryFilter, value);
        },

        /**
         * watcher for pageSize variable, change pageSize and reload relations
         *
         * @param {Number} value
         *
         * @emits Event#filter-update-page-size
         *
         * @returns {void}
         */
        pageSize(value) {
            this.$emit("filter-update-page-size", this.pageSize);
        }
    },

    methods: {
        /**
         * trigger filter-objects event when query string has 3 or more carachter
         *
         * @emits Event#filter-objects
         */
        onQueryStringChange() {
            let queryString = this.queryFilter.q || "";

            clearTimeout(this.timer);
            if (queryString.length >= 3 || queryString.length == 0) {
                this.timer = setTimeout(() => {
                    this.$emit("filter-objects", this.queryFilter);
                }, 300);
            }
        },

        /**
         * load custom filters property names
         *
         * @returns {Object} filters' name
         */
        loadCustomFilters() {
            let filter = {};
            if (this.filterList) {
                this.filterList.forEach(
                    f => (filter[f.name] = f.date ? {} : "")
                );
            }

            return { filter: filter };
        },

        /**
         * apply filters
         *
         * @emits Event#filter-objects-submit
         */
        applyFilter() {
            this.$emit("filter-objects-submit", this.queryFilter);
        },

        /**
         * reset filters
         *
         * @emits Event#filter-reset
         */
        resetFilter() {
            this.$emit("filter-reset");
        },

        /**
         * change page with index {index}
         *
         * @param {Number} index page number
         *
         * @emits Event#filter-update-current-page
         */
        changePage(index) {
            this.$emit("filter-update-current-page", index);
        }
    }
};
