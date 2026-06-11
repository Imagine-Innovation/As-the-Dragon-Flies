from playwright.sync_api import sync_playwright
import os

def run_cuj(page):
    # Get the absolute path to the HTML file
    file_path = "file://" + os.path.abspath("verification/test_editor.html")
    page.goto(file_path)
    page.wait_for_timeout(500)

    editor = page.locator("#editor1-editor")

    # Verify initial state
    print("Initial HTML:", editor.inner_html())

    # 1. Test clearing Scroll block
    # Select text inside scroll block
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

    html_after_scroll_clear = editor.inner_html()
    print("After clearing scroll block:", html_after_scroll_clear)
    if 'class="scroll"' in html_after_scroll_clear:
         print("FAILED: Scroll block still exists")
    else:
         print("SUCCESS: Scroll block cleared")

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

    html_after_text_scroll_clear = editor.inner_html()
    print("After clearing scroll text:", html_after_text_scroll_clear)
    if 'class="text-scroll"' in html_after_text_scroll_clear:
         print("FAILED: Scroll text class still exists")
    else:
         print("SUCCESS: Scroll text class cleared")

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

    html_after_dwarvish_clear = editor.inner_html()
    print("After clearing dwarvish text:", html_after_dwarvish_clear)
    if 'class="text-dwarvish"' in html_after_dwarvish_clear:
         print("FAILED: Dwarvish text class still exists")
    else:
         print("SUCCESS: Dwarvish text class cleared")

    # 4. Test partial selection in scroll block
    # Reset HTML
    page.evaluate('''() => {
        const editor = document.getElementById('editor1-editor');
        editor.innerHTML = '<div class="scroll"><p>One</p><p>Two</p></div>';
        const p = editor.querySelector('p');
        const range = document.createRange();
        range.selectNodeContents(p);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }''')
    page.wait_for_timeout(500)
    page.click("#clear-btn")
    page.wait_for_timeout(500)
    html_after_partial = editor.inner_html()
    print("After clearing partial selection in scroll block:", html_after_partial)
    if 'class="scroll"' in html_after_partial:
         print("FAILED: Scroll block still exists after partial selection")
    else:
         print("SUCCESS: Scroll block cleared after partial selection")

    # Take screenshot
    page.screenshot(path="/home/jules/verification/screenshots/verification.png")
    page.wait_for_timeout(1000)

if __name__ == "__main__":
    os.makedirs("/home/jules/verification/videos", exist_ok=True)
    os.makedirs("/home/jules/verification/screenshots", exist_ok=True)
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            record_video_dir="/home/jules/verification/videos"
        )
        page = context.new_page()
        try:
            run_cuj(page)
        finally:
            context.close()
            browser.close()
