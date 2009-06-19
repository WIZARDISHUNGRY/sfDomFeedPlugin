<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomFeed.
 *
 * @package    sfDomFeed
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfDomFeed extends DOMDocument
{

    public function __construct($feed_array=null;$version='1.0',$encoding='UTF-8')
    {
        parent::__construct($version,$encoding);
        if($feed_array)
        {
            $this->initialize($feed_array);
        }
    }

    public function initialize($feed_array)
    {
    }

}
