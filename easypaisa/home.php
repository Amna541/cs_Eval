<?php
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
echo "<title>Cyber Security Awareness - Army</title>";
echo "<link rel='stylesheet' href='/w3css/3/w3.css'>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>";
echo "<style> 
        body { 
            background: #3e4a3e; /* Darker tone for military style */
            color: #ecf0f1; /* Lighter text for contrast */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
            text-align: center; 
            flex-direction: column; 
        }
        .mySlides { display: none; } /* Hide images initially */
        .w3-bar { background-color: #34495e; } /* Darker bar for military feel */
        .w3-button { color: #ecf0f1; } /* Light color for buttons */
        .w3-button:hover { background-color: #16a085; } /* Green hover for military look */
        h1, h2 { font-family: 'Arial', sans-serif; font-weight: bold; }
        p { font-family: 'Arial', sans-serif; }
      </style>";
echo "</head>";
echo "<body>";

/* Navigation */
echo "<nav class='w3-bar w3-black'>";
echo "<a href='#home' class='w3-button w3-bar-item'>Home</a>";
echo "<a href='#band' class='w3-button w3-bar-item'>Our Forces</a>";
echo "<a href='#tour' class='w3-button w3-bar-item'>Mission</a>";
echo "<a href='#contact' class='w3-button w3-bar-item'>Contact</a>";
echo "</nav>";

/* Slide Show */
echo "<section>";
echo "<img class='mySlides' src='img_soldier1.jpg' style='width:100%'>";
echo "<img class='mySlides' src='img_soldier2.jpg' style='width:100%'>";
echo "<img class='mySlides' src='img_soldier3.jpg' style='width:100%'>";
echo "</section>";

/* Army Description */
echo "<section class='w3-container w3-center w3-content' style='max-width:600px'>";
echo "<h2 class='w3-wide'>OUR MISSION</h2>";
echo "<p class='w3-opacity'><i>Dedicated to defending and securing our nation</i></p>";
echo "<p class='w3-justify'>We are a force of dedicated personnel committed to maintaining national security and operational integrity. Through cyber security and military intelligence, we safeguard the digital and physical borders of our country. Our operations are precise, confidential, and of utmost importance to the safety of all citizens.</p>";
echo "</section>";

/* Army Units */
echo "<section class='w3-row-padding w3-center w3-light-grey'>";
echo "<article class='w3-third'>";
echo "<p>Infantry</p>";
echo "<img src='img_infantry.jpg' alt='Infantry Unit' style='width:100%'>";
echo "<p>The backbone of our operations.</p>";
echo "</article>";
echo "<article class='w3-third'>";
echo "<p>Special Forces</p>";
echo "<img src='img_specialforces.jpg' alt='Special Forces' style='width:100%'>";
echo "<p>Elite units for high-stakes missions.</p>";
echo "</article>";
echo "<article class='w3-third'>";
echo "<p>Cyber Defense</p>";
echo "<img src='img_cyberdefense.jpg' alt='Cyber Defense' style='width:100%'>";
echo "<p>Securing the digital frontlines.</p>";
echo "</article>";
echo "</section>";

/* Footer */
echo "<footer class='w3-container w3-padding-64 w3-center w3-black w3-xlarge'>";
echo "<a href='#'><i class='fa fa-facebook-official'></i></a>";
echo "<a href='#'><i class='fa fa-pinterest-p'></i></a>";
echo "<a href='#'><i class='fa fa-twitter'></i></a>";
echo "<a href='#'><i class='fa fa-flickr'></i></a>";
echo "<a href='#'><i class='fa fa-linkedin'></i></a>";
echo "<p class='w3-medium'>";
echo "Powered by the Army Cyber Security Division";
echo "</p>";
echo "</footer>";

/* Slideshow JavaScript */
echo "<script>";
echo "var myIndex = 0;";
echo "carousel();";

echo "function carousel() {";
echo "  var i;";
echo "  var x = document.getElementsByClassName('mySlides');";
echo "  for (i = 0; i < x.length; i++) {";
echo "    x[i].style.display = 'none';";
echo "  }";
echo "  myIndex++;";
echo "  if (myIndex > x.length) {myIndex = 1}";
echo "  x[myIndex-1].style.display = 'block';";
echo "  setTimeout(carousel, 3000);";
echo "}";
echo "</script>";

echo "</body>";
echo "</html>";
?>
