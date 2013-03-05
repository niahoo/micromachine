<?php


$router->map('/login',  ch('Login_Controller'), array('name'=>'mod_signup_login'));
$router->map('/logout',  ch('Login_Controller', 'logout'), array('name'=>'mod_signup_logout'));
$router->map('/signup',  ch('Signup_Controller'), array('name'=>'mod_signup_signup'));
$router->map('/profiles/new',  ch('Profile_Controller','new_profile'), array('name'=>'mod_signup_new_profile'));

$router->map('/signup/lol',  ch('Signup_Controller'));
$router->map('/signup/lol',  ch('Signup_Controller'));
$router->map('/account',  ch('Account_Controller'), array('name'=>'user_account'));

// FORM PROCESSES

$router->map('/signup/process',  ch('Signup_Controller', 'process_email'),
        array('name'=>'mod_signup_signup_process'
           ,  'methods' => 'POST'   ));
$router->map('/login/process',  ch('Login_Controller', 'process_login'),
        array('name'=>'mod_signup_login_process'
           ,  'methods' => 'POST'   ));
$router->map('/profiles/new/process',  ch('Profile_Controller', 'process_new'),
        array('name'=>'mod_signup_new_profile_process'
           ,  'methods' => 'POST'   ));
$router->map('/account/change/password',  ch('Password_Controller', 'process_change'),
        array('name'=>'mod_signup_password_change'
           ,  'methods' => 'POST'   ));




