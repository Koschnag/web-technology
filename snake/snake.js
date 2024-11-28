const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const startButton = document.getElementById('startButton');
const leftButton = document.getElementById('leftButton');
const rightButton = document.getElementById('rightButton');
const scoreDisplay = document.getElementById('score');
const gameOverDisplay = document.getElementById('gameOver');

const gridSize = 20;
const canvasSize = 400;
const initialSnakeLength = 5;
const directions = {
    UP: { x: 0, y: -1 },
    DOWN: { x: 0, y: 1 },
    LEFT: { x: -1, y: 0 },
    RIGHT: { x: 1, y: 0 }
};

let snake = [];
let direction = directions.UP;
let gameInterval;
let score = 0;
let steps = 0;
let speed = 1000;

function initGame() {
    snake = [];
    for (let i = 0; i < initialSnakeLength; i++) {
        snake.push({ x: canvasSize / 2 / gridSize, y: (canvasSize / 2 / gridSize) + i });
    }
    direction = directions.UP;
    score = 0;
    steps = 0;
    speed = 1000;
    scoreDisplay.textContent = `Punkte: ${score}`;
    gameOverDisplay.style.display = 'none';
    clearInterval(gameInterval);
    gameInterval = setInterval(gameLoop, speed);
}

function gameLoop() {
    moveSnake();
    if (checkCollision()) {
        endGame();
    } else {
        updateCanvas();
        score++;
        scoreDisplay.textContent = `Punkte: ${score}`;
        adjustSpeed();
    }
}

function moveSnake() {
    const head = { x: snake[0].x + direction.x, y: snake[0].y + direction.y };
    snake.unshift(head);
    steps++;
    if (steps % 10 !== 0) {
        snake.pop();
    }
}

function checkCollision() {
    const head = snake[0];
    if (head.x < 0 || head.x >= canvasSize / gridSize || head.y < 0 || head.y >= canvasSize / gridSize) {
        return true;
    }
    for (let i = 1; i < snake.length; i++) {
        if (snake[i].x === head.x && snake[i].y === head.y) {
            return true;
        }
    }
    return false;
}

function endGame() {
    clearInterval(gameInterval);
    gameOverDisplay.style.display = 'block';
}

function updateCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'green';
    snake.forEach(segment => {
        ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize, gridSize);
    });
}

function turnLeft() {
    if (direction === directions.UP) direction = directions.LEFT;
    else if (direction === directions.LEFT) direction = directions.DOWN;
    else if (direction === directions.DOWN) direction = directions.RIGHT;
    else if (direction === directions.RIGHT) direction = directions.UP;
}

function turnRight() {
    if (direction === directions.UP) direction = directions.RIGHT;
    else if (direction === directions.RIGHT) direction = directions.DOWN;
    else if (direction === directions.DOWN) direction = directions.LEFT;
    else if (direction === directions.LEFT) direction = directions.UP;
}

function keyEvent(event) {
    console.log("Key: " + event.keyCode);
    if (event.keyCode == 39) { // Right arrow key
        turnRight();
    }
    if (event.keyCode == 37) { // Left arrow key
        turnLeft();
    }
}

function adjustSpeed() {
    if (score === 10 || score === 20 || score === 30) {
        speed -= 200;
        clearInterval(gameInterval);
        gameInterval = setInterval(gameLoop, speed);
    }
}

startButton.addEventListener('click', initGame);
leftButton.addEventListener('click', turnLeft);
rightButton.addEventListener('click', turnRight);
document.addEventListener('keydown', keyEvent);