var easingFunctions;
const checkboxWrapper = document.getElementById("checkboxes");
const rangeSlider = document.getElementById("slider");
const animateButton = document.getElementById("animate-button");
const canvas = document.getElementById("canvas");
const rangeMax = 3000;
var currentRange = 1500;
var isAnimating = false;

function loadEasingJSON() {
    const URL = "/assets//easing-functions.json"

    fetch(URL).then((res) => {
        return res.json()
    }).then((data) => {
        parseEasingFunctions(data)
        rangeSlider.addEventListener("input", (e) => {onSliderChange(e)})
        animateButton.addEventListener("click", startAnimation);
    })
}

function parseEasingFunctions(data) {
    easingFunctions = data.easingFunctions;

    Object.values(easingFunctions).forEach(easingFunction => {
        easingFunction.active = false;
        createCheckbox(easingFunction);
        renderCanvas();
    });
}

function createCheckbox(easingFunction) {
    let label = document.createElement("label");
    label.className = "easingfunction-label"
    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.name = easingFunction.text;
    let text = document.createTextNode(easingFunction.text);
    let formula = document.createElement("div");
    formula.className = "formula-hover"
    formula.innerText = easingFunction.formula;
    label.replaceChildren(checkbox, text, formula);
    checkboxWrapper.appendChild(label);
    checkbox.addEventListener("change", (e) => {onCheckboxChanged(easingFunction, e.target.checked)});
}

function onCheckboxChanged(easingFunction, checked) {
    easingFunctions[easingFunction.text].active = checked;
    renderCanvas();
}

function onSliderChange(e) {
    currentRange = e.target.value * 30;
    renderCanvas();
}

function startAnimation() {
    if (!isAnimating) {
        isAnimating = true;
        rangeSlider.disabled = true;

        var animationStartTime;
        var rangeStart = currentRange;

        function nextFrame(time) {
            if (animationStartTime) {
                let nextValue = rangeStart + (time - animationStartTime)

                if (nextValue >= rangeMax) {
                    currentRange = rangeMax;
                } else {
                    currentRange = nextValue;
                }

                rangeSlider.value = Math.round(nextValue / 30)
                renderCanvas();
            } else {
                animationStartTime = time;
            }

            if (currentRange < rangeMax) {
                window.requestAnimationFrame(nextFrame);
            } else {
                isAnimating = false;
                rangeSlider.disabled = false;
            }
        }

        window.requestAnimationFrame(nextFrame)
    }
}

function renderCanvas() {
    // 1000 x 1000
    const ctx = canvas.getContext("2d");
    ctx.fillStyle = "#fff";
    ctx.fillRect(0, 0, 1200, 1200);

    ctx.beginPath();
    ctx.strokeStyle = "#000";
    ctx.lineWidth = 10;
    ctx.moveTo(100, 100);
    ctx.lineTo(100, 1100);
    ctx.lineTo(1100, 1100);
    ctx.stroke();
    ctx.fillStyle = "#000";
    ctx.font = "64px bold sans-serif";
    ctx.fillText("100%", 0, 80);
    ctx.fillText("3s", 1100, 1150);
    ctx.fillText("0", 50, 1150);



    Object.values(easingFunctions).forEach(easingFunction => {
        if (easingFunction.active) {
            ctx.beginPath();
            ctx.lineWidth = 5;
            ctx.fillStyle = "#fff";
            ctx.strokeStyle = "#000";
            ctx.moveTo(100, 1100);
            
            for (let i = 0; i < currentRange / 3; i+=5) {
                let t = i / 1000;
                let formulaY = eval(`t=${t};${easingFunction.equation}`);

                ctx.lineTo(i + 100, 1100 - (formulaY * 1000));
            }

            ctx.stroke();

            let t = currentRange / rangeMax;
            let formulaY = eval(`t=${t};${easingFunction.equation}`);
            let circleX = currentRange / 3 + 95;
            let circleY = 1100 - (formulaY * 1000) + 5;

            ctx.strokeStyle = "#00f";
            ctx.beginPath();
            ctx.arc(circleX, circleY, 40, 0, 2 * Math.PI)
            ctx.fill();
            ctx.stroke();
            ctx.fillStyle = "#000";
            ctx.font = "bold 32px sans-serif";
            ctx.fillText(`${Math.round(formulaY * 100)}%`, circleX - 30, circleY + 10);
        }
    })

    // ctx.beginPath();
    // ctx.lineWidth = 5;
    // ctx.strokeStyle = "#f00"
    // ctx.moveTo(75, 50);
    // ctx.lineTo(100, 75);
    // ctx.lineTo(100, 25);
    // ctx.stroke();
}

loadEasingJSON();

