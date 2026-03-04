import V1AdminApiClient from "../../clients/api/v1-admin-api-client";

export const previewInputFile = () => ({
    url: null,
    csrf: null,
    name: null,
    client: new V1AdminApiClient(),
    files: [],
    removeFiles: [],
    loading: false,
    errors: [],

    init() {
        this.url = this.$el.getAttribute("data-url");
        this.name = this.$el.getAttribute("data-name");

        this.csrf = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        let existingFiles = this.$el.getAttribute("data-files");

        if (
            existingFiles !== null &&
            existingFiles !== "" &&
            existingFiles !== "[]"
        ) {
            try {
                existingFiles = JSON.parse(existingFiles);
            } catch (error) {
                existingFiles = [existingFiles];
            }

            existingFiles.forEach((file) => this.addFile(file));
        }
    },

    getFiles() {
        return this.files;
    },

    getRemoveFiles() {
        return this.removeFiles;
    },

    getErrors() {
        return this.errors;
    },

    emptyErrors() {
        this.errors = [];
    },

    addFile(file) {
        console.log(file);

        const _file =
            typeof file === "string"
                ? {
                      url: file,
                      ext: this.getExtension(file),
                  }
                : {
                      ...file,
                      ext: this.getExtension(file.url),
                  };

        this.files.push(_file);
    },

    async uploadFiles() {
        if (!this.loading) {
            this.emptyErrors();

            this.loading = true;

            const formData = new FormData();
            const dataTransfer = new DataTransfer();

            Array.from(this.$event.target.files).forEach((file, index) => {
                formData.append(this.name, file);
            });

            try {
                const resp = await this.client.post(
                    this.url,
                    this.csrf,
                    formData,
                );

                if (resp.status >= 200 && resp.status < 300) {
                    if (this.$event.target.multiple !== true) {
                        this.emptyFiles();
                    }

                    const data = await resp.json();

                    data.forEach((url) => this.addFile(url));

                    this.$event.target.files = dataTransfer.files;
                } else {
                    if (resp.status === 422) {
                        const errors = await resp.json();

                        this.errors = Object.values(errors.errors).flat();

                        this.$event.target.files = dataTransfer.files;
                    }
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        }
    },

    removeFile(file) {
        const idx = this.files.findIndex((el) => el === file);

        if (idx !== -1) {
            this.files.splice(idx, 1);
        }

        if (file.id) {
            this.removeFiles.push(file);
        }
    },

    emptyFiles() {
        this.files = [];
    },

    getExtension(path) {
        const lastDotIndex = path.lastIndexOf(".");

        if (lastDotIndex === -1 || lastDotIndex === path.length - 1) {
            return "Unknown";
        }

        return path.substring(lastDotIndex + 1);
    },
});
