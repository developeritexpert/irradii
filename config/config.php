<?php
//include("config_local.php"); 
include("config_web.php"); 

date_default_timezone_set('America/Los_Angeles');

//define("GOOGLE_API_KEY", "ABQIAAAA3cNWER9yJ_-fm4TXRVdSuRRudkRJZqUjV1PzHXjuatro6-uXkxS32osDY3UcwsoAfV8DqOV8jH976A");
define("GOOGLE_API_KEY", "ABQIAAAA3cNWER9yJ_-fm4TXRVdSuRR6xNr4J1O1jmLIKlg5WxwTmHz3qRRlF3EKVliIdGbdOw05Wgn4jzKPYw");
define("ROOT_BASE_FLASH", ROOT_BASE."flash/");
if(!defined("HTTPS_DIRECTORY")) define("HTTPS_DIRECTORY",HTTP_BASE."members/");
define("ROOT_SELECT_OPTION", ROOT_BASE."select_option/"); 
define("FULL_MEMBERS_BASE_DIRECTORY", ROOT_BASE."members/");  
define("FULL_MEMBERS_SECURE", HTTPS_BASE."members/"); 
define("PAYPAL_RECURRING_URL","https://www.paypal.com/cgi-bin/webscr");
//define("PAYPAL_RECURRING_URL",FULL_MEMBERS_DIRECTORY."thankyou.php");
$check_for_http=  isset($_SERVER['HTTPS'])?$_SERVER['HTTPS']:'';
if($check_for_http=='')
{
    define("FULL_MEMBERS_DIRECTORY", HTTP_BASE."members/");
    define("HTTP_BASE_IMAGES",HTTP_BASE."images/");
    define("HTTP_BASE_FLASH",HTTP_BASE."flash/");
    define("FULL_MEMBERS_IMAGES", HTTP_BASE."members/images/");
    define("PROPERTY_THUMB_IMAGES", HTTP_BASE."members/property_thumbnail/");
    define("PROPERTY_MIDDLE_IMAGES", HTTP_BASE."members/property_middle/");
    define("MERBER_THUMB_IMAGES", HTTP_BASE."members/member_thumbnail_image/");
    define("MERBER_MIDDLE_IMAGES", HTTP_BASE."members/member_middle_image/");
    define("FULL_ADMIN_IMAGES", HTTP_BASE."admin/images/");
    define("SELECT_OPTION", HTTP_BASE."select_option/");
}
else if($check_for_http=="on")
{
    define("FULL_MEMBERS_DIRECTORY", HTTPS_BASE."members/");
    define("HTTP_BASE_IMAGES",HTTPS_BASE."images/");
    define("HTTP_BASE_FLASH", HTTPS_BASE."flash/");
    define("FULL_MEMBERS_IMAGES", HTTPS_BASE."members/images/");
    define("PROPERTY_THUMB_IMAGES", HTTP_BASE."members/property_thumbnail/");
    define("PROPERTY_MIDDLE_IMAGES", HTTP_BASE."members/property_middle/");
    define("MERBER_THUMB_IMAGES", HTTP_BASE."members/member_thumbnail_image/");
    define("MERBER_MIDDLE_IMAGES", HTTP_BASE."members/member_middle_image/");
    define("SELECT_OPTION", HTTPS_BASE."select_option/");
} else {
    define("FULL_MEMBERS_DIRECTORY", HTTP_BASE."members/");
}
define("KEYWORD_BASE_DIRECTORY", "/");
define("FULL_ADMIN_DIRECTORY", HTTP_BASE."admin/");
define("ROOT_ADMIN_DIRECTORY", ROOT_BASE."admin/");
define("KEYWORD_MEMBERS_DIRECTORY", "/members/");
define("CURRENCY", "$");
define("HTACCESS", "No");
define("EMAIL_FROM", "property@propertyhookup.com"); //EMAIL ADDRESS THAT DEBUGGING INFORMATION IS SENT FROM
define("COMPANY", "PropertyHookUp");
define("COMPANY_NAME", "PropertyHookUp.com");
//define("CURRENCY","$");

@set_magic_quotes_runtime(0);
//session_start();
require_once(ROOT_BASE."include/database_function.php");
$connect_DB_Object = new db_functions();
// example  $connect_DB_Object->
$xFormarray = $_POST;
$xFormarray += $_GET;
//if(!isset($_GET['page'])){
    $page = 1;
//} else {
//    $page = $_REQUEST['page'];
//}
//if($_SESSION['rate_page'] != ''){
//    $page = $_SESSION['rate_page'];
//}

$max_results="30";
$from = (($page * $max_results) - $max_results);
if($from <= 0){$from=0;}
$max_property_results=20;
$from_for_property = (($page * $max_property_results) - $max_property_results);
if($from_for_property <= 0){$from_for_property=0;}

$max_result_buyer = "3";
$from_buyer = ((($page * $max_result_buyer) - $max_result_buyer) > 0) ? (($page * $max_result_buyer) - $max_result_buyer) : 0;

?>


