(function() {
    const SESSION_TIMEOUT = 900; // Seconds (matches server-side timeout)
    const REDIRECT_GRACE_PERIOD = 6; // Seconds before actual timeout to redirect
    const WARNING_TIME_BEFORE_REDIRECT = 120; // Seconds before redirect to show warning (2 minutes)

    const actualTimeoutDuration = (SESSION_TIMEOUT - REDIRECT_GRACE_PERIOD) * 1000; // milliseconds
    const warningDuration = (SESSION_TIMEOUT - REDIRECT_GRACE_PERIOD - WARNING_TIME_BEFORE_REDIRECT) * 1000; // milliseconds

    let activityTimer;
    let warningTimer;
    let modalTimer;
    let countdownInterval;

    const warningModalId = 'sessionTimeoutWarningModal';
    const countdownDisplayId = 'sessionTimeoutCountdown';
    const keepAliveButtonId = 'sessionKeepAliveBtn';
    const logoutButtonId = 'sessionLogoutBtn'; // For manual logout from modal

    function createWarningModal() {
        if (document.getElementById(warningModalId)) {
            return; // Modal already exists
        }

        const modalHTML = `
            <div class="modal fade" id="${warningModalId}" tabindex="-1" aria-labelledby="sessionTimeoutWarningLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sessionTimeoutWarningLabel">Session Timeout Warning</h5>
                            <!-- No close button for forced interaction -->
                        </div>
                        <div class="modal-body">
                            <p>Your session is about to expire due to inactivity.</p>
                            <p>You will be logged out in <span id="${countdownDisplayId}">${WARNING_TIME_BEFORE_REDIRECT}</span> seconds.</p>
                            <p>Do you want to stay logged in?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="${keepAliveButtonId}">Stay Logged In</button>
                            <button type="button" class="btn btn-secondary" id="${logoutButtonId}">Logout Now</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        document.getElementById(keepAliveButtonId).addEventListener('click', keepSessionAlive);
        document.getElementById(logoutButtonId).addEventListener('click', redirectToLockScreen);
    }

    function showWarningModal() {
        createWarningModal(); // Ensure modal exists
        const modalElement = document.getElementById(warningModalId);
        const modal = new bootstrap.Modal(modalElement); // Assuming Bootstrap 5 is used
        modal.show();

        let countdown = WARNING_TIME_BEFORE_REDIRECT;
        const countdownDisplay = document.getElementById(countdownDisplayId);
        countdownDisplay.textContent = countdown;

        if (countdownInterval) clearInterval(countdownInterval); // Clear existing interval if any
        countdownInterval = setInterval(() => {
            countdown--;
            if (countdownDisplay) countdownDisplay.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                // Time's up, user didn't click "Stay Logged In"
            }
        }, 1000);
    }

    function hideWarningModal() {
        const modalElement = document.getElementById(warningModalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        if (countdownInterval) clearInterval(countdownInterval);
    }

    function redirectToLockScreen() {
        // Try to get username from a known element if available, otherwise server handles it.
        // This is just a best-effort for pre-filling. Server-side flash message is more reliable.
        // Example: const usernameElement = document.getElementById('current-username-display');
        // const username = usernameElement ? usernameElement.textContent : '';
        // if (username) {
        //    lockScreenUrl += '?username=' + encodeURIComponent(username);
        // }
        // For now, rely on server-side flash message for username.

        // Before redirecting, set a flash message or similar for the username.
        // This client-side script can't set a Yii session flash message directly.
        // The server-side actionLockScreen will use getFlash('lockscreen_username').
        // We can ping a URL to set the flash message if needed, or rely on last known identity.

        // Store current username in localStorage to be picked up by lock screen JS (less secure, but an option)
        // This is NOT a good practice for sensitive data, but for pre-filling username it might be acceptable by some.
        // For this implementation, we will rely on Yii's session flash data set by server on logout/timeout.

        window.location.href = '/site/lock-screen'; // Adjust URL if your app uses a different base path
    }

    function keepSessionAlive() {
        hideWarningModal();
        // Make a simple AJAX call to a server endpoint to extend the session
        // This endpoint should be lightweight and just touch the session.
        fetch('/site/keep-alive', { // Assuming Yii URL structure, create this dummy action
            method: 'POST',
            headers: {
                'X-CSRF-Token': yii.getCsrfToken() // Assuming Yii's CSRF token is available via yii.getCsrfToken()
            }
        })
        .then(response => {
            if (response.ok) {
                console.log('Session kept alive.');
                resetTimers(); // Reset inactivity timers
            } else {
                console.error('Failed to keep session alive.');
                redirectToLockScreen(); // If keep-alive fails, better to lock.
            }
        })
        .catch(error => {
            console.error('Error during keep-alive:', error);
            redirectToLockScreen(); // If error, better to lock.
        });
    }

    function resetTimers() {
        clearTimeout(activityTimer);
        clearTimeout(warningTimer);
        clearTimeout(modalTimer); // Also clear modal timer if it was set separately

        // Start the main timer for redirecting to lock screen
        activityTimer = setTimeout(redirectToLockScreen, actualTimeoutDuration);

        // Start a timer to show the warning modal
        // Ensure warningDuration is positive
        if (warningDuration > 0) {
            warningTimer = setTimeout(showWarningModal, warningDuration);
        }

        console.log('Session timers reset. Warning in ' + warningDuration/1000 + 's, Lock in ' + actualTimeoutDuration/1000 + 's.');
    }

    // Initial setup
    // Only run if user is not on login or lock-screen page already
    if (window.location.pathname.indexOf('/site/login') === -1 &&
        window.location.pathname.indexOf('/site/lock-screen') === -1 &&
        window.location.pathname.indexOf('/site/signup') === -1 && // Add other public pages
        document.body.classList.contains('logged-in')) { // Add a class 'logged-in' to body tag in main layout for logged-in users

        ['click', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(eventName) {
            document.addEventListener(eventName, resetTimers, { passive: true });
        });
        resetTimers(); // Start timers on page load
    }

})();
