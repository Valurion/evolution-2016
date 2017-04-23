<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if($_SERVER['SERVER_NAME'] == 'www.cairn-int.info' || $_SERVER['SERVER_NAME'] == 'www.localcairn.int'){
    include 'static/maintenance/index-int.html';
}else{
    include 'static/maintenance/index.html';
}