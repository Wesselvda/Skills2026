var isDocumentLoaded = false;
var items = null;

document.addEventListener('DOMContentLoaded', function() {
    isDocumentLoaded = true;
    if (items) {
        populateNavigationItems(items);
    }
});

function fetchNavigationItems() {
    fetch('/api/navigation-items').then(response => response.json()).then(data => {
        items = data;
        if (isDocumentLoaded) {
            populateNavigationItems(items);
        }
    });
}

function populateNavigationItems(data) {
    const navigationItems = document.getElementById('navigation-items');
    navigationItems.replaceChildren(...data.map(item => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = item.link;
        a.textContent = item.name;
        li.appendChild(a);

        return li;
    }));
}

fetchNavigationItems();