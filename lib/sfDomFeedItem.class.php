<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomFeedItem.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfDomFeedItem extends sfDomStorage
{
    protected $wrapped_object=null;
    function __construct($object=null)
    {
        parent::__construct($object);
        if(!is_null($object)
        {
            $this->wrapped_object=$object;
        }
    }
}
