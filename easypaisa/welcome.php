<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Easypaisa</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            overflow-x: hidden;
        }

        .page {
            min-height: 100vh;
            padding: 50px 20px;
            text-align: center;
            color: #fff;
        }

        #page1 {
            background-image: url('image.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #page2, #page3, #page4, #page5, #page6, #page7 {
            background-color: #EBF5EF;
        }

        .highlight-green {
            color: green;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Ensure modal appears on top */
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            width: 350px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-out;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        .star-rating {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star {
            font-size: 40px;
            color: gray;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .star:hover,
        .star.selected {
            color: #FFD700; /* Gold color for selected stars */
        }

        /* easypaisa in the center of the modal */
        .easypaisa {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        /* Updated Close Button Styles */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            z-index: 10001; /* Ensure it stays on top of modal content */
        }

        /* Transition for page2 */
        #page2 {
            opacity: 0;
            transform: translateY(100px);
            transition: opacity 1s ease-out, transform 1s ease-out;
        }

        #page2.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Comment Textarea Styling */
        .comment-container {
            margin-bottom: 20px;
            margin-right: 30px;
            margin-left: 10px;
        }

        textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: none;
            outline: none;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            border-color: #4CAF50;
        }

        /* Submit Button Styling */
        .submit-container {
            margin-top: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Modal Fade In Effect */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* "Thank You" Popup */
        /* Full-Screen Thank You Overlay */
.thank-you-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
    display: none; /* Initially hidden */
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Make sure it's on top */
}

/* Thank you text styling */
.thank-you-text {
    font-size: 2rem;
    font-weight: bold;
    color: #fff;
    text-align: center;
    text-shadow: 0 0 15px rgba(255, 215, 0, 1); /* Glowing text */
    animation: fadeInText 1s ease-out;
}

/* Animation for fading in the text */
@keyframes fadeInText {
    0% {
        opacity: 0;
        transform: scale(0.8);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

    </style>
</head>
<body>

<!-- Rating Modal -->
<div id="ratingModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">×</span>

        <img src="easypaisa.png" alt="easypaisa" class="easypaisa"> <!-- Replace with your actual easypaisa image path -->
        
        
        <p>Love navigating through our website?</p>
        <h2>How would you rate your experience?</h2>
        <div class="star-rating">
            <span class="star" onclick="selectRating(1)">&#9733;</span>
            <span class="star" onclick="selectRating(2)">&#9733;</span>
            <span class="star" onclick="selectRating(3)">&#9733;</span>
            <span class="star" onclick="selectRating(4)">&#9733;</span>
            <span class="star" onclick="selectRating(5)">&#9733;</span>
        </div>
        <p id="ratingText">Comments</p>

        <!-- Comment Writing Container -->
        <div class="comment-container">
            <textarea id="userComment" placeholder="Write your comment here..." rows="4"></textarea>
        </div>

        <!-- Submit Button Container -->
        <div class="submit-container">
            <button onclick="submitFeedback()">Submit</button>
        </div>
    </div>
</div>
<!-- Full-Screen Thank You Overlay -->
<div id="thankYouOverlay" class="thank-you-overlay">
    <div class="thank-you-text">
        Thank you for your feedback🎉❤️
    </div>
</div>



<div id="page1" class="page"></div>

<div id="page2" class="page" style="display: flex; align-items: center; justify-content: center; background-color: #EBF5EF; padding: 50px 20px; flex-wrap: wrap;">
    <div style="flex: 1; padding: 20px; text-align: left; display: flex; justify-content: center;">
        <img src="e5.webp" alt="Image Description" class="slide-in-image" style="max-width: 100%; height: auto; border-radius: 8px;">
    </div>
    <div style="flex: 1; padding: 20px; text-align: left;">
        <img src="e4.png" alt="Small Image" style="max-width: 30%; height: auto; border-radius: 8px;">
        <p class="slide-in-text" style="font-size: 20px; color:black; margin-bottom: 10px;">Making your everyday transactions easy, one tap at a time!</p>
        <p class="slide-in-text" style="font-size: 22px; color:black; font-weight: bold;">#live<span class="highlight-green">easy</span></p>
        <video class="slide-in-video" width="100%" controls>
            <source src="v1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        #page2 {
            flex-direction: column; /* Stack the sections vertically on small screens */
            text-align: center;
        }
        #page2 > div {
            flex: 0 0 100%; /* Allow each section to take full width */
            padding: 10px;
        }
        #page2 img {
            max-width: 90%; /* Adjust image size for smaller screens */
        }
    }
</style>


<style>
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .slide-in-text {
        animation: slideInRight 1.5s ease-out forwards;
    }

    .slide-in-video {
        animation: slideInRight 1.5s ease-out forwards;
        animation-delay: 1s; /* Delay to appear after text */
        opacity: 0;
    }

    .slide-in-image {
        animation: slideInLeft 1.5s ease-out forwards;
        animation-delay: 2s; /* Delay to appear after video */
        opacity: 0;
    }
</style>


<div id="page3" class="page" style="
    position: relative; 
    background-color: #EBF5EF;
    background: url('y22.png') no-repeat bottom center; 
    background-size: 100% 150px;  /* Adjust the width (100%) and height (150px) of the wave */
    background-position: bottom center; 
    padding: 50px 20px; 
    text-align: center;
    height: 400px; /* Adjust section height */
">
    <p class="slide-up-text" style="font-size: 20px; color:black; max-width: 100%; margin-bottom: 10px; font-weight:bold;">
        Welcome to Pakistan’s favorite way to pay!<br>
    </p>

    <p class="slide-up-text" style="font-family: 'Roboto', Sans-serif; font-size: 15px; text-align:center; max-width: 50%; 
        margin-left: auto; margin-right: auto; color:black; margin-bottom: 20px; font-weight: 100px; font-size:medium;">
        easypaisa is more than just money transfer. With the latest easypaisa app you can order your own 
        <br class="hide-on-mobile"> easypaisa debit card. You can also recharge your phone, pay utility bills, send gifts, win rewards, cashbacks 
        <br class="hide-on-mobile"> and enjoy discounts on partner restaurants and shopping.
    </p>

    <img src="e8.png" alt="Easypaisa Services" class="slide-up-image" style="max-width: 100%; height: auto; border-radius: 8px; position: relative; z-index: 1;">
</div>

<style>
    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Text animation first */
    .slide-up-text {
        animation: slideUp 1.5s ease-out forwards;
    }

    /* Image animation with delay */
    .slide-up-image {
        animation: slideUp 1.5s ease-out forwards;
        animation-delay: 1s; /* Image appears after text animation */
        opacity: 0; /* Initially hidden */
    }
</style>





<div id="page4" class="page" style="display: flex; flex-wrap: wrap; align-items: center; gap: 50px; padding: 60px; background-color: #eaf3eb;">
    <!-- Left side (Image) -->
    <div style="flex: 1; max-width: 100%; padding: 20px; display: flex; justify-content: center;">
        <img src="e7.png" alt="Image Description" class="slide-up-image"
            style="max-width: 80%; height: auto; border-radius: 8px;">
    </div>

    <!-- Right side (Text and Images) -->
    <div style="flex: 1; max-width: 100%; text-align: left; position: relative; top: -50px;">
        <p class="slide-up-text" style="font-size: 32px; color: #322D41; font-weight: bold; margin-bottom: 15px; font-family: 'Roboto Slab', serif;">
            Money Transfer
        </p>
        <p class="slide-up-text" style="font-size: 20px; color:#322D41; line-height: 1.6; margin-bottom: 30px; font-weight:1000; width:87%; margin-top: 25px; font-family: 'Roboto Slab', serif;">
            Humans have never had so many ways to transfer money – that too, all in one place. 
            With Easypaisa, you can send money to anyone you want, on any network, on any number!
        </p>

        <!-- Learn More Button -->
        <div class="slide-up-text" style="
            background-color: #ffffff; 
            border-radius: 30px; 
            padding: 15px 35px; 
            display: inline-block; 
            margin-bottom: 40px; 
            cursor: pointer; 
            transition: all 0.3s ease-in-out; 
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        " 
        onmouseover="this.style.backgroundColor='#e0e0e0'" 
        onmouseout="this.style.backgroundColor='#ffffff'" 
        onclick="window.location.href='https://example.com';">
            <p style="margin: 0; font-size: 18px; font-weight: bold; color: #333; text-align: center;">Learn More</p>
        </div>

        <!-- Six images in a row -->
        <div class="slide-up-images" style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <div style="text-align: center;">
                <img src="y20.png" alt="Easypaisa" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Easypaisa</p>
            </div>
            <div style="text-align: center;">
                <img src="y21.png" alt="Bank Transfer" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Bank Transfer</p>
            </div>
            <div style="text-align: center;">
                <img src="y19.png" alt="Mobile Account" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Other Mobile Account</p>
            </div>
            <div style="text-align: center;">
                <img src="y18.png" alt="CNIC" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">CNIC</p>
            </div>
            <div style="text-align: center;">
                <img src="y17.png" alt="WhatsApp" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">WhatsApp</p>
            </div>
            <div style="text-align: center;">
                <img src="y16.png" alt="Raast" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Raast</p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Text animation first */
    .slide-up-text {
        animation: slideUp 1.5s ease-out forwards;
    }

    /* Image animation after text */
    .slide-up-image {
        animation: slideUp 1.5s ease-out forwards;
        animation-delay: 1s; /* Delay to start after text */
        opacity: 0;
    }

    /* Small images animation */
    .slide-up-images {
        animation: slideUp 1.5s ease-out forwards;
        animation-delay: 1.5s; /* Delayed after the main image */
        opacity: 0;
    }

    @media (max-width: 768px) {
        #page4 {
            flex-direction: column; /* Stack sections vertically on smaller screens */
            height: auto; /* Adjust height for stacked layout */
        }
        #page4 > div {
            flex: 0 0 100%; /* Allow sections to take full width */
            padding: 10px;
        }
        #page4 img {
            max-width: 90%; /* Adjust image size for smaller screens */
        }
    }
</style>



<div id="page5" class="page" style="display: flex; flex-wrap: wrap; align-items: center; gap: 30px; padding: 20px; background-color: #EBF5EF;">
    <!-- Left side (Text) -->
    <div style="flex: 1; max-width: 100%; text-align: left; position: relative; top: -90px; background-color: #EBF5EF; padding: 20px;">
        <p style="font-size: 24px; color: black; font-weight: bold; margin-bottom: 10px; margin-left: 60px;">Utility Bill Payments</p>
        <p style="font-size: 20px; color: black; line-height: 1.5; margin-bottom: 20px; margin-left: 60px;">
        It’s no trouble anymore; as we have brought more than 1500+ billers all on the easypaisa app. So whether it’s electricity bills, gas, water or telephone, there’s the easypaisa app for that!
        </p>
        
        <!-- Icons with names in two rows -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px; row-gap: 20px; justify-content: center; background-color: #EBF5EF;">
            <!-- Row 1 -->
            <div style="width: 23%; text-align: center;">
                <img src="y15.png" alt="Electricity" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Electricity</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y14.png" alt="Telephone" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Telephone</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y13.png" alt="Gas" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Gas</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y12.png" alt="Education" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Education</p>
            </div>
            
            <!-- Row 2 -->
            <div style="width: 23%; text-align: center;">
                <img src="y11.png" alt="Internet" style="width:35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Internet</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y10.png" alt="Water" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Water</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y9.png" alt="Government Fees" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Government Fees</p>
            </div>
            <div style="width: 23%; text-align: center;">
                <img src="y8.png" alt="Credit Card" style="width: 35%; border-radius: 8px;">
                <p style="margin-top: 5px;color:#322D41">Credit Card</p>
            </div>
        </div>
    </div>

    <!-- Right side (Image) -->
    <div style="flex: 1; max-width: 100%; background-color: #EBF5EF; padding: 20px;">
        <img src="ps5.png" alt="Image Description" 
            style="max-width: 90%; height: auto; border-radius: 8px;">
    </div>
</div>

<style>
    @media (max-width: 768px) {
        #page5 {
            flex-direction: column-reverse; /* Stack sections vertically with image on top */
        }

        #page5 > div {
            flex: 0 0 100%; /* Allow sections to take full width */
            padding: 10px;
        }

        #page5 img {
            max-width: 90%; /* Adjust image size for smaller screens */
        }

        .slide-up-images div {
            width: 45%; /* Adjust icon width for smaller screens */
        }
    }
</style>




<div id="page6" class="page" style="display: flex; flex-wrap: wrap; align-items: center; gap: 50px; padding: 60px; background-color: #EBF5EF;">
    <!-- Left side (Image) -->
    <div style="flex: 1; max-width: 100%; display: flex; justify-content: center;">
        <img src="c1.png" alt="Image Description" class="slide-right"
            style="max-width: 80%; height: auto; border-radius: 8px;">
    </div>

    <!-- Right side (Text and Images) -->
    <div style="flex: 1; max-width: 100%; text-align: left; position: relative; top: -50px; padding: 20px;">
        <p class="fade-in-text" style="font-size: 32px; color: #322D41; font-weight: bold; margin-bottom: 15px; font-family: 'Roboto Slab', serif;">
            Mobile Load & Packages
        </p>
        <p class="fade-in-text" style="font-size: 20px; color:#322D41; line-height: 1.6; margin-bottom: 30px; font-weight:1000; width:87%; margin: top 25px; font-family: 'Roboto Slab', serif;">
            Running low on balance? Stay seated, please. With easypaisa you can recharge your own, or anyone else’s number straight from your home. Not just that, also enjoy special discounted packages from all mobile networks of Pakistan!
        </p>

        <!-- Learn More Button -->
        <div class="fade-in-text" style="
            background-color: #ffffff; 
            border-radius: 30px; 
            padding: 15px 35px; 
            display: inline-block; 
            margin-bottom: 40px; 
            cursor: pointer; 
            transition: all 0.3s ease-in-out; 
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        " 
        onmouseover="this.style.backgroundColor='#e0e0e0'" 
        onmouseout="this.style.backgroundColor='#ffffff'" 
        onclick="window.location.href='https://example.com';">
            <p style="margin: 0; font-size: 18px; font-weight: bold; color: #333; text-align: center;">Learn More</p>
        </div>

        <!-- Four images in a row, separated by lines -->
        <div class="fade-in-text" style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <div style="text-align: center; padding-left: 20px; top:-90px;">
                <img src="y20.png" alt="Easypaisa" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Easypaisa</p>
            </div>
            <div style="text-align: center; height: 60px; border-left: 3px solid #322D41; padding-left: 20px;">
                <img src="y21.png" alt="Bank Transfer" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Bank Transfer</p>
            </div>
            <div style="text-align: center; height: 60px; border-left: 3px solid #322D41; padding-left: 20px;">
                <img src="y19.png" alt="Mobile Account" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">Other Mobile Account</p>
            </div>
            <div style="text-align: center; height: 60px; border-left: 3px solid #322D41; padding-left: 20px;">
                <img src="y18.png" alt="CNIC" style="width: 80px; border-radius: 50%;">
                <p style="font-size: 16px; color: #333; margin-top: 10px;">CNIC</p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slideRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Image animation first (right to left) */
    .slide-right {
        animation: slideRight 1.5s ease-out forwards;
    }

    /* Text animation after image */
    .fade-in-text {
        animation: fadeIn 1.5s ease-out forwards;
        animation-delay: 1s; /* Delay to appear after image */
        opacity: 0;
    }

    @media (max-width: 768px) {
        #page6 {
            flex-direction: column; /* Stack sections vertically */
            padding: 30px; /* Adjust padding for smaller screens */
        }

        #page6 > div {
            flex: 1 100%; /* Allow sections to take full width */
            margin-bottom: 30px;
        }

        .fade-in-text {
            animation-delay: 0.5s; /* Adjust animation delay for mobile */
        }

        .slide-right {
            max-width: 100%; /* Make the image fit on smaller screens */
        }

        .slide-right, .fade-in-text {
            animation-duration: 1s; /* Faster animations on small screens */
        }
    }
</style>




<!-- Page 7 -->
<div id="page7" class="page" style="display: flex; flex-wrap: wrap; align-items: center; gap: 30px; padding: 20px;">
    <!-- Left side (Text) -->
    <div style="flex: 1; text-align: left; position: relative; opacity: 0; animation: slideInText 1s forwards;">
        <div style="margin-left: 60px;">
            <p style="font-size: 24px; color: black; font-weight: bold; margin-bottom: 10px;">Savings</p>
            <p style="font-size: 20px; color: black; line-height: 1.5; margin-bottom: 20px;">
                Subscribe a savings plan of your choice and get up to 14% annual profit. With zero documentation and no fixed deposits, choose your plan with just one tap!
            </p>

            <!-- Learn More Button -->
            <div style="background-color: #ffffff; 
                        border-radius: 30px; 
                        padding: 15px 35px; 
                        display: inline-block; 
                        margin-top: 20px; 
                        cursor: pointer; 
                        transition: all 0.3s ease-in-out; 
                        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);"
                onmouseover="this.style.backgroundColor='#e0e0e0'" 
                onmouseout="this.style.backgroundColor='#ffffff'" 
                onclick="window.location.href='https://example.com';">
                <p style="margin: 0; font-size: 18px; font-weight: bold; color: #333; text-align: center;">Learn More</p>
            </div>
        </div>
    </div>

    <!-- Right side (Image) -->
    <div style="flex: 1; opacity: 0; animation: slideInImage 1s 0.5s forwards;">
        <img src="y23.png" alt="Image Description" style="max-width: 90%; height: auto; border-radius: 8px;">
    </div>
</div>

<style>
    @keyframes slideInText {
        from {
            opacity: 0;
            transform: translateX(-100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInImage {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive Layout for smaller screens */
    @media (max-width: 768px) {
        #page7 {
            flex-direction: column; /* Stack image and text on top of each other */
            gap: 20px; /* Adjust gap for smaller screens */
        }

        #page7 > div {
            flex: 1 100%; /* Make both sections take full width */
            margin-left: 0;
        }

        /* Adjust image margin and max-width */
        .page > div:nth-child(2) img {
            max-width: 80%; /* Adjust image width for smaller screens */
            margin-left: 0;
        }

        /* Make text readable on mobile */
        .page > div:nth-child(1) p {
            font-size: 18px; /* Adjust font size for smaller screens */
        }

        /* Adjust animation durations and delays */
        .page > div:nth-child(1) {
            animation-duration: 1s;
            animation-delay: 0.5s;
        }

        .page > div:nth-child(2) {
            animation-duration: 1s;
            animation-delay: 1s;
        }
    }
</style>


<!-- Page 8 -->
<div id="page8" class="page">
    <!-- New Text with Image -->
    <div style="text-align: left; margin-top: -90px; opacity: 0; animation: slideInText 1s forwards;">
        <p style="font-size: 32px; color: #333; font-weight: bolder;">Wherever you go, we go!</p>
        <p style="font-size: 18px; color: #333;">With the largest footprint in the country, constant innovation, an ever-growing range of ground-breaking digital solutions and “a state of the art mobile app,” we are Pakistan’s favourite way to pay.</p>
        <p style="font-size: 18px; color: #333; font-weight: bolder;">Secured and regulated by State Bank of Pakistan.</p>

        <!-- Image displayed below text, slightly aligned to the left -->
        <div style="margin-top: 10px; text-align: left; opacity: 0; animation: slideInImage 1s 0.5s forwards;">
            <img src="y3.png" alt="Image Description" style="width: 100%; max-width: 500px; height: auto;">
        </div>


        <!-- Green line for separation -->
        <div style="background-color: #00BD60; height: 3px; margin-top: 20px; margin-left: 100px; margin-right: 100px;"></div>

        <!-- Centered text -->
        <div style="text-align: center; margin-top: 20px;">
            <p style="font-size: 24px; color: #333; font-weight: bolder;">...there is so much more!</p>
        </div>

        <!-- In-app Verification Section -->
        <div style="display: flex; justify-content: space-between; margin-top: 40px; text-align: center;">
            <div style="opacity: 0; animation: slideInImage 1s 1s forwards;">
                <img src="y1.png" alt="Image Description" style="width: 100px; height: auto;">
                <p style="font-size: 18px; color: #333; font-weight: bolder;">In-app Verification</p>
                <p style="font-size: 16px; color: #333;">Verify your account anytime in easypaisa app.</p>
            </div>
            
            <!-- Account Upgrade Section -->
            <div style="opacity: 0; animation: slideInImage 1s 1.5s forwards;">
                <img src="y2.png" alt="Image Description" style="width: 100px; height: auto;">
                <p style="font-size: 18px; color: #333; font-weight: bolder;">Account Upgrade</p>
                <p style="font-size: 16px; color: #333;">Enjoy increased limits of up to Rs. 10 lac.</p>
            </div>
        </div>

        <!-- Green line for separation (after Account Upgrade section) -->
        <div style="background-color: #00BD60; height: 3px; margin-top: 20px; margin-left: 100px; margin-right: 100px;"></div>
        
        <!-- "Ready to get started with easypaisa" Section -->
        <div style="text-align: start; margin-top: 20px;">
            <p style="font-size: 24px; color: #00BD60; font-weight: bolder; margin-left: 100px;">Ready to get started with easypaisa</p>
        </div>

        <!-- New Green line for separation -->
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <div style="background-color: #00BD60; height: 3px; width: 20%; margin-left: 100px;"></div>
            <div style="background-color: #00BD60; height: 3px; width: 20%; margin-left: 100px; margin-right: 100px;"></div>
            <div style="background-color: #00BD60; height: 3px; width: 20%; margin-left: 30px; margin-right: 100px;"></div>
        </div>
    </div>
</div>

<style>
    @keyframes slideInText {
        0% {
            transform: translateX(100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideInImage {
        0% {
            transform: translateX(100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive Layout for smaller screens */
    @media (max-width: 768px) {
        /* Adjust text alignment and image size for smaller screens */
        #page8 > div {
            margin-left: 0;
            margin-right: 0;
        }

        #page8 img {
            width: 90%; /* Make image responsive */
            margin-left: 0;
            margin-right: 0;
        }

        /* Stack In-app Verification and Account Upgrade vertically */
        #page8 > div > div {
            margin-bottom: 30px;
        }

        /* Adjust Green lines for smaller screens */
        #page8 div[style*="background-color: #00BD60;"] {
            margin-left: 0;
            margin-right: 0;
        }

        /* Adjust font sizes for mobile */
        #page8 p {
            font-size: 16px;
        }

        /* Center text for mobile */
        #page8 .page > div p {
            font-size: 20px;
        }
    }
</style>












<script>
    // Combined window.onload function
window.onload = function() {
    // Show the rating modal after 1 second
    setTimeout(function() {
        document.getElementById("ratingModal").style.display = "flex";
    }, 1000); // Delay to show the modal after 1 second

    // Transition for page2 (show it when page loads)
    document.getElementById("page2").classList.add('show');
};

// Close modal
function closeModal() {
    document.getElementById("ratingModal").style.display = "none";
}

// Select Rating Function
function selectRating(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });

    // Display the full-screen "Thank you" text
    showThankYouText();
}

// Show full-screen "Thank you for your feedback!" text
function showThankYouText() {
    const thankYouOverlay = document.getElementById("thankYouOverlay");
    thankYouOverlay.style.display = "flex"; // Show overlay

    // Hide the overlay after 5 seconds
    setTimeout(function() {
        thankYouOverlay.style.display = "none";
    }, 2000);
}

// Scroll event to show page2 when it's in view
document.addEventListener('scroll', function() {
    const page2 = document.getElementById('page2');
    const page2Position = page2.getBoundingClientRect().top;
    const screenPosition = window.innerHeight / 1.2;

    if (page2Position < screenPosition) {
        page2.classList.add('show');
    }
});

</script>
<?php include 'footer.php'; ?>

</body>
</html>
