<?php
$view['template'] = '/app';
$view['params']['app_title'] = '稿件编辑';
$view['params']['app_view'] = '/app/contribute/initiate/article';
$view['params']['global_js'] = array('angular-route.min','bootstrap.min','ui-bootstrap.min','ui-bootstrap-tpls.min','tinymce/tinymce.min','ui-tms','matters-xxt');
$view['params']['global_css'] = array('tms');
$view['params']['css'] = array(array('/app/contribute/initiate', 'article'));
$view['params']['js'] = array(array('/app/contribute', 'base'), array('/app/contribute/initiate', 'article'));
