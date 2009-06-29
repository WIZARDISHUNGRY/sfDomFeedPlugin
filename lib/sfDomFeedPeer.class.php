<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomFeedPeer.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfDomFeedPeer
{
    /**
    * Populates a feed with items based on objects.
    * Inspects the available methods of the objects to populate items properties.
    */
    public static function convertObjectsToItems($objects, $options = array())
    {
        $items = array();
        foreach($objects as $object)
        {
            $item = new sfDomFeedItem($object);
        }
    }
}
