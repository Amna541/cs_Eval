<?php 
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>President of SGC</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <style>
        .president-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px;
            background-color: #f8f9fa;
        }
        .president-text {
            flex: 1;
            max-width: 50%;
            padding-right: 30px;
        }
        .president-text h2 {
            color: #002147;
            font-size: 32px;
            font-weight: bold;
        }
        .president-text span {
            color: #ffb400;
            font-size: 32px;
            font-weight: bold;
        }
        .president-text p {
            color: #444;
            font-size: 16px;
            line-height: 1.6;
        }
        .president-image {
            flex: 1;
            max-width: 50%;
        }
        .president-image img {
            width: 100%;
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .president-section {
                flex-direction: column;
                text-align: center;
            }
            .president-text, .president-image {
                max-width: 100%;
                padding-right: 0;
            }
        }
    </style>
</head>

<body>

<div id="page-wrapper">
    <div class="row"> 
        
        <!-- Include Header -->
        <?php include("include/header.php"); ?>

        <!-- Main Content -->
        <div class="president-section">
            <div class="president-text">
                <h2>The President of SGC, <span>Mr. Sardar Tanveer Ilyas Khan</span></h2>
                <p>
                    A name that does not need introduction, but it would be unfair not to mention just a few attributes. 
                    A philanthropist, educationist, and a renowned politician, he has had the great distinction and honor 
                    of being elected as the Prime Minister of Azad Jammu Kashmir. This one-man army has been constantly working 
                    and living each moment to bring in more opportunities.
                </p>
                <h3>Vision</h3>
                <p>
                    Empowering diverse industries through a commitment to excellence in education, pioneering innovation 
                    in real estate development, delivering unparalleled hospitality experiences, driving sustainable textile 
                    manufacturing practices, and providing innovative property solutions.
                </p>
            </div>
            <div class="president-image">
            <img src="/Real-Estate-Management-System-main/admin/assets/img/stak.jpg" alt="The President of SGC" class="img-fluid" style="max-width: 300px; border-radius: 10px;">

            </div>
        </div>

        <!-- Include Footer -->
        <?php include("include/footer.php"); ?>
        
        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>
</div>

<!-- JS Files -->
<script src="js/jquery.min.js"></script> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/custom.js"></script>

</body>
</html>
