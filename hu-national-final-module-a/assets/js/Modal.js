export default class Modal {
    constructor(_element) {
        this.element = _element;
    }

    get isOpened() {
        return !this.element.classList.contains("hidden");
    }

    set isOpened(value) {
        if (value) {
            if(this.element.classList.contains("hidden")) this.element.classList.remove("hidden");
        } else {
            if(!this.element.classList.contains("hidden")) this.element.classList.add("hidden");
        }
    }
}