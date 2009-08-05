<?php

require_once(dirname(__FILE__).'/../bootstrap/unit.php');

require_once(dirname(__FILE__).'/../../lib/sfDomFeed.class.php');
require_once(dirname(__FILE__).'/../../lib/sfRssDomFeed.class.php');

$t = new lime_test(0, new lime_output_color());

$feed_params = array(
  'title' => 'foo', 
  'link' => 'http://bar', 
  'description' => 'foobar baz',
  'language' => 'fr', 
  'authorName' => 'francois',
  'authorEmail' => 'francois@toto.com',
  'authorLink' => 'http://francois.toto.com',
  'subtitle' => 'this is foo bar',
  'categories' => array('apples', 'oranges'),
  'feedUrl' => 'http://www.example.com',
  'encoding' => 'UTF-16',
  'feed_items' => Array(new sfDomFeedItem(null,Array
    (
        'link'=>'http://wowthisworks.example.com/',
    )
  ),
  new sfDomFeedItem(null,Array
    (
        'link'=>'http://dotwowork.example.com/',
    )
  )
  ),
);


$feed=new sfRssDomFeed($feed_params,array('dummy'));
echo $feed->asXml();
