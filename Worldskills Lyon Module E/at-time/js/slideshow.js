
const SAMPLE_IMAGE_PATHS = [
    "sample-photos/basilique-notre-dame-de-fourviere-lyon.jpg",
    "sample-photos/beautiful-view-in-lyon.jpg",
    "sample-photos/place-bellecour-lyon.jpg",
    "sample-photos/tour-metalique-lyon.jpg"
];

const state = {
    photos: [],
    activeIndex: 0,
    sourceKeys: new Set(),
    idCounter: 0,
    dragPhotoId: null,
    mode: "manual",
    theme: "a",
    modeTimer: null,
    slideIntervalMs: 4000,
    visiblePhotoId: null,
    transitionTimer: null,
    themeDPrimed: false
};

const slideshowElement = document.querySelector(".slideshow");
const imageOrderElement = document.getElementById("image-order");
const fileInput = document.getElementById("image-drop");
const addSampleButton = document.getElementById("add-sample-images");
const dropLabel = fileInput ? fileInput.closest("label") : null;
const modeManualInput = document.getElementById("mode-manual");
const modeAutoInput = document.getElementById("mode-auto");
const modeRandomInput = document.getElementById("mode-random");
const themeInputs = {
    a: document.getElementById("theme-a"),
    b: document.getElementById("theme-b"),
    c: document.getElementById("theme-c"),
    d: document.getElementById("theme-d"),
    e: document.getElementById("theme-e"),
    f: document.getElementById("theme-f")
};

let slideshowStageElement = null;
let slideshowEmptyElement = null;

function getNextId() {
    state.idCounter += 1;
    return state.idCounter;
}

function formatCaptionFromFileName(fileName) {
    const dotIndex = fileName.lastIndexOf(".");
    const baseName = dotIndex > 0 ? fileName.slice(0, dotIndex) : fileName;
    return baseName.replaceAll("-", " ").replaceAll("_", " ").trim().toLowerCase();
}

function getNameFromPath(path) {
    const lastSlash = path.lastIndexOf("/");
    if (lastSlash < 0) {
        return path;
    }
    return path.slice(lastSlash + 1);
}

function isImageFile(file) {
    if (file && file.type && file.type.indexOf("image/") === 0) {
        return true;
    }

    const lowerName = file.name.toLowerCase();
    return (
        lowerName.endsWith(".jpg")
        || lowerName.endsWith(".jpeg")
        || lowerName.endsWith(".png")
        || lowerName.endsWith(".gif")
        || lowerName.endsWith(".webp")
        || lowerName.endsWith(".bmp")
        || lowerName.endsWith(".svg")
    );
}

function setSlideshowThemeClass() {
    if (!slideshowElement) {
        return;
    }

    slideshowElement.classList.remove("theme-a", "theme-b", "theme-c", "theme-d", "theme-e", "theme-f");
    slideshowElement.classList.add(`theme-${state.theme}`);
}

function ensureSlideshowStructure() {
    if (!slideshowElement) {
        return;
    }

    if (!slideshowStageElement) {
        slideshowStageElement = document.createElement("div");
        slideshowStageElement.className = "slideshow-stage";
        slideshowElement.appendChild(slideshowStageElement);
    }

    if (!slideshowEmptyElement) {
        slideshowEmptyElement = document.createElement("p");
        slideshowEmptyElement.className = "slideshow-empty";
        slideshowEmptyElement.textContent = "No photos selected yet.";
    }
}

function getPhotoById(photoId) {
    for (let i = 0; i < state.photos.length; i += 1) {
        if (state.photos[i].id === photoId) {
            return state.photos[i];
        }
    }

    return null;
}

function buildCaptionContent(photo, captionElement) {
    captionElement.innerHTML = "";

    if (state.theme === "e") {
        return;
    }

    if (state.theme === "c") {
        const words = photo.caption.split(" ");
        for (let i = 0; i < words.length; i += 1) {
            const word = words[i];
            if (!word) {
                continue;
            }

            const wordSpan = document.createElement("span");
            wordSpan.textContent = word;
            wordSpan.className = "slideshow-caption-word";
            wordSpan.style.setProperty("--word-index", String(i));
            captionElement.appendChild(wordSpan);
        }

        return;
    }

    captionElement.textContent = photo.caption;
}

function getOrCreateSlideElement(photo) {
    if (photo.slideElement) {
        return photo.slideElement;
    }

    const slideElement = document.createElement("div");
    slideElement.className = "slideshow-slide is-hidden";
    slideElement.dataset.photoId = String(photo.id);

    const imageElement = document.createElement("img");
    imageElement.src = photo.url;
    imageElement.alt = photo.caption;
    imageElement.className = "slideshow-image";

    const captionElement = document.createElement("p");
    captionElement.className = "slideshow-caption";

    slideElement.appendChild(imageElement);
    slideElement.appendChild(captionElement);

    photo.slideElement = slideElement;
    photo.imageElement = imageElement;
    photo.captionElement = captionElement;

    return slideElement;
}

function resetSlideState(photo) {
    if (!photo || !photo.slideElement) {
        return;
    }

    photo.slideElement.classList.remove("is-active", "is-entering", "is-exiting");
    photo.slideElement.classList.add("is-hidden");
}

function refreshPhotoSlide(photo) {
    if (!photo.slideElement) {
        return;
    }

    photo.imageElement.src = photo.url;
    photo.imageElement.alt = photo.caption;
    photo.slideElement.style.removeProperty("--theme-d-rotation");

    buildCaptionContent(photo, photo.captionElement);

    if (state.theme === "d") {
        const randomRotation = (Math.random() * 10) - 5;
        photo.slideElement.style.setProperty("--theme-d-rotation", `${randomRotation}deg`);
    }
}

function clearTransitionTimer() {
    if (state.transitionTimer) {
        window.clearTimeout(state.transitionTimer);
        state.transitionTimer = null;
    }
}

function showPhotoImmediately(photo) {
    ensureSlideshowStructure();

    if (!photo) {
        return;
    }

    if (photo.hideTimer) {
        window.clearTimeout(photo.hideTimer);
        photo.hideTimer = null;
    }

    const slideElement = getOrCreateSlideElement(photo);
    refreshPhotoSlide(photo);

    if (slideElement.parentNode !== slideshowStageElement) {
        slideshowStageElement.appendChild(slideElement);
    }

    if (slideshowEmptyElement.parentNode) {
        slideshowElement.removeChild(slideshowEmptyElement);
    }

    for (let i = 0; i < state.photos.length; i += 1) {
        const currentPhoto = state.photos[i];
        if (!currentPhoto.slideElement) {
            continue;
        }

        if (currentPhoto.id === photo.id) {
            currentPhoto.slideElement.classList.remove("is-hidden", "is-entering", "is-exiting", "is-stacked");
            currentPhoto.slideElement.classList.add("is-active");
            slideshowStageElement.appendChild(currentPhoto.slideElement);
        } else {
            if (state.theme === "d" && state.themeDPrimed) {
                currentPhoto.slideElement.classList.remove("is-hidden", "is-entering", "is-exiting", "is-active");
                currentPhoto.slideElement.classList.add("is-stacked");
                slideshowStageElement.appendChild(currentPhoto.slideElement);
            } else {
                resetSlideState(currentPhoto);
            }
        }
    }

    state.visiblePhotoId = photo.id;
    updateOrderActiveState();
}

function transitionToPhoto(photo) {
    ensureSlideshowStructure();

    const currentPhoto = state.visiblePhotoId ? getPhotoById(state.visiblePhotoId) : null;
    if (!currentPhoto || currentPhoto.id === photo.id) {
        showPhotoImmediately(photo);
        return;
    }

    clearTransitionTimer();

    if (photo.hideTimer) {
        window.clearTimeout(photo.hideTimer);
        photo.hideTimer = null;
    }

    if (currentPhoto.hideTimer) {
        window.clearTimeout(currentPhoto.hideTimer);
        currentPhoto.hideTimer = null;
    }

    const currentSlide = getOrCreateSlideElement(currentPhoto);
    const nextSlide = getOrCreateSlideElement(photo);

    refreshPhotoSlide(photo);

    if (slideshowEmptyElement.parentNode) {
        slideshowElement.removeChild(slideshowEmptyElement);
    }

    if (nextSlide.parentNode !== slideshowStageElement) {
        slideshowStageElement.appendChild(nextSlide);
    } else {
        slideshowStageElement.appendChild(nextSlide);
    }

    if (state.theme === "d") {
        currentSlide.classList.remove("is-active", "is-entering", "is-exiting", "is-hidden");
        currentSlide.classList.add("is-stacked");

        nextSlide.classList.remove("is-hidden", "is-stacked", "is-exiting");
        nextSlide.classList.add("is-entering");

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
                nextSlide.classList.add("is-active");
                nextSlide.classList.remove("is-entering");
            });
        });

        state.visiblePhotoId = photo.id;
        state.themeDPrimed = true;
        updateOrderActiveState();
        return;
    }

    nextSlide.classList.remove("is-hidden", "is-exiting");
    nextSlide.classList.add("is-entering");

    currentSlide.classList.remove("is-entering", "is-hidden");
    currentSlide.classList.add("is-exiting");

    currentSlide.classList.remove("is-active");

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
            nextSlide.classList.add("is-active");
            nextSlide.classList.remove("is-entering");
        });
    });

    if (currentPhoto.hideTimer) {
        window.clearTimeout(currentPhoto.hideTimer);
    }

    state.transitionTimer = window.setTimeout(() => {
        currentSlide.classList.remove("is-exiting");
        currentSlide.classList.add("is-hidden");
        state.transitionTimer = null;
        currentPhoto.hideTimer = null;
    }, 700);

    currentPhoto.hideTimer = state.transitionTimer;

    state.visiblePhotoId = photo.id;
    updateOrderActiveState();
}

function renderActivePhoto() {
    if (!slideshowElement) {
        return;
    }

    ensureSlideshowStructure();

    if (state.photos.length === 0) {
        clearTransitionTimer();
        slideshowStageElement.innerHTML = "";
        if (!slideshowEmptyElement.parentNode) {
            slideshowElement.appendChild(slideshowEmptyElement);
        }
        state.visiblePhotoId = null;
        updateOrderActiveState();
        return;
    }

    if (slideshowEmptyElement.parentNode) {
        slideshowElement.removeChild(slideshowEmptyElement);
    }

    if (state.activeIndex >= state.photos.length) {
        state.activeIndex = state.photos.length - 1;
    }

    const activePhoto = state.photos[state.activeIndex];

    if (activePhoto.slideElement) {
        refreshPhotoSlide(activePhoto);
    }

    if (state.visiblePhotoId === null) {
        showPhotoImmediately(activePhoto);
        return;
    }

    transitionToPhoto(activePhoto);
}

function clearModeTimer() {
    if (state.modeTimer) {
        window.clearInterval(state.modeTimer);
        state.modeTimer = null;
    }
}

function getRandomNextIndex() {
    if (state.photos.length <= 1) {
        return 0;
    }

    let nextIndex = state.activeIndex;
    while (nextIndex === state.activeIndex) {
        nextIndex = Math.floor(Math.random() * state.photos.length);
    }
    return nextIndex;
}

function updateOrderActiveState() {
    if (!imageOrderElement) {
        return;
    }

    const buttons = imageOrderElement.querySelectorAll(".order-item");
    const activePhoto = state.photos[state.activeIndex];

    for (let i = 0; i < buttons.length; i += 1) {
        const button = buttons[i];
        const buttonPhotoId = Number(button.dataset.photoId);
        const isActive = activePhoto && buttonPhotoId === activePhoto.id;
        button.classList.toggle("is-active", Boolean(isActive));
    }
}

function goToNextPhoto() {
    if (state.photos.length === 0) {
        return;
    }

    if (state.mode === "random") {
        state.activeIndex = getRandomNextIndex();
    } else {
        state.activeIndex = (state.activeIndex + 1) % state.photos.length;
    }

    renderActivePhoto();
}

function goToPreviousPhoto() {
    if (state.photos.length === 0) {
        return;
    }

    state.activeIndex = (state.activeIndex - 1 + state.photos.length) % state.photos.length;
    renderActivePhoto();
}

function applyModeTimer() {
    clearModeTimer();

    if (state.mode === "manual") {
        return;
    }

    state.modeTimer = window.setInterval(() => {
        goToNextPhoto();
    }, state.slideIntervalMs);
}

function setMode(mode) {
    state.mode = mode;
    applyModeTimer();
}

function setTheme(theme) {
    state.theme = theme;
    state.themeDPrimed = theme !== "d";
    setSlideshowThemeClass();
    for (let i = 0; i < state.photos.length; i += 1) {
        const photo = state.photos[i];
        if (photo.slideElement) {
            refreshPhotoSlide(photo);
        }
    }

    if (theme === "d") {
        const activePhoto = state.photos[state.activeIndex];
        if (activePhoto) {
            for (let i = 0; i < state.photos.length; i += 1) {
                const photo = state.photos[i];
                if (!photo.slideElement) {
                    continue;
                }

                if (photo.id === activePhoto.id) {
                    photo.slideElement.classList.remove("is-hidden", "is-entering", "is-exiting", "is-stacked");
                    photo.slideElement.classList.add("is-active");
                    slideshowStageElement.appendChild(photo.slideElement);
                } else {
                    resetSlideState(photo);
                }
            }

            state.visiblePhotoId = activePhoto.id;
        }
    }

    renderActivePhoto();
}

function createOrderItem(photo) {
    const item = document.createElement("button");
    item.type = "button";
    item.draggable = true;
    item.dataset.photoId = String(photo.id);
    item.className = "order-item";

    const thumbnail = document.createElement("img");
    thumbnail.src = photo.url;
    thumbnail.alt = photo.caption;
    thumbnail.className = "order-item-image";
    item.appendChild(thumbnail);

    item.addEventListener("dragstart", (event) => {
        state.dragPhotoId = photo.id;
        event.dataTransfer.effectAllowed = "move";
        event.dataTransfer.setData("text/plain", String(photo.id));
    });

    item.addEventListener("dragover", (event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
    });

    item.addEventListener("drop", (event) => {
        event.preventDefault();
        const fromIdText = event.dataTransfer.getData("text/plain");
        const fromId = Number(fromIdText || state.dragPhotoId);
        const toId = Number(item.dataset.photoId);

        if (!fromId || !toId || fromId === toId) {
            return;
        }

        const fromIndex = state.photos.findIndex((entry) => entry.id === fromId);
        const toIndex = state.photos.findIndex((entry) => entry.id === toId);
        if (fromIndex < 0 || toIndex < 0) {
            return;
        }

        const [movedPhoto] = state.photos.splice(fromIndex, 1);
        state.photos.splice(toIndex, 0, movedPhoto);
        state.activeIndex = state.photos.findIndex((entry) => entry.id === movedPhoto.id);

        renderOrderList();
        renderActivePhoto();
    });

    item.addEventListener("dragend", () => {
        state.dragPhotoId = null;
    });

    return item;
}

function renderOrderList() {
    if (!imageOrderElement) {
        return;
    }

    imageOrderElement.innerHTML = "";

    if (state.photos.length === 0) {
        const placeholder = document.createElement("p");
        placeholder.textContent = "No images loaded.";
        placeholder.className = "image-order-empty";
        imageOrderElement.appendChild(placeholder);
        return;
    }

    for (let i = 0; i < state.photos.length; i += 1) {
        const item = createOrderItem(state.photos[i]);
        imageOrderElement.appendChild(item);
    }

    updateOrderActiveState();
}

function addPhotoEntry(entry) {
    if (state.sourceKeys.has(entry.sourceKey)) {
        return;
    }

    state.sourceKeys.add(entry.sourceKey);
    entry.slideElement = null;
    entry.imageElement = null;
    entry.captionElement = null;
    entry.hideTimer = null;
    state.photos.push(entry);

    ensureSlideshowStructure();
    const slideElement = getOrCreateSlideElement(entry);
    refreshPhotoSlide(entry);

    if (slideElement.parentNode !== slideshowStageElement) {
        slideshowStageElement.appendChild(slideElement);
    }

    resetSlideState(entry);
}

function addFiles(files) {
    const fileList = Array.from(files || []);

    for (let i = 0; i < fileList.length; i += 1) {
        const file = fileList[i];
        if (!isImageFile(file)) {
            continue;
        }

        const sourceKey = `file:${file.name}:${file.size}`;
        const entry = {
            id: getNextId(),
            name: file.name,
            caption: formatCaptionFromFileName(file.name),
            url: URL.createObjectURL(file),
            sourceKey
        };

        addPhotoEntry(entry);
    }

    renderOrderList();
    renderActivePhoto();
}

function addSampleImages() {
    for (let i = 0; i < SAMPLE_IMAGE_PATHS.length; i += 1) {
        const path = SAMPLE_IMAGE_PATHS[i];
        const fileName = getNameFromPath(path);

        const entry = {
            id: getNextId(),
            name: fileName,
            caption: formatCaptionFromFileName(fileName),
            url: path,
            sourceKey: `sample:${path}`
        };

        addPhotoEntry(entry);
    }

    renderOrderList();
    renderActivePhoto();
}

function handleDropFiles(event) {
    event.preventDefault();
    addFiles(event.dataTransfer.files);
}

function bindEvents() {
    if (fileInput) {
        fileInput.addEventListener("change", (event) => {
            addFiles(event.target.files);
            fileInput.value = "";
        });
    }

    if (addSampleButton) {
        addSampleButton.addEventListener("click", addSampleImages);
    }

    if (dropLabel) {
        dropLabel.addEventListener("dragover", (event) => {
            event.preventDefault();
        });
        dropLabel.addEventListener("drop", handleDropFiles);
    }

    if (modeManualInput) {
        modeManualInput.addEventListener("change", () => {
            if (modeManualInput.checked) {
                setMode("manual");
            }
        });
    }

    if (modeAutoInput) {
        modeAutoInput.addEventListener("change", () => {
            if (modeAutoInput.checked) {
                setMode("auto");
            }
        });
    }

    if (modeRandomInput) {
        modeRandomInput.addEventListener("change", () => {
            if (modeRandomInput.checked) {
                setMode("random");
            }
        });
    }

    const themeKeys = Object.keys(themeInputs);
    for (let i = 0; i < themeKeys.length; i += 1) {
        const key = themeKeys[i];
        const input = themeInputs[key];
        if (!input) {
            continue;
        }

        input.addEventListener("change", () => {
            if (input.checked) {
                setTheme(key);
            }
        });
    }

    document.addEventListener("keydown", (event) => {
        if (state.mode !== "manual") {
            return;
        }

        if (event.key === "ArrowRight") {
            event.preventDefault();
            goToNextPhoto();
            return;
        }

        if (event.key === "ArrowLeft") {
            event.preventDefault();
            goToPreviousPhoto();
        }
    });
}

function syncInitialControls() {
    if (modeAutoInput && modeAutoInput.checked) {
        state.mode = "auto";
    } else if (modeRandomInput && modeRandomInput.checked) {
        state.mode = "random";
    } else {
        state.mode = "manual";
    }

    const themeKeys = Object.keys(themeInputs);
    for (let i = 0; i < themeKeys.length; i += 1) {
        const key = themeKeys[i];
        const input = themeInputs[key];
        if (input && input.checked) {
            state.theme = key;
            break;
        }
    }
}

bindEvents();
syncInitialControls();
setSlideshowThemeClass();
applyModeTimer();
renderOrderList();
renderActivePhoto();