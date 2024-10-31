<?php
session_start();

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function check_session_timeout($timeout = 1800)
{ // 30 minutes default
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Session has expired
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
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
