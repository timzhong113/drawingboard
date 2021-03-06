<!DOCTYPE html>
<html>
<body>
<style>
* {margin: 0; padding: 0;}
body {background: #333333;font-family:sans-serif;margin:0;}
canvas {display: block;}
#mask{
  position:absolute;
  background:#222222;
  width:100%;
  height:100%;
  top:0;
  left:0;
  opacity:1
  -webkit-transition:top 300ms, opacity 300ms;
  -moz-transition:top 300ms, opacity 300ms;
  -ms-transition:top 300ms, opacity 300ms;
  -o-transition:top 300ms, opacity 300ms;
  transition:top 300ms, opacity 300ms;
}
#mask h1{
  font-size:50px;
  font-weight:100;
  letter-spacing:2px;
  margin-bottom: 30px;
}
#mask h2{
  margin-bottom: 20px;
}
#instructions{
  font-size:20px;
  font-weight:300;
}
#instructions li{
  padding-top:10px;
  letter-spacing:1px;
}
#mask_inner{
  position:absolute;
  width:100%;
  color:#fdfdfd;
  top:50%;
  -webkit-transform:translateY(-50%);
  -moz-transform:translateY(-50%);
  -o-transform:translateY(-50%);
  -ms-transform:translateY(-50%);
  transform:translateY(-50%);
  text-align:center;
}
#boardid{
  color:#00ffcc;
  font-family:serif;
  text-transform:uppercase;
  font-size:24px;
}
#alphalabel{display:none;}
</style>
<div id="mask">
  <div id="mask_inner">
    <h1>Welcome to the Drawingboard!!</h1>
    <h2>Please Follow the instructions below</h2>
    <ul id="instructions">
      <li>1. Open this page with your computer</li>
      <li>2. Use your phone to visit <span style="font-style:italic; color:#ffcc00">timzhong.com/drawingboard/pen</span></li>
      <li>3. Enter <span id="boardid"></span> as the board ID and press "Start"</li>
    </ul>
    <div id="boardid"></div>
  </div>
</div>
<canvas id="canvas"></canvas>

<script src="wss/wss.js"></script>
<script>

// var originalleft = window.innerWidth/2;
// var originaltop = window.innerHeight/2;
// document.getElementById('canvas').onclick = resetCanvas;

// function clearCanvas()
// {
//   document.getElementById('canvas').clearRect(0, 0, canvas.width, canvas.height);
// }
// function resetCanvas()
// {
//   clearCanvas();
// }
var alpharounds = 0;
var lastalpha = -1;

var myx=0;
var myy=0;

var originalleft =500;
var originaltop = 500;

var originalalpha = 0;
var originalbeta = 0;

var speed = 6;

var currentleft = 600;
var currenttop = 600;

var socket = null;
var url = "ws://ec2-52-37-132-185.us-west-2.compute.amazonaws.com:9697";
socket = wssconnect(socket,url,'board');
var ctx,canvas;

//Drawing function
(function() {
  'use strict';
  // Declare us some global vars
  var width, height, mouseParticles, followingParticles, mouse, numParticles, colors;

  // Generic Particle constructor
  function Particle(x, y, radius, color) {
    this.x = x;
    this.y = y;
    this.radius = radius;
    this.color = color;
    this.speed = 0.2 + Math.random() * 0.02;
    this.offset = -25 + Math.random() * 50;
    this.angle = Math.random() * 360;
    this.targetX = null;
    this.targetY = null;
    this.vx = null;
    this.vy = null;
    this.compositeOperation = 'source-over';
  }

  Particle.prototype = {
    constructor: Particle,
    draw: function(ctx) {
      ctx.save();
      
      ctx.fillStyle = getColor();
      ctx.translate(this.x, this.y);
      ctx.beginPath();
      ctx.arc(0, 0, this.radius, 0, Math.PI * 2, true);
      ctx.closePath();
      ctx.fill();
      ctx.restore();
    }
  }

  init(); // Start the program
  function init() {
    // Assign global vars accordingly
    canvas = document.querySelector('canvas');
    ctx = canvas.getContext('2d');
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
    // Get mouse positions
    mouse = getMousePos(canvas);
    // Two arrays to hold our rotating and 'following' particles
    mouseParticles = [];
    followingParticles = [];
    numParticles = 1;
    colors = ['#e74509', '#243d89', '#ffe500'];

    // Generate particles to rotate our mouse
    generateParticles(mouseParticles, numParticles, 0, 0);

    // Generate particles, which follow the mouse particles
    generateParticles(followingParticles, numParticles, Math.random() * width, Math.random() * height);

    drawFrame();

  }

  // Generic function for generating particles
  function generateParticles(particlesArray, count, x, y) {
    var i, particle;
    for (i = 0; i < count; i++) {
      if (particlesArray === followingParticles) {
        particle = new Particle(x, y, 10, colors[i]);
      } else {
        particle = new Particle(x, y, 10,colors[i]);
      }
      particlesArray.push(particle);
    }
  }

  function drawFrame() {
    // Update & Redraw the entire screen on each frame
    window.requestAnimationFrame(drawFrame, canvas);
    ctx.fillStyle = 'rgba(23, 41, 58, 0.0)';
    ctx.fillRect(0, 0, width, height);
    mouseParticles.forEach(rotateParticle);
    followingParticles.forEach(updateParticle)
  }

  // Update each of our following particles to follow the corresponding rotating one
  function updateParticle(particle, index) {
    var rotParticle, speed, gravity,
        dx, dy, dist;

    rotParticle = mouseParticles[index];
    speed = 0.1;
    gravity = 0.8;


    particle.targetX = rotParticle.x;
    particle.targetY = rotParticle.y;

    dx = particle.targetX - particle.x;
    dy = particle.targetY - particle.y;
    dist = Math.sqrt(dx * dx + dy * dy);

    if (dist < 50) {
      particle.targetX = rotParticle.x;
      particle.targetY = rotParticle.y;
    } else {
      particle.targetX = mouseParticles[Math.round(index / 2)];
      particle.targetX = mouseParticles[Math.round(index / 2)];
    }

    particle.vx += dx * speed;
    particle.vy += dy * speed;
    particle.vx *= gravity;
    particle.vy *= gravity;
    particle.x += particle.vx;
    particle.y += particle.vy;

    particle.draw(ctx);
  }

  // Rotate our particles around the mouse one by one
  function rotateParticle(particle)  {
    var vr, radius, centerX, centerY;

    vr = 0.1;
    radius = 0;
    centerX = mouse.x;
    centerY = mouse.y;

    // Rotate the particles
    particle.x = centerX + particle.offset + Math.cos(particle.angle) * radius;
    particle.y = centerY + particle.offset + Math.sin(particle.angle) * radius;
    particle.angle += particle.speed;


    // Reposition a particle if it goes out of screen
    if (particle.x - particle.radius / 2 <= -radius / 2) {
      particle.x = 5;
    } else if (particle.x + particle.radius / 2 >= width - radius / 2) {
      particle.x = width - 5;
    } else if (particle.y - particle.radius / 2 <= -radius / 2) {
      particle.y = 5;
    } else if (particle.y + particle.radius / 2 >= height - radius / 2) {
      particle.y = height - 5;
    }

    //particle.draw(ctx);
  }

  // Util function for getting the mouse coordinates
  function getMousePos(element) {
    var mouse = {x: width / 2, y: height / 2};
    setInterval(function(){
      mouse.x = myx;
      mouse.y = myy;
    }, 100);
    return mouse;
  }

}());



var optellen = true;
var letter = 'B';
var colorR = 255;
var colorG = 0;
var colorB = 0;

function getColor()
{
  var colorTransformSpeed = 5;
  
  if(letter == 'R')
  {
    if(optellen) {
      colorR += colorTransformSpeed;
      if(colorR == 255) {
        letter = 'G';
        optellen = false;
      }
    } else {
      colorR -= colorTransformSpeed;
      if(colorR == 0) {
        letter = 'G';
        optellen = true;
      }
    }
  }
  else if(letter == 'G')
  {
    if(optellen) {
      colorG += colorTransformSpeed;
      if(colorG == 255) {
        letter = 'B';
        optellen = false;
      }
    } else {
      colorG -= colorTransformSpeed;
      if(colorG == 0) {
        letter = 'B';
        optellen = true;
      }
    }
  }
  else
  {
    if(optellen) {
      colorB += colorTransformSpeed;
      if(colorB == 255) {
        letter = 'R';
        optellen = false;
      }
    } else {
      colorB -= colorTransformSpeed;
      if(colorB == 0) {
        letter = 'R';
        optellen = true;
      }
    }
  }
  
  return 'rgb(' + colorR + ', ' + colorG + ', ' + colorB + ')';
}

function clearcanvas(){
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}
</script>

<script>//google
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-78864604-1', 'auto');
  ga('send', 'pageview');

</script>

</body>
</html>

<!-- <!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<style>
span{font-size:40px;}

#test_square{display: block; position: absolute; top:100px; left:100px; background: #dedede; width:50px; height:50px;}
</style>
<span id="boardid"></span><br/>
<span id="ax"></span><br/>
<span id="ay"></span><br/>
<span id="az"></span><br/>
<span id="arAlpha"></span><br/>
<span id="arBeta"></span><br/>
<span id="arGamma"></span><br/>
<span id="alpha"></span><br/>
<span id="beta"></span><br/>
<span id="gamma"></span><br/>

<div id="test_square">
</div>

<script src="wss/wss.js"></script>
<script>
var originalleft = window.innerWidth/2;
var originaltop = window.innerHeight/2;

var originalalpha = 0;
var originalbeta = 0;

var speed = 6;

var currentleft = 600;
var currenttop = 600;

var socket = null;
var url = "ws://ec2-52-37-132-185.us-west-2.compute.amazonaws.com:9697";
socket = wssconnect(socket,url,'board');

</script>	
</body>
</html> -->
