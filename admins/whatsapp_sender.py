import time
import sys
import urllib.parse
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

def send_whatsapp_message(phone_number, message):
    chrome_options = Options()
    # chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--log-level=3")

    # keep logged in
    chrome_options.add_argument(r"--user-data-dir=C:\Users\wiset\AppData\Local\Google\Chrome\User Data\Default")
    chrome_options.add_argument("--profile-directory=Default")

    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)

    try:
        # URL encode the message
        encoded_message = urllib.parse.quote(message)
        url = f"https://web.whatsapp.com/send?phone={phone_number}&text={encoded_message}"
        driver.get(url)

        wait = WebDriverWait(driver, 40)

        # wait until input box is ready
        input_box = wait.until(EC.presence_of_element_located((By.XPATH, '//div[@contenteditable="true"][@data-tab="10"]')))
        
        # hit ENTER to send the pre-typed message
        input_box.send_keys(Keys.ENTER)

        # Wait for message to be sent - INCREASED WAIT TIME
        time.sleep(8)  # 3 seconds se badhakar 8 seconds kar diya

        # Use ASCII characters instead of Unicode to avoid encoding issues
        print("SUCCESS: Message sent to " + phone_number)
        return True

    except Exception as e:
        # Use ASCII characters instead of Unicode to avoid encoding issues
        print("ERROR: " + str(e))
        return False

    finally:
        driver.quit()

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python admins/whatsapp_sender.py <phone_number> <message>")
        sys.exit(1)
    
    phone = sys.argv[1]
    msg = sys.argv[2]
    success = send_whatsapp_message(phone, msg)
    sys.exit(0 if success else 1)