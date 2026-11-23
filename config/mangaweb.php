<?php
return [
 'allow_registration'=>filter_var(env('ALLOW_REGISTRATION',true),FILTER_VALIDATE_BOOL),
 'allow_extension_management'=>filter_var(env('ALLOW_EXTENSION_MANAGEMENT',false),FILTER_VALIDATE_BOOL),
 'download_max_pages'=>(int)env('DOWNLOAD_MAX_PAGES',400),
 'download_max_image_bytes'=>(int)env('DOWNLOAD_MAX_IMAGE_BYTES',12582912),
];
