document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('main-nav-list');
    if (!list) {
        return;
    }

    let draggedItem = null;

    list.addEventListener('dragstart', function (event) {
        draggedItem = event.target.closest('.sortable-item');
    });

    list.addEventListener('dragover', function (event) {
        event.preventDefault();
    });

    list.addEventListener('drop', function (event) {
        event.preventDefault();

        const target = event.target.closest('.sortable-item');
        if (!draggedItem || !target || target === draggedItem) {
            return;
        }

        const items = [...list.children];
        const draggedIsBeforeTarget = items.indexOf(draggedItem) < items.indexOf(target);

        target.insertAdjacentElement(draggedIsBeforeTarget ? 'afterend' : 'beforebegin', draggedItem);
        draggedItem = null;
    });
});
