<?php
/**
 * simple-uploader.php 1.0
 */

$accept_tokens = array("!!CHANGEME!!");
$default_dir = ".";

error_reporting(E_ERROR);
header("Content-Type: application/json; charset=UTF-8");

if (isset($_GET["token"]))
{
    $_POST["token"] = $_GET["token"];
}
$add_random = isset($_GET["addRandom"]);
$random_length = isset($_GET["randomLength"]) ? intval($_GET["randomLength"]) : 5;
$target_dir= isset($_GET["targetDir"]) ? $_GET["targetDir"] : $default_dir;

function randomize_filename($original_filename): string
{
    global $random_length;
    $random = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, $random_length);
    if (str_contains($original_filename, ".")) {
        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        $filename = pathinfo($original_filename, PATHINFO_FILENAME);
        return $filename . "-" . $random . "." . $ext;
    } else {
        return $original_filename . "-" . $random;
    }
}

if (isset($_POST["token"]) && in_array($_POST["token"], $accept_tokens)) {
    if (isset($_GET["del"]))
    {
        if (file_exists($_GET["del"]))
        {
            unlink($_GET["del"]);
            http_response_code(200);
            $json = ["status" => "success", "message" => "file deleted"];
        } else {
            http_response_code(404);
            $json = ["status" => "error", "message" => "file not found"];
        }
    } else
    {
        $file = $_FILES["upload"];
        $filename = $add_random ? randomize_filename($file["name"]) : $file["name"];
        error_log($file["tmp_name"]);
        if (move_uploaded_file($file["tmp_name"], realpath($target_dir) . DIRECTORY_SEPARATOR . $filename)) {
            $json = ["status" => "ok", "url" => $target_dir == "." ? $filename : $target_dir . "/" . $filename];
            http_response_code(200);
        } else {
            error_log("bruh");
            $json = ["status" => "error", "message" => "upload failed. does the folder exist with proper permissions?"];
            http_response_code(500);
        }
    }
} else {
    $json = ["status" => "error", "message" => "token " . $_POST["token"] . " is not valid"];
    http_response_code(400);
}
echo json_encode($json);
