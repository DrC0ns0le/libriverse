<?php
session_start();

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        $current_page = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?redirect=$current_page");
        exit();
    }
}

function check_session_timeout($timeout = 1800)
{ // 30 minutes default
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Session has expired
        session_unset();
        session_destroy();
        $current_page = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?expired=1&redirect=$current_page");
        exit();
    }
    // Update last activity time stamp
    $_SESSION['last_activity'] = time();
}

// Call this function at the beginning of pages that require login
function init_authenticated_session()
{
    require_login();
    check_session_timeout();
}

function logout()
{
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php?logout=1");
    exit();
}
