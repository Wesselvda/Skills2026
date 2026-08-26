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

        if (item.link === '/cart') {
            const badge = document.createElement('span');
            badge.className = 'cart-badge hidden';
            a.appendChild(badge);
            fetchCartCount(badge);
        }

        return li;
    }));
}

function fetchCartCount(badge) {
    fetch('/api/cart-count').then(response => response.json()).then(data => {
        if (data.count > 0) {
            badge.textContent = data.count;
            badge.classList.remove('hidden');
        }
    });
}

fetchNavigationItems();
