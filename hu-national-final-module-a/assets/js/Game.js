import { Skills } from "./Skills.js";
import Modal from "./Modal.js";

// Modals
const modalStartScreen = new Modal(document.getElementById("modal-start-screen"));
const modalSpaghettiCode = new Modal(document.getElementById("modal-spaghetti-code"));
const modalBurnout = new Modal(document.getElementById("modal-burnout"));
const modalPaused = new Modal(document.getElementById("modal-paused"));
const modals = [modalStartScreen, modalSpaghettiCode, modalBurnout, modalPaused];

// Start buttons
const startButtons = new Array(...document.getElementsByClassName("start-game-button"));
const resumeButton = document.getElementById("resume-game-button");

// HUD
const certificatesValueElement = document.getElementById("certificates-earned");
const levelValueElement = document.getElementById("developer-level");
const bestRunValueElement = document.getElementById("best-run");
const footerTextElement = document.getElementById("footer-text");
const burnoutCertificatesElement = document.getElementById("modal-burnout-certificate-count");
const burnoutLevelElement = document.getElementById("modal-burnout-level-count");
const spaghettiCertificatesElement = document.getElementById("modal-spaghetti-certificate-count");
const spaghettiLevelElement = document.getElementById("modal-spaghetti-level-count");

// Generic
const gameGridElement = document.getElementById("game-grid");
const gameWrapperElement = document.querySelector(".game-inner-wrapper");
const gridSize = 20;
const basespeed = 200;
const minimumSpeed = 80;
const speedStepPerSkill = 6;
const bestRunStorageKey = "ssa-best-run";
var gridCells = [];
var snakeSegments = [];
var snakeX = 10;
var snakeY = 10;
var snakeDirection = "right";
var nextDirection = "right";
var gameActive = false;
var gamePaused = false;
var lastTime = 0;
var speed = 500;
var certificates = 0;
var bestRun = 0;
var currentSkill = null;
var runId = 0;

startButtons.forEach(button => {
    if (button === resumeButton) {
        button.addEventListener("click", () => setPaused(false));
        return;
    }

    button.addEventListener("click", startGame);
});

function startGame() {
    modals.forEach(modal => {
        modal.isOpened = false;
    });

    gameWrapperElement.classList.remove("is-burnout");

    currentSkill = null;

    generateGrid();
    createSnake();
    certificates = 0;
    updateHud();
    spawnSkill();

    gameActive = true;
    gamePaused = false;
    lastTime = 0;
    runId++;
    updateSpeed(basespeed);
    setFooterText("Collect skills. Space to pause.");
    startLoop();
};

function startLoop() {
    let loopId = runId;

    window.requestAnimationFrame(function loop(time) {
        if (loopId !== runId || !gameActive || gamePaused) {
            return;
        }

        gameLoop(time);
        window.requestAnimationFrame(loop);
    });
}

class GridCell {
    constructor(_x, _y) {
        this.x = _x;
        this.y = _y;

        this.createElement();
    }

    createElement() {
        let element = document.createElement("div");
        element.className = "grid-cell"

        this.element = element;
    }
}

function generateGrid() {
    gridCells = [];
    let elementList = [];

    for (let y = 0; y < gridSize; y++) {
        let row = [];

        for (let x = 0; x < gridSize; x++) {
            let cell = new GridCell(x, y);

            row.push(cell);
            elementList.push(cell.element);
        }

        gridCells.push(row);
    }


    gameGridElement.replaceChildren(...elementList);
}

class SnakeSegment {
    constructor(_x, _y) {
        this.createElement()
        this.x = _x;
        this.y = _y;
        snakeSegments.push(this);
    }

    createElement() {
        this.element = document.createElement("div");
        this.element.className = "snake-segment";

        gameGridElement.append(this.element);
    }

    destroy() {
        this.element.remove();
    }

    get x() {
        return parseFloat(this.element.style.getPropertyValue("--x"));
    }

    set x(value) {
        this.element.style.setProperty("--x", value);
    }

    get y() {
        return parseFloat(this.element.style.getPropertyValue("--y"));
    }

    set y(value) {
        this.element.style.setProperty("--y", value);
    }
}

function createSnake() {
    snakeSegments.forEach(segment => segment.destroy());
    snakeSegments = [];
    snakeX = 10;
    snakeY = 10;
    snakeDirection = "right";
    nextDirection = "right";

    new SnakeSegment(snakeX, snakeY);
    new SnakeSegment(snakeX - 1, snakeY);
    new SnakeSegment(snakeX - 2, snakeY);

    snakeSegments[0].element.classList.add("snake-head");
}

function updateSpeed(newSpeed) {
    speed = newSpeed;
    gameGridElement.style.setProperty("--speed", speed);
}

class SkillItem {
    constructor(_x, _y, _skill) {
        this.skill = _skill;
        this.createElement();
        this.x = _x;
        this.y = _y;
    }

    createElement() {
        this.element = document.createElement("div");
        this.element.className = "skill-item";
        this.element.title = this.skill.label;

        let icon = document.createElement("img");
        icon.src = `assets/skill-icons/${this.skill.id}.svg`;
        icon.alt = this.skill.label;

        this.element.append(icon);
        gameGridElement.append(this.element);
    }

    destroy() {
        this.element.remove();
    }

    set x(value) {
        this._x = value;
        this.element.style.setProperty("--x", value);
    }

    get x() {
        return this._x;
    }

    set y(value) {
        this._y = value;
        this.element.style.setProperty("--y", value);
    }

    get y() {
        return this._y;
    }
}

function isOccupiedBySnake(x, y) {
    return snakeSegments.some(segment => segment.x === x && segment.y === y);
}

function spawnSkill() {
    if (currentSkill) {
        currentSkill.destroy();
        currentSkill = null;
    }

    let freeCells = [];

    for (let y = 0; y < gridSize; y++) {
        for (let x = 0; x < gridSize; x++) {
            if (!isOccupiedBySnake(x, y)) {
                freeCells.push({ x, y });
            }
        }
    }

    if (freeCells.length === 0) {
        return;
    }

    let cell = freeCells[Math.floor(Math.random() * freeCells.length)];
    let skill = Skills[Math.floor(Math.random() * Skills.length)];

    currentSkill = new SkillItem(cell.x, cell.y, skill);
}

function getLevel() {
    return 1 + Math.floor(certificates / 2);
}

function loadBestRun() {
    let stored = parseInt(window.localStorage.getItem(bestRunStorageKey), 10);
    bestRun = Number.isNaN(stored) ? 0 : stored;
}

function saveBestRun() {
    if (certificates > bestRun) {
        bestRun = certificates;
        window.localStorage.setItem(bestRunStorageKey, bestRun);
    }
}

function bumpValue(element) {
    element.classList.remove("value-bump");
    void element.offsetWidth;
    element.classList.add("value-bump");
}

function updateHud(animate = false) {
    certificatesValueElement.textContent = certificates;
    levelValueElement.textContent = getLevel();
    bestRunValueElement.textContent = bestRun;

    if (animate) {
        bumpValue(certificatesValueElement);
        bumpValue(levelValueElement);
    }
}

function setFooterText(text) {
    footerTextElement.textContent = text;
}

function collectSkill() {
    certificates++;
    saveBestRun();
    updateHud(true);

    let tail = snakeSegments[snakeSegments.length - 1];
    new SnakeSegment(tail.x, tail.y);

    updateSpeed(Math.max(minimumSpeed, basespeed - certificates * speedStepPerSkill));
    spawnSkill();
}

function endGame(reason) {
    gameActive = false;
    gamePaused = false;
    saveBestRun();
    updateHud();

    if (reason === "burnout") {
        burnoutCertificatesElement.textContent = certificates;
        burnoutLevelElement.textContent = getLevel();
        modalBurnout.isOpened = true;
        gameWrapperElement.classList.add("is-burnout");
        setFooterText("Run finished - Start a new path from the dialog");
    } else {
        spaghettiCertificatesElement.textContent = certificates;
        spaghettiLevelElement.textContent = getLevel();
        modalSpaghettiCode.isOpened = true;
        setFooterText("Run finished - Start a new path from the dialog");
    }
}

function setPaused(value) {
    if (!gameActive || gamePaused === value) {
        return;
    }

    gamePaused = value;
    modalPaused.isOpened = value;

    if (value) {
        setFooterText("Paused. press Space to resume.");
        return;
    }

    lastTime = 0;
    setFooterText("Collect skills. Space to pause.");
    startLoop();
}

const opposites = {
    up: "down",
    down: "up",
    left: "right",
    right: "left"
};

const keyDirections = {
    w: "up",
    s: "down",
    a: "left",
    d: "right",
    arrowup: "up",
    arrowdown: "down",
    arrowleft: "left",
    arrowright: "right"
};

document.addEventListener("keydown", (event) => {
    let key = event.key.toLowerCase();

    if (key === " " || key === "spacebar") {
        event.preventDefault();
        setPaused(!gamePaused);
        return;
    }

    let direction = keyDirections[key];

    if (!direction || gamePaused) {
        return;
    }

    event.preventDefault();

    if (direction !== opposites[snakeDirection]) {
        nextDirection = direction;
    }
});

function gameLoop(time) {
    if (lastTime === 0) {
        lastTime = time;
    }

    if (time - lastTime > speed) {
        lastTime = time;

        snakeDirection = nextDirection;

        let headX = snakeX;
        let headY = snakeY;

        switch (snakeDirection) {
            case "up":
                headY--;
                break;
            case "down":
                headY++;
                break;
            case "left":
                headX--;
                break;
            case "right":
                headX++;
                break;
        }

        if (headX < 0 || headY < 0 || headX >= gridSize || headY >= gridSize) {
            endGame("burnout");
            return;
        }

        let bodyHit = snakeSegments
            .slice(0, snakeSegments.length - 1)
            .some(segment => segment.x === headX && segment.y === headY);

        if (bodyHit) {
            endGame("spaghetti");
            return;
        }

        snakeX = headX;
        snakeY = headY;

        for (let i = snakeSegments.length - 1; i > 0; i--) {
            snakeSegments[i].x = snakeSegments[i - 1].x;
            snakeSegments[i].y = snakeSegments[i - 1].y;
        }

        snakeSegments[0].x = snakeX;
        snakeSegments[0].y = snakeY;

        if (currentSkill && currentSkill.x === snakeX && currentSkill.y === snakeY) {
            collectSkill();
        }
    }
}

loadBestRun();
updateHud();
