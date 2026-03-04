import V1AdminApiClient from "../../clients/api/v1-admin-api-client";

export const previewInputImage = () => ({
    url: null,
    csrf: null,
    name: null,
    client: new V1AdminApiClient(),
    images: [],
    removeImages: [],
    loading: false,
    errors: [],

    init() {
        this.url = this.$el.getAttribute("data-url");
        this.name = this.$el.getAttribute("data-name");

        this.csrf = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        let existingImages = this.$el.getAttribute("data-images");

        if (
            existingImages !== null &&
            existingImages !== "" &&
            existingImages !== "[]"
        ) {
            try {
                existingImages = JSON.parse(existingImages);
            } catch (error) {
                existingImages = [existingImages];
            }

            existingImages.forEach((image) =>
                this.addImage(image.url, image.isNew),
            );
        }
    },

    getImages() {
        return this.images;
    },

    getRemoveImages() {
        return this.removeImages;
    },

    getErrors() {
        return this.errors;
    },

    emptyErrors() {
        this.errors = [];
    },

    addImage(url, isNew = false) {
        this.images.push({
            url: url,
            isNew: isNew,
        });
    },

    async uploadImages() {
        if (!this.loading) {
            this.emptyErrors();

            this.loading = true;

            const formData = new FormData();
            const dataTransfer = new DataTransfer();

            Array.from(this.$event.target.files).forEach((file, index) => {
                if (file.type.startsWith("image/")) {
                    formData.append(this.name, file);
                }
            });

            try {
                const resp = await this.client.post(
                    this.url,
                    this.csrf,
                    formData,
                );

                if (resp.status >= 200 && resp.status < 300) {
                    if (this.$event.target.multiple !== true) {
                        this.emptyImages();
                    }

                    const data = await resp.json();

                    data.forEach((url) => this.addImage(url, true));

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

            console.log(this.images, this.removeImages);
        }
    },

    removeImage(image) {
        const idx = this.images.findIndex((el) => el === image);

        if (idx !== -1) {
            this.images.splice(idx, 1);
        }

        if (image.isNew === false) {
            this.removeImages.push(image);
        }

        console.log(this.images, this.removeImages);
    },

    emptyImages() {
        this.images = [];
    },

    openLightbox(index) {
        const images = this.$refs.previews.querySelectorAll("img");

        const imageLinks = Array.from(images).map((item) => {
            return item.src;
        });

        const lightbox = new FsLightbox();

        lightbox.props.sources = imageLinks;

        lightbox.open(index);
    },
});
