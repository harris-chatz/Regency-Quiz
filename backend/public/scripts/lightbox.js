//  Gallery image, full screen

/*
    <div class="lightbox">
        <div class="close-btn">
            <span></span>
            <span></span>
        </div>
        <div class="img-container"></div>
    </div>
*/

// select gallery-items
const galleryImg = document.querySelectorAll(".gallery-item");

const imagePanel = document.querySelector(".lightbox");
const imagePanelClose = document.querySelector(".lightbox .close-btn");
const imageContainer = document.querySelector(".lightbox .img-container");
//console.log(galleryImg);

if (galleryImg) {
  galleryImg.forEach((item) => {
    item.addEventListener("click", function () {
      popUpImage(item);
    });
  });
}

if (imagePanel) {
  imagePanelClose.addEventListener("click", function () {
    imageContainer.style.width = "0px";
    imageContainer.style.height = "0px";

    setTimeout(function () {
      const img = imageContainer.querySelector("img");
      img.remove();
      imageContainer.classList.remove("show");
      imagePanel.classList.remove("show");
    }, 400);
  });
}

if (imagePanelClose) {
  imagePanel.addEventListener("click", function () {
    imageContainer.style.width = "0px";
    imageContainer.style.height = "0px";

    setTimeout(function () {
      const img = imageContainer.querySelector("img");
      img.remove();
      imageContainer.classList.remove("show");
      imagePanel.classList.remove("show");
    }, 400);
  });
}

function popUpImage(el) {
  imagePanel.classList.add("show");
  const selectedImg = el.querySelector("img");

  const ar = selectedImg.naturalWidth / selectedImg.naturalHeight;

  const img = document.createElement("img");
  img.className = "img-center";
  img.src = selectedImg.src;

  const w = window.innerWidth;
  const h = window.innerHeight;

  if (ar > 1) {
    let estHeight = (w - 50) / ar;
    if (estHeight < h - 50) {
      imageContainer.style.width = w - 50 + "px";
      imageContainer.style.height = estHeight + "px";
    } else {
      imageContainer.style.width = (h - 50) * ar + "px";
      imageContainer.style.height = h - 50 + "px";
    }
  } else {
    let estWidth = (h - 50) * ar;
    if (estWidth < w - 50) {
      imageContainer.style.height = h - 50 + "px";
      imageContainer.style.width = (h - 50) * ar + "px";
    } else {
      imageContainer.style.height = (w - 50) / ar + "px";
      imageContainer.style.width = w - 50 + "px";
    }
  }

  imageContainer.appendChild(img);

  setTimeout(function () {
    imageContainer.classList.add("show");
  }, 400);
}
