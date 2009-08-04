<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomFeedExtension.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
abstract class sfDomFeedAbstraction
{

    protected $family; // e.g. RSS or Atom // todo does this go here or in sfDomFeed?
    protected $decorate_rules = Array( // feed is global; item is foreach item
        'feed'=>Array(),'item'=>Array()); // xpath query=>transform (string or array-callback)
}
