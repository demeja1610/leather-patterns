import V1AdminApiClient from "../../clients/api/v1-admin-api-client";

export const fetchSelect = () => ({
    open: false,
    q: "",
    selectedKey: null,
    selectedValue: null,
    keyName: null,
    valueName: null,
    items: [],
    url: null,
    loading: false,
    client: new V1AdminApiClient(),

    init() {
        if (this.$el.getAttribute("data-selected-key") !== "") {
            this.selectedKey = this.$el.getAttribute("data-selected-key");
        }
        if (this.$el.getAttribute("data-selected-value") !== "") {
            this.selectedValue = this.$el.getAttribute("data-selected-value");
        }

        this.url = this.$el.getAttribute("data-url");
        this.keyName = this.$el.getAttribute("data-key-name");
        this.valueName = this.$el.getAttribute("data-value-name");

        if (this.q === "") {
            this.q = this.selectedValue;
        }
    },

    getItems() {
        return this.items;
    },

    async fetchItems() {
        if (this.q === "") {
            this.open = false;
            this.selectedKey = null;

            return;
        }

        if (this.open === false) {
            this.open = true;
        }

        this.items = [];
        this.loading = true;

        try {
            const resp = await this.client._unkownGet(this.url, {
                q: this.q,
            });

            this.items = resp.data;
        } catch (error) {
            console.log(error);
        } finally {
            this.loading = false;
        }
    },

    selectItem(item) {
        this.selectedKey = item[this.keyName];

        this.q = item[this.valueName];

        this.open = false;
    },
});
