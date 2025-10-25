<?php
function flash($msg = null){
    if($msg === null){
        if(isset($_SESSION['flash'])){ $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; }
        return null;
    }
    $_SESSION['flash'] = $msg;
}
