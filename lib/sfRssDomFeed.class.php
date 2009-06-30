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
    protected $decorate_rules = Array(
        'feed' => Array(
            '/rss/attribute::version'  => "2.0",
            '/rss/channel/lastBuildDate'  =>
                Array('$d=new DateTime();return $d->format(DATE_RSS);'), // can't use create function in this context! yay!
                    // also obv should be able to set a object serialization functions (but not here)
        ),
        'item' => Array(
           // '::isPermalink' => 
        ),
    );
}
