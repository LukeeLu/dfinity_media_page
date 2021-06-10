from selenium import webdriver
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

def dfinity():
    browser = webdriver.Chrome('PATH.../chromedriver')
    browser.get("https://dfinity.org/media/")


    click = WebDriverWait(browser, 10).until(
        EC.presence_of_element_located((By.XPATH, '//*[@id="gatsby-focus-wrapper"]/section[3]/div[1]/div/button[1]/span'))
    )

    click.click()#模拟点击ALL选项得到所有信息
    saveHtml("output2", browser.page_source.encode("utf-8"))#生成的output2.html用于传入definity.php去解析
    browser.quit()

def saveHtml(file_name, file_content):
    with open(file_name + ".html", "wb") as f:
        f.write(file_content)

dfinity()
