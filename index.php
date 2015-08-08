<?php

define('ROOT_DIR', realpath(__DIR__));
define('SHORT_URL_LENGTH', 8); // 52 ^ 8 = 53459728531456 combinations. [a-zA-Z]
require_once('config.php');
require_once('db.php');

app();

function app() {       
    $uri_arr = explode('?', $_SERVER['REQUEST_URI']);
    $uri = $uri_arr[0];
    if ($uri == '/') {        
        ob_start();
        require(ROOT_DIR .'/view/main.php');
        $content = ob_get_clean();
        require(ROOT_DIR . '/layout.php');        
        return;
    } else if (strpos($_SERVER['REQUEST_URI'], '/ajax_make_short_url') === 0) {
        ajax_make_short_url();
        return;
    }

    $is_short_url = is_known_short_url($_SERVER['REQUEST_URI'], $redirect_url);
    if (!$is_short_url) {
        require_once(ROOT_DIR . '/404.php');
        return;
    }

    //defalut behavior
    make_redirect($redirect_url);
    return;
}

/**
 * checks if $url is known short URL, stored in a database.
 * sets $redirect_url, if found.
 *
 * @return bool
 */
function is_known_short_url($url, &$redirect_url) {    
    global $pdo;
    $short_url = ltrim($url, '/');
    $stmt = $pdo->prepare('SELECT long_url FROM urls WHERE short_url=?');    
    $stmt->execute(array($short_url));
    $long_url = $stmt->fetchColumn();
    if ($long_url) {
        $redirect_url = $long_url;        
        return true;
    }
    return false;
}

function make_redirect($redirect_url) {
    header("Location: ". $redirect_url);
    die;
}

function ajax_make_short_url() {
    $response = array();
    $response['error'] = array();
    if (!isset($_REQUEST['long_url'])) {
        $response['error'][] = 'long_url not defined';
        echo json_encode($response); die;
    } 
    $short_url = make_short_url($_REQUEST['long_url'], $response['error']);
    if (!$short_url) {
        $response['error'][] = 'Sorry, we were unable to make short URL.';
        echo json_encode($response); die;
    }

    $response['short_url'] = $short_url;

    echo json_encode($response); die;
}

/**
 * Creates unique short URL and stores it in database
 *
 * @return short URL; Or NULL on error;
 */
function make_short_url($long_url, &$error) {
    global $pdo;

    $url = $long_url;

    preg_match('%^[a-z]+://%', $url, $matches);
    if (!isset($matches[0])) {
        $url = 'http://' . $url;
    }
    
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
        $error[] = 'not a valid URL.';
        return null;
    }

    //check if such long URL already in database
    $stmt = $pdo->prepare('SELECT short_url FROM urls WHERE long_url=?');
    $stmt->execute(array($url));
    $short_url = $stmt->fetchColumn();    
    if (!$short_url) { //create record
        $short_url = generate_short_url();

        try {
            $stmt = $pdo->prepare('INSERT INTO urls(long_url, short_url, created) VALUES (?, ?, ?)');
            $params = array($url, $short_url, date('Y-m-d H:i:s'));
            $stmt->execute($params);
        } catch (Exception $e) {
            $error[] = $e->getMessage();
            return null;
        }
    }

    $result = 'http://' . rtrim($_SERVER['HTTP_HOST'], '/') . '/' . $short_url;
    return $result;
}

/**
 * generates unique short url. (Not URL but URI actually)
 *
 * @return string;
 */
function generate_short_url() {
    global $pdo;

     //we can deviate this set. So no ASCII ranges, please.
    $chars_set = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $set_length = strlen($chars_set);
    $result_url = '';
    for ($i=0; $i < SHORT_URL_LENGTH; $i++) {
        $rand_pos = mt_rand(0, $set_length - 1);
        $char = substr($chars_set, $rand_pos, 1);
        $result_url .= $char;
    }

    //now, need to check, if URL not preserved in db earlier. Though, the probability is low...
    $rows_found_num = $pdo->query("SELECT COUNT(id) FROM urls WHERE short_url='{$result_url}'")->fetchColumn();
    if ($rows_found_num) { //very unlikely
        $result_url = generate_short_url();
    }

    return $result_url;
}

function pr($val) {
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}

function vd($val) {
    echo '<pre>';
    var_dump($val);
    echo '</pre>';
}
