<?php

 $needed_extensions = array('curl',  '... other extionsions to check');
    $missing_extensions = array();
    foreach ($needed_extensions as $needed_extension) {
        if (!extension_loaded($needed_extension)) {
            $missing_extensions[] = $needed_extension;
        }
    }
    if (count($missing_extensions) > 0) {
        echo 'This software needs the following extensions, please install/enable them: ' . implode(', ', $missing_extensions) . PHP_EOL;
        exit(1);
    }

?>