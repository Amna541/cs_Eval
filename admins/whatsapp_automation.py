import time
import urllib.parse
import sys
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.action_chains import ActionChains

def send_video_headless(phone_number, video_path):
    try:
        # Set up Chrome in headless mode
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--user-data-dir=C:\Users\wiset\AppData\Local\Google\Chrome\User Data\Default")
        
        driver = webdriver.Chrome(options=chrome_options)
        
        # Open WhatsApp Web
        driver.get("https://web.whatsapp.com/send?phone=" + urllib.parse.quote(phone_number))
        
        # Wait for page to load
        wait = WebDriverWait(driver, 30)
        
        try:
            # Check if we're on the login page
            if "qr" in driver.page_source.lower():
                print("ERROR: Not logged in to WhatsApp Web. Please scan QR code first.")
                driver.quit()
                return False
                
            # Wait for the message input box to be present
            message_input = wait.until(
                EC.presence_of_element_located((By.CSS_SELECTOR, "div[contenteditable='true'][data-tab='10']"))
            )
            
            # Click the attachment button using proper element detection
            attachment_btn = wait.until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, "span[data-icon='attach-menu-plus']"))
            )
            attachment_btn.click()
            time.sleep(2)
            
            # Find and click the document option
            document_btn = wait.until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, "input[type='file']"))
            )
            
            # Send the video file path to the input element
            document_btn.send_keys(video_path)
            time.sleep(3)
            
            # Wait for the file to upload and click send
            send_btn = wait.until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, "span[data-icon='send']"))
            )
            send_btn.click()
            time.sleep(3)
            
            print("SUCCESS: Video sent successfully in headless mode")
            driver.quit()
            return True
            
        except Exception as e:
            print(f"ERROR: {str(e)}")
            driver.save_screenshot("error_screenshot.png")
            driver.quit()
            return False
            
    except Exception as e:
        print(f"ERROR: {str(e)}")
        return False

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python whatsapp_video_headless.py <phone_number> <video_path>")
        sys.exit(1)
        
    phone = sys.argv[1]
    video_path = sys.argv[2]
    
    success = send_video_headless(phone, video_path)
    if success:
        print("SUCCESS: Video sent successfully")
    else:
        print("ERROR: Failed to send video")