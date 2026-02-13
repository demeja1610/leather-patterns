export default class V1ApiClient {
    static ALL_ENDPOINT = "all";

    static PATTERN_CATEGORY_ENDPOINT = "pattern-category";

    static PATTERN_TAG_ENDPOINT = "pattern-tag";

    static PATTERN_AUTHOR_ENDPOINT = "pattern-author";

    constructor() {
        this.apiBaseUrl = `${import.meta.env.VITE_APP_API_BASE_URL}/v1`;
    }

    async _get(endpoint, params = {}) {
        let url = `${this.apiBaseUrl}/${endpoint}`;

        if (Object.keys(params).length !== 0) {
            const queryString = new URLSearchParams(params).toString();

            url = `${url}?${queryString}`;
        }

        try {
            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            return data;
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    async getAllPatternCategories(from = null) {
        const params = {};

        if (from !== null) {
            params.from = from;
        }

        return await this._get(
            `${V1ApiClient.PATTERN_CATEGORY_ENDPOINT}/${V1ApiClient.ALL_ENDPOINT}`,
        );
    }

    async getAllPatternTags(from = null) {
        const params = {};

        if (from !== null) {
            params.from = from;
        }

        return await this._get(
            `${V1ApiClient.PATTERN_TAG_ENDPOINT}/${V1ApiClient.ALL_ENDPOINT}`,
        );
    }

    async getAllPatternAuthors(from = null) {
        const params = {};

        if (from !== null) {
            params.from = from;
        }

        return await this._get(
            `${V1ApiClient.PATTERN_AUTHOR_ENDPOINT}/${V1ApiClient.ALL_ENDPOINT}`,
        );
    }
}
