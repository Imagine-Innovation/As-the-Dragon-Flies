from playwright.sync_api import sync_playwright
import os

def run_cuj(page, script_dir):
    # Use relative path from script location
    html_file = os.path.join(script_dir, "test_editor.html")
    file_url = "file://" + os.path.abspath(html_file)
    page.goto(file_url)
    page.wait_for_timeout(500)

    editor = page.locator("#editor1-editor")

    # 1. Test clearing Scroll block
    page.evaluate('''() => {
        const editor = document.getElementById('editor1-editor');
        const scrollDiv = editor.querySelector('div.scroll');
        const range = document.createRange();
        range.selectNodeContents(scrollDiv);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }''')
    page.wait_for_timeout(500)
    page.click("#clear-btn")
    page.wait_for_timeout(500)

    html = editor.inner_html()
    if 'class="scroll"' not in html:
         print("SUCCESS: Scroll block cleared")
    else:
         print("FAILED: Scroll block still exists")

    # 2. Test clearing Scroll text paragraph
    page.evaluate('''() => {
        const editor = document.getElementById('editor1-editor');
        const p = editor.querySelector('p.text-scroll');
        const range = document.createRange();
        range.selectNodeContents(p);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }''')
    page.wait_for_timeout(500)
    page.click("#clear-btn")
    page.wait_for_timeout(500)

    html = editor.inner_html()
    if 'class="text-scroll"' not in html:
         print("SUCCESS: Scroll text class cleared")
    else:
         print("FAILED: Scroll text class still exists")

    # 3. Test clearing Dwarvish text paragraph
    page.evaluate('''() => {
        const editor = document.getElementById('editor1-editor');
        const p = editor.querySelector('p.text-dwarvish');
        const range = document.createRange();
        range.selectNodeContents(p);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }''')
    page.wait_for_timeout(500)
    page.click("#clear-btn")
    page.wait_for_timeout(500)

    html = editor.inner_html()
    if 'class="text-dwarvish"' not in html:
         print("SUCCESS: Dwarvish text class cleared")
    else:
         print("FAILED: Dwarvish text class still exists")

    # Take screenshot
    screenshot_path = os.path.join(script_dir, "screenshots", "verification.png")
    page.screenshot(path=screenshot_path)
    page.wait_for_timeout(1000)

if __name__ == "__main__":
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.makedirs(os.path.join(script_dir, "videos"), exist_ok=True)
    os.makedirs(os.path.join(script_dir, "screenshots"), exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            record_video_dir=os.path.join(script_dir, "videos")
        )
        page = context.new_page()
        try:
            run_cuj(page, script_dir)
        finally:
            context.close()
            browser.close()
