import V1AdminApiClient from "../../clients/api/v1-admin-api-client";

export const fetchSelect = () => ({
    open: false,
    q: "",
    selectedItem: null,
    selectedItemOptionValueName: null,
    selectedItemOptionLabelName: null,
    items: [],
    url: null,
    loading: false,
    client: new V1AdminApiClient(),

    init() {
        this.url = this.$el.getAttribute("data-url");

        const selectedItemOptionValueName = this.$el.getAttribute(
            "data-selected-item-option-value-name",
        );

        if (
            selectedItemOptionValueName !== null &&
            selectedItemOptionValueName !== ""
        ) {
            this.selectedItemOptionValueName = selectedItemOptionValueName;
        }

        const selectedItemOptionLabelName = this.$el.getAttribute(
            "data-selected-item-option-label-name",
        );

        if (
            selectedItemOptionLabelName !== null &&
            selectedItemOptionLabelName !== ""
        ) {
            this.selectedItemOptionLabelName = selectedItemOptionLabelName;
        }

        let selectedItem = this.$el.getAttribute("data-selected-item");

        if (selectedItem !== null && selectedItem !== "") {
            try {
                selectedItem = JSON.parse(selectedItem);
            } catch (e) {
                selectedItem = selectedItem;
            }

            this.items.push(selectedItem);
            this.setSelectedItem(selectedItem);
        }
    },

    getItems() {
        return this.items;
    },

    async fetchItems() {
        if (this.q === "") {
            this.open = false;
            this.setSelectedItem(null);

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

    setSelectedItem(item) {
        if (this.isItemSelected(item)) {
            this.selectedItem = null;
            this.q = "";
        } else {
            this.selectedItem = item;

            if (item !== null) {
                this.q =
                    this.selectedItemOptionLabelName === null
                        ? item
                        : item[this.selectedItemOptionLabelName];
            }
        }

        this.open = false;
    },

    isItemSelected(item) {
        return this.selectedItem === item;
    },

    getItemValueName() {
        return this.selectedItemOptionValueName === null
            ? "item"
            : `item.${this.selectedItemOptionValueName}`;
    },
});
