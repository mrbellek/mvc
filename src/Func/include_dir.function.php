<?php
//includes all files in a folder
function include_dir($dir) {
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            $ext            = explode('.', $entry);
            $ext            = end($ext);

            if($entry != '..' && $entry != '.' && $ext == 'php' && is_readable($dir . '/' . $entry)) {
                include_once($dir . '/' . $entry);
            }
        }
    }
} 
