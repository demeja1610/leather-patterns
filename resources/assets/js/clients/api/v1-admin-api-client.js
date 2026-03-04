export default class V1AdminApiClient {
    async get(url, csrf, params = {}, headers = {}) {
        if (Object.keys(params).length !== 0) {
            const queryString = new URLSearchParams(params).toString();

            const hasQueryParams = url.includes("?");

            url = hasQueryParams
                ? `${url}&${queryString}`
                : `${url}?${queryString}`;
        }

        try {
            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf,
                    ...headers,
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

    async post(url, csrf, body, headers = {}) {
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf,
                    ...headers,
                },
                body: body,
            });

            return response;
        } catch (error) {
            throw error;
        }
    }
}
