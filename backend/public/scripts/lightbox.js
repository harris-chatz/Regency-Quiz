// Image lightbox panel
// Δημιουργεί ένα overlay και ανοίγει σε μεγέθυνση όποια εικόνα έχει data-lightbox.

const imagePanel = document.querySelector(".lightbox");

if (imagePanel) {
  const imagePanelClose = document.querySelector(".lightbox .close-btn");
  const imageContainer = document.querySelector(".lightbox .img-container");

  function openLightbox(src, alt) {
    if (!imageContainer) return;
    imageContainer.innerHTML = "";
    const img = document.createElement("img");
    img.src = src;
    img.alt = alt || "";
    imageContainer.appendChild(img);
    imagePanel.classList.add("show");
    document.body.classList.add("stop-scrolling");
  }

  function closeLightbox() {
    imagePanel.classList.remove("show");
    document.body.classList.remove("stop-scrolling");
  }

  document.querySelectorAll("[data-lightbox]").forEach((trigger) => {
    trigger.addEventListener("click", function () {
      const img = this.tagName === "IMG" ? this : this.querySelector("img");
      const src = this.getAttribute("data-lightbox") || (img && img.src);
      if (src) openLightbox(src, img && img.alt);
    });
  });

  if (imagePanelClose) {
    imagePanelClose.addEventListener("click", closeLightbox);
  }

  imagePanel.addEventListener("click", function (e) {
    if (e.target === imagePanel) closeLightbox();
  });
}
