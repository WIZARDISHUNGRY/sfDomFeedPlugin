<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomFeedExtensionDummy.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfDomFeedExtensionDummy extends sfDomFeedExtension
{
  public function __construct()
  { 
      $this->decorate_rules = Array(
          'feed' => Array(
              '/rss/attribute::version'  => "2.1", // testing to make sure this works
          ),
          'item' => Array(
          ),
      );
  }
}
