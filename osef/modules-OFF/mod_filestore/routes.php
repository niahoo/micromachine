<?php

$router->map('GET', '/dyn/image/:media_query', ch('DynImg_Controller','generate'), 'dynamic_image');
