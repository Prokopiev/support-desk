<?php
    $DR = realpath((dirname(__FILE__).'/../'));
    if (!$DR) die("error get PWD");

    const CACHE_TTL = 3600;

    include($DR.'/xmlrpc.config.php');
    include($DR.'/memcached.config.php');

    $client = new xmlrpc_client('/', $apiHost, 80);
    $client->return_type = 'xmlrpcvals';
    $msg = new xmlrpcmsg('admin.getMiscUrls');

    $p1 = new xmlrpcval($apiKey, 'string');
    $msg->addparam($p1);

    $res =& $client->send($msg, 0, 'http11');

    if ($res->faultcode())
        echo '<!--'.$res->faultString().'-->';
    else
        $miscUrls = php_xmlrpc_decode($res->value());

    foreach($miscUrls as $id=>$item)
    {
        if($item['id']=='logoRegistration')
            $memcached->set('logoRegistration', $item['value'], CACHE_TTL);
    }

    $settings = array(
        'title',
        'siteName',
        'outerHostName',
    );
    foreach($settings as $setting)
    {
        $client = new xmlrpc_client('/', $apiHost, 80);
        $client->return_type = 'xmlrpcvals';
        $msg = new xmlrpcmsg('admin.getParam');

        $p1 = new xmlrpcval($apiKey, 'string');
        $msg->addparam($p1);

        $p2 = new xmlrpcval($setting, 'string');
        $msg->addparam($p2);

        $res =& $client->send($msg, 0, 'http11');

        $param = '';

        if ($res->faultcode())
            echo $res->faultString();
        else
            $param = php_xmlrpc_decode($res->value());

        $memcached->set($setting, iconv('UTF-8','CP1251',$param), CACHE_TTL);
    }
?>