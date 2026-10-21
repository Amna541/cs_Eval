<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Satisfaction Survey</title>
    <link rel="icon" href="survey_icon.png" type="image/png"> <!-- Icon for the page -->

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
        }

        .container h2 {
            font-size: 28px;
            color: #0056b3;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .container p {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        .container .survey-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .survey-form input,
        .survey-form select,
        .survey-form button {
            padding: 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
            width: 100%;
        }

        .survey-form input:focus,
        .survey-form select:focus {
            outline: none;
            border-color: #00796b;
        }

        .survey-form button {
            background-color: #00796b;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .survey-form button:hover {
            background-color: #004d40;
        }

        .reward {
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 18px;
            color: #333;
        }

        .reward strong {
            color: #00796b;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #00796b;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .banner {
            background-color: #00796b;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            font-size: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .container h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="banner">
            <strong>Win a Gift Card by Completing This Quick Survey!</strong>
        </div>

        <h2>Employee Satisfaction Survey</h2>
        <p>We value your opinion! Please take a few minutes to complete this survey and claim your gift card.</p>

        <div class="survey-form">
            <!-- Example of form with fake questions -->
            <input type="text" placeholder="Your Full Name" required>
            <input type="email" placeholder="Your Email Address" required>
            <select required>
                <option value="">How satisfied are you with your current job?</option>
                <option value="Very Satisfied">Very Satisfied</option>
                <option value="Satisfied">Satisfied</option>
                <option value="Neutral">Neutral</option>
                <option value="Dissatisfied">Dissatisfied</option>
                <option value="Very Dissatisfied">Very Dissatisfied</option>
            </select>

            <textarea placeholder="Any suggestions for improvement?" rows="4"></textarea>

            <button type="submit">Submit Survey</button>
        </div>

        <div class="reward">
            <strong>Upon completing the survey, you will be eligible for a special reward!</strong>
            <p>Click below to claim your gift card once you're finished.</p>
        </div>

        <div class="footer">
            <p>If you have any questions, feel free to <a href="mailto:support@survey.com">contact us</a>.</p>
        </div>
    </div>

</body>
</html>
