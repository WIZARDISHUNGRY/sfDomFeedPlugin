<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRssDomFeed.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfRssDomFeed extends sfDomFeed 
{
    public function __construct($feed_array=array(),$version='1.0',$encoding='UTF-8')
    {
        $this->family='rss';
        $this->xpath_item='/rss/channel/item';
        $this->xpath_channel='/rss/channel[1]';
        $this->decorate_rules = Array(
            'feed' => Array(
                '/rss/attribute::version'  => "2.0",
                '/rss/channel/lastBuildDate'  =>
                    Array(create_function('$obj','$d=new DateTime();return $d;')),
                '/rss/channel/link' =>
                     Array($this,'genUrl'),
            ),
            'item' => Array(
                '/guid/@isPermaLink' =>
                     Array('item','isPermalink'),
                '/link' =>
                     Array($this,'genUrl'),
            ),
        );

        parent::__construct($feed_array=array(),$version='1.0',$encoding='UTF-8');
        // if we move the xpath expression expansion in the parent constructor
        // this function won't have to be called last todo
    }
    public function serializeDateTime(DateTime $d)
    {
        return $d->format(DATE_RSS);
    }
    public function genUrl(DOMElement $url)
    {
      // this is here to wrap the call to sfContext -- probably move into sfDomFeed todo
      return sfContext::getInstance()->getController()->genUrl($url->textContent,true);
    }
}
