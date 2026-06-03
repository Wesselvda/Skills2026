document.addEventListener("DOMContentLoaded", () => {
    let ul = document.querySelector("body > header:nth-child(1) > div:nth-child(1) > nav:nth-child(2) > ul:nth-child(1)");
    let li = document.createElement("li");
    let switcher = document.createElement("button");
    updateSwitcher()
    li.appendChild(switcher);
    ul.appendChild(li)

    
    function isDark() {
        return window.getComputedStyle(document.body).backgroundColor === "rgb(18, 18, 18)";
    }

    function updateSwitcher() {
        if (isDark()) {
            switcher.innerText = "☀️"
        } else {
            switcher.innerText = "🌑"
        }
    }

    switcher.addEventListener("click", () => {
        if (isDark()) {
            if (document.body.classList.contains("dark")) document.body.classList.remove("dark");
            document.body.classList.add("light")
        } else {
            if (document.body.classList.contains("light")) document.body.classList.remove("light");
            document.body.classList.add("dark")
        }
        updateSwitcher()
    })
});