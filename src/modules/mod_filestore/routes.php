<?php

$router->map('/dyn/image/:media_query',  ch('DynImg_Controller', 'generate'), 
        array('name'=>'dynamic_image'
            , 'filters' => array(
                    'media_query'=>'([^/]+)'
                    // 'param_string'=>'(\w+)'
                    // 'param_string'=>'\(([a-zA-Z0-9])+(,[a-zA-Z0-9]+)*\)+/'
                )
            )
);