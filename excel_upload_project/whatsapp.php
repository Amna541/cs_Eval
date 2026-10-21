<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message to WhatsApp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #25D366, #128C7E);
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            transition: 0.3s;
        }
        .container:hover {
            transform: scale(1.05);
        }
        h2 {
            color: #128C7E;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
        }
        input, textarea {
            width: calc(100% - 20px);
            margin: 10px 0;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #25D366;
            box-shadow: 0 0 8px rgba(37, 211, 102, 0.5);
        }
        input[name='phone'] {
            padding-left: 10px;
        }
        textarea {
            resize: none;
            height: 100px;
        }
        button {
            background-color: #25D366;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
            width: 100%;
            font-weight: bold;
        }
        button:hover {
            background-color: #1ebe57;
            box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let phoneInput = document.querySelector("input[name='phone']");
            phoneInput.value = "+92";
            phoneInput.addEventListener("input", function () {
                if (!phoneInput.value.startsWith("+92")) {
                    phoneInput.value = "+92";
                }
            });
        });

        function sendMessage(event) {
            event.preventDefault();
            let phone = document.querySelector("input[name='phone']").value.trim();
            let message = encodeURIComponent(document.querySelector("textarea[name='message']").value.trim());

            let whatsappURL = `https://web.whatsapp.com/send?phone=${phone}&text=${message}`;
            let whatsappWindow = window.open(whatsappURL, "whatsappWindow");
            
            if (!whatsappWindow) {
                alert("Please allow pop-ups for this site to use WhatsApp Web.");
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Send WhatsApp Message</h2>
        <form onsubmit="sendMessage(event)">
            <input type="text" name="phone" required>
            <textarea name="message" placeholder="Enter your message" required></textarea>
            <button type="submit">Send to WhatsApp</button>
        </form>
    </div>
</body>
</html>