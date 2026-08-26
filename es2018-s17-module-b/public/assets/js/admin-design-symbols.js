document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('design-dropzone');
    const fileInput = document.getElementById('design-file-input');
    const form = document.getElementById('design-upload-form');

    if (!dropzone || !fileInput || !form) {
        return;
    }

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length > 0) {
            form.submit();
        }
    });

    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropzone.classList.add('dropzone-active');
    });

    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('dropzone-active');
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropzone.classList.remove('dropzone-active');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            form.submit();
        }
    });
});
