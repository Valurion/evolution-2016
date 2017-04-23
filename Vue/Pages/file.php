<?php

if(isset($contentType)){
    header("Content-type: ".$contentType);
}
if(isset($fileName)){
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
}

echo $datas;



