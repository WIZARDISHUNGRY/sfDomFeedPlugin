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
  protected $family='rss';
  protected $xpath_item='/rss/channel/item';
  protected $xpath_channel='/rss/channel[1]';

  public function __construct($feed_array=array(),$extensions=array())
  { 
      parent::__construct($feed_array,$extensions);
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
              '/enclosure' =>
                  Array('item','genEnclosure')
          ),
      );
  }
  public function serializeDateTime(DateTime $d)
  {
      return $d->format(DATE_RSS);
  }
}
