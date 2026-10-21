<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stylo E-Commerce</title>
    <style>
         body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border-bottom: 1px solid #ccc;
            padding: 15px 40px;
        }
        .logo img {
            height: 50px;
        }
        nav {
            display: flex;
            gap: 30px;
        }
        nav a {
            text-decoration: none;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ff007f;
        }
        .icons {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        .icons img {
            height: 24px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .icons img:hover {
            transform: scale(1.1);
        }
        .promo-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: 20px auto;
            overflow: hidden;
            top: -30px;
        }
        .slide {
            display: none;
            width: 100%;
            top: -90px;
        }
        .slide img {
            width: 100%;
            height: auto;
            top: -90px;
        }
        .page-two {
            text-align: center;
            padding: 50px 20px;
        }
        .view-all-container {
            display: inline-block;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px 0;
            cursor: pointer;
        }
        .image-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .image-box {
            position: relative;
            width: 30%;
        }
        .image-box img {
            width: 100%;
            height: auto;
            display: block;
        }
        /* New styles for the grey container and sale details */
        .product-details-container {
            background-color: #f2f2f2;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .product-price {
            font-size: 16px;
            color: #333;
            text-decoration: line-through;
        }
        .discount-price {
            font-size: 18px;
            color: #d32f2f;
            font-weight: bold;
            margin-top: 5px;
        }
        .sale-tag {
            font-size: 14px;
            color: red;
            font-weight: bold;
            margin-top: 5px;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }
        /* New styles for the pink quick view box */
        .quick-view {
            position: absolute;
            top: 0;
            width: 100%;
            background-color: rgba(255, 0, 127, 0.9);
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            display: none;
        }
        .image-box:hover .quick-view {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="stylo.svg" alt="Stylo Logo">
        </div>
        <nav>
            <a href="#">Winter Sale</a>
            <a href="#">New In</a>
            <a href="#">Women Shoes</a>
            <a href="#">Women Apparel</a>
            <a href="#">Bags</a>
            <a href="#">Kids</a>
            <a href="#">Accessories</a>
            <a href="#">Fragrances</a>
            <a href="#">Online Exclusives</a>
        </nav>
        <div class="icons">
            <img src="user_icon.png" alt="User Icon" title="Profile">
            <img src="search.png" alt="Search Icon" title="Search">
            <img src="cart.png" alt="Cart Icon" title="Cart">
        </div>
    </header>
    <div class="promo-banner">
        Use code ‘freedelivery' at checkout to get free delivery on prepaid orders.
    </div>
    <div class="slideshow-container">
        <div class="slide">
            <img src="s1.webp" alt="Slide 1">
        </div>
        <div class="slide">
            <img src="s2.webp" alt="Slide 2">
        </div>
    </div>
    <div class="page-two">
        <h2>Leading Choices</h2>
        <div class="view-all-container">View All</div>

        <!-- Page 2 Product Image Row -->
        <div class="slideshow-container">
            <div class="image-row" id="imageRow">
                <!-- First Set of Products -->
                <div class="image-box">
                    <img src="s3.webp" alt="Choice 1">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Pink Casual Sneakers AT7328</div>
                        <div class="product-price">Rs. 4,000</div>
                        <div class="discount-price">Rs. 2,760</div>
                        <div class="sale-tag">Sale 31%</div>
                    </div>
                </div>
                <div class="image-box">
                    <img src="s4.webp" alt="Choice 2">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Pink Casual Sneakers AT7328</div>
                        <div class="product-price">Rs. 4,000</div>
                        <div class="discount-price">Rs. 2,760</div>
                        <div class="sale-tag">Sale 31%</div>
                    </div>
                </div>
                <div class="image-box">
                    <img src="s5.webp" alt="Choice 3">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Pink Casual Sneakers AT7328</div>
                        <div class="product-price">Rs. 4,000</div>
                        <div class="discount-price">Rs. 2,760</div>
                        <div class="sale-tag">Sale 31%</div>
                    </div>
                </div>
            </div>
            <!-- Second Set of Products -->
            <div class="image-row" id="imageRow2" style="display: none;">
                <div class="image-box">
                    <img src="s6.webp" alt="Choice 4">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Red Casual Sneakers AT1234</div>
                        <div class="product-price">Rs. 4,200</div>
                        <div class="discount-price">Rs. 3,000</div>
                        <div class="sale-tag">Sale 29%</div>
                    </div>
                </div>
                <div class="image-box">
                    <img src="s7.webp" alt="Choice 5">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Red Casual Sneakers AT1234</div>
                        <div class="product-price">Rs. 4,200</div>
                        <div class="discount-price">Rs. 3,000</div>
                        <div class="sale-tag">Sale 29%</div>
                    </div>
                </div>
                <div class="image-box">
                    <img src="s8.webp" alt="Choice 6">
                    <div class="quick-view">Quick View</div>
                    <!-- Product Details under image -->
                    <div class="product-details-container">
                        <div class="product-name">Women Red Casual Sneakers AT1234</div>
                        <div class="product-price">Rs. 4,200</div>
                        <div class="discount-price">Rs. 3,000</div>
                        <div class="sale-tag">Sale 29%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Page 1 Slideshow
let page1Index = 0;
function showPage1Slides() {
    const page1Slides = document.querySelectorAll('.slide');
    page1Slides.forEach((slide, index) => {
        slide.style.display = 'none';
    });
    
    page1Index++;
    if (page1Index > page1Slides.length) { page1Index = 1; }  // Reset after last slide
    page1Slides[page1Index - 1].style.display = 'block';  // Display the current slide

    setTimeout(showPage1Slides, 3000);  // Change slide every 3 seconds
}

showPage1Slides();  // Start Page 1 slideshow


// Page 2 Slideshow (3 images at a time)
// Page 2 Slideshow (3 images at a time)
let page2Index = 0;
const slidesPerPage = 3;  // Number of images to display per slide (3 images at a time)
const page2Slides = document.querySelectorAll('.image-row .image-box'); // Get all image boxes in the row

function showPage2Slides() {
    // Calculate the starting index for the next set of 3 images
    const startIndex = page2Index * slidesPerPage;
    const endIndex = startIndex + slidesPerPage;  // Calculate the end index (exclusive)

    // Loop through the next 3 images and display them
    for (let i = startIndex; i < endIndex; i++) {
        if (i < page2Slides.length) {
            page2Slides[i].style.display = 'block';  // Show the current set of 3 images
        }
    }

    page2Index++;
    if (page2Index * slidesPerPage >= page2Slides.length) {
        page2Index = 0;  // Reset after the last set of images (loop back to the first set)
    }

    setTimeout(showPage2Slides, 2000);  // Change slide every 2 seconds
}

// Initially hide all the images to ensure only the required batch is displayed first
page2Slides.forEach((img) => {
    img.style.display = 'none';
});

showPage2Slides();  // Start Page 2 slideshow





    </script>
</body>
</html>