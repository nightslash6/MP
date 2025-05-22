<?php
require 'db_connect.php';
// Assuming you have a database connection set up in $conn
$conn = db_connect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersite</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="mstyles.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <svg height="40" width="40" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="#8a2be2" />
                <circle cx="50" cy="50" r="30" fill="#9932CC" />
                <path d="M 30 50 L 70 50" stroke="white" stroke-width="5" />
                <path d="M 50 30 L 50 70" stroke="white" stroke-width="5" />
            </svg>
            <a href="#" class="logo-text">Cybersite</a>
        </div>
        <div class="nav-links">
            <a href="#">Get Started</a>
            <a href="#">Learn</a>
            <a href="quiz.php">Practice</a>
            <a href="#">Compete</a>

            <div class="dropdown">
                <a class="dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    About
                </a>
                <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                    <li><a class="dropdown-item" href="#">Contact Us</a></li>
                    <li><a class="dropdown-item" href="#">About Cybersite</a></li>
                </ul>
            </div>

            <a href="#">Log In</a>
        </div>

    </nav>
    
    <div class="hero">    
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" class="active" aria-current="true" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            
            <div class="carousel-inner">
                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Learn Cybersecurity</h1>
                        <p>Master the fundamentals with our interactive challenges</p>
                        <a href="#" class="read-more-btn">Read More</a>
                    </div>
                </div>
                
                <div class="carousel-item active">
                    <div class="hero-content">
                        <h1>How You Can Write CTF Challenges</h1>
                        <p>by Wei Hong</p>
                        <a href="#" class="read-more-btn">Read More</a>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Join The Competition</h1>
                        <p>Test your skills against others in our global competitions</p>
                        <a href="ctf_info.php" class="read-more-btn">Read More</a>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <h2 class="text-center">Welcome to Cybersite</h2>
                <p class="text-center">Your one-stop destination for learning and practicing cybersecurity skills.</p>
            </div>
        <div class="card-grid">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Learn</h3>
                            <p class="card-text">Access comprehensive resources and tutorials to build your cybersecurity knowledge from the ground up.</p>
                            <a href="#" class="btn btn-primary card-btn">Start Learning</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Practice</h3>
                            <p class="card-text">Apply your skills with hands-on challenges designed to test and improve your abilities.</p>
                            <a href="question_1.php" class="btn btn-primary card-btn">Try Challenges</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Compete</h3>
                            <p class="card-text">Join competitions and test your skills against others worldwide.</p>
                            <a href="#" class="btn btn-primary card-btn">View Competitions</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Resources</h3>
                            <p class="card-text">Access tools, guides, and additional learning materials.</p>
                            <a href="#" class="btn btn-primary card-btn">Explore Resources</a>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Center the Community card using offset in a new row -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Community</h3>
                            <p class="card-text">Connect with fellow cybersecurity enthusiasts and share knowledge.</p>
                            <a href="#" class="btn btn-primary card-btn">Join Forum</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>