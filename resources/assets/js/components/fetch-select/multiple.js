import V1AdminApiClient from "../../clients/api/v1-admin-api-client";

export const multipleFetchSelect = () => ({
    open: false,
    q: "",
    selectedItems: [],
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

        let selectedItems = this.$el.getAttribute("data-selected-items");

        if (
            selectedItems !== null &&
            selectedItems !== "" &&
            selectedItems !== "[]"
        ) {
            selectedItems = JSON.parse(selectedItems);

            this.items = selectedItems;
            this.selectedItems = selectedItems;
        }
    },

    getItems() {
        return this.items;
    },

    getSelectedItems() {
        return this.selectedItems;
    },

    toggleSelectedItem(item) {
        if (this.isItemSelected(item)) {
            this.removeSelectedItem(item);
        } else {
            this.addSelectedItem(item);
        }

        this.q = "";
    },

    isItemSelected(item) {
        if (this.selectedItemOptionValueName !== null) {
            return this.selectedItems.some(
                (selectedItem) =>
                    selectedItem[this.selectedItemOptionValueName] ===
                    item[this.selectedItemOptionValueName],
            );
        } else {
            return this.selectedItems.includes(item);
        }
    },

    removeSelectedItem(item) {
        if (this.isItemSelected(item) === false) {
            return;
        }

        const idx =
            this.selectedItemOptionValueName === null
                ? this.selectedItems.indexOf(item)
                : this.selectedItems.findIndex(
                      (selectedItem) =>
                          selectedItem[this.selectedItemOptionValueName] ===
                          item[this.selectedItemOptionValueName],
                  );

        if (idx > -1) {
            this.selectedItems.splice(idx, 1);
        }
    },

    addSelectedItem(item) {
        if (this.isItemSelected(item) === true) {
            return;
        }

        this.selectedItems.push(item);
    },

    shouldShowOptions() {
        return this.open === true;
    },

    showOptions() {
        this.open = true;
    },

    hideOptions() {
        this.open = false;

        this.$refs.textInput.blur();
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
});
