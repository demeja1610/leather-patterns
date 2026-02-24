export default class V1AdminApiClient {
    static SEARCH_ENDPOINT = "search";

    static PATTERN_AUTHOR_ENDPOINT = "pattern-author";

    constructor() {
        this.apiBaseUrl = `${import.meta.env.VITE_APP_ADMIN_API_BASE_URL}/v1`;
    }

    async _unkownGet(url, params = {}) {
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
}
