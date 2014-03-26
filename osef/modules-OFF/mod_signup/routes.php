<?php


$router->map('GET', '/login',  ch('Login_Controller'), 'mod_signup_login');
$router->map('GET', '/logout',  ch('Login_Controller', 'logout'), 'mod_signup_logout');
$router->map('GET', '/signup',  ch('Signup_Controller'), 'mod_signup_signup');
$router->map('GET', '/profiles/new',  ch('Profile_Controller','new_profile'), 'mod_signup_new_profile');

$router->map('GET', '/account',  ch('Account_Controller'), 'user_account');

// FORM PROCESSES

$router->map('POST', '/signup/process',  ch('Signup_Controller', 'process_email'), 'mod_signup_signup_process');
$router->map('POST', '/login/process',  ch('Login_Controller', 'process_login'), 'mod_signup_login_process');
$router->map('POST', '/profiles/new/process',  ch('Profile_Controller', 'process_new'), 'mod_signup_new_profile_process');
$router->map('POST', '/account/change/password',  ch('Password_Controller', 'process_change'), 'mod_signup_password_change');


