let designs = [];
let products = [];

let selectedDesign = null;
let selectedProduct = null;

document.addEventListener('DOMContentLoaded', function () {
    fetchCustomizerOptions();
});

function fetchCustomizerOptions() {
    fetch('/api/customizer-options')
        .then(response => response.json())
        .then(data => {
            designs = data.designs;
            products = data.products;
            renderDesigns();
            renderProducts();
        });
}

function renderDesigns() {
    const container = document.getElementById('design-symbols');
    container.replaceChildren(...designs.map(design => buildOptionInput({
        name: 'design',
        value: design.id,
        labelClass: 'design-symbol',
        imageSrc: `/storage/design_symbols/${design.image_filename}`,
        imageAlt: design.name,
        onChange: () => onDesignSelected(design),
    })));
}

function renderProducts() {
    const container = document.getElementById('products');
    container.replaceChildren(...products.map(product => {
        const firstColor = product.product_colors[0];
        return buildOptionInput({
            name: 'product',
            value: product.id,
            labelClass: 'product',
            imageSrc: firstColor ? `/storage/product_images/${firstColor.image_filename}` : '',
            imageAlt: product.name,
            imageClass: 'product-image',
            onChange: () => onProductSelected(product),
        });
    }));
}

function renderColors(product) {
    const container = document.getElementById('color-options');
    container.replaceChildren(...product.product_colors.map(color => buildOptionInput({
        name: 'color',
        value: color.id,
        labelClass: 'color-option',
        imageSrc: `/storage/product_images/${color.image_filename}`,
        imageAlt: color.name,
        onChange: () => onColorSelected(color),
    })));

    if (product.product_colors.length > 0) {
        const firstInput = container.querySelector('input[type="radio"]');
        firstInput.checked = true;
        onColorSelected(product.product_colors[0]);
    }
}

function buildOptionInput({ name, value, labelClass, imageSrc, imageAlt, imageClass, onChange }) {
    const fragment = document.createDocumentFragment();
    const id = `${name}-${value}`;

    const input = document.createElement('input');
    input.type = 'radio';
    input.name = name;
    input.id = id;
    input.value = value;
    input.className = 'hidden';
    input.addEventListener('change', onChange);

    const label = document.createElement('label');
    label.setAttribute('for', id);
    label.className = labelClass;

    const img = document.createElement('img');
    img.src = imageSrc;
    img.alt = imageAlt;
    if (imageClass) {
        img.className = imageClass;
    }

    label.appendChild(img);
    fragment.appendChild(input);
    fragment.appendChild(label);

    return fragment;
}

function onDesignSelected(design) {
    selectedDesign = design;
    updatePreview();
}

function onProductSelected(product) {
    selectedProduct = product;
    renderColors(product);
    updatePreview();
}

function onColorSelected(color) {
    const productImage = document.getElementById('preview-product-image');
    productImage.src = `/storage/product_images/${color.image_filename}`;
    productImage.alt = `${selectedProduct.name} - ${color.name}`;
}

function updatePreview() {
    const designImage = document.getElementById('preview-design-image');

    if (selectedDesign) {
        designImage.src = `/storage/design_symbols/${selectedDesign.image_filename}`;
        designImage.alt = selectedDesign.name;
    }
}
