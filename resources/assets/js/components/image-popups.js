import fslightbox from "fslightbox";

export default function imagePopups() {
    const imagePopupTriggers = document.querySelectorAll(
        ".image-popup-trigger"
    );

    imagePopupTriggers.forEach((trigger) => {
        trigger.addEventListener("click", (e) => {
            e.preventDefault();

            const imagePopupContainer = trigger.closest(
                ".image-popup-container"
            );

            const imagePopupItems =
                imagePopupContainer.querySelectorAll(".image-popup-item");

            const imageSrcs = Array.from(imagePopupItems).map((item) => {
                return item.src;
            });

            const lightbox = new FsLightbox();

            lightbox.props.sources = imageSrcs;
            lightbox.open();
        });
    });
}
