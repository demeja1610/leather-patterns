import fslightbox from "fslightbox";

export default function imagePopups() {
    const galleries = document.querySelectorAll("[data-gallery]");

    if (galleries.length === 0) {
        return;
    }

    galleries.forEach((gallery) => {
        const trigger = gallery.querySelector("[data-gallery-trigger]");

        if (trigger === null) {
            return;
        }

        trigger.addEventListener("click", (e) => {
            e.preventDefault();

            const images = gallery.querySelectorAll("[data-gallery-image]");

            const imageLinks = Array.from(images).map((item) => {
                return item.src;
            });

            const lightbox = new FsLightbox();

            lightbox.props.sources = imageLinks;
            
            lightbox.open();
        });
    });
}
