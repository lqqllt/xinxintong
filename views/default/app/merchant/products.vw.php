<?php
$view['template'] = '/app';
$view['params']['app_title'] = TPL::val('title');
$view['params']['app_view'] = '/app/merchant/products';
$view['params']['css'] = array(array('/app/merchant', 'products'));
$view['params']['js'] = array(array('/app/merchant', 'products'));