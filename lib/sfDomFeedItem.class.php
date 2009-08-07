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
    function __construct($object=null,$data_array=null)
    {
        parent::__construct($object,$data_array);
        if(!is_null($object))
        {
            $this->wrapped_object=$object;
        }
    }
    public function isPermalink()
    {
        // for RSS implementation
        $guid = $this->getGuid();
        // todo: there needs to be a notion of binding guid and <link>
        return $guid && parse_url($guid,PHP_URL_SCHEME)!=FALSE;
    }
  public function genEnclosure(DOMElement $enclosure)
  {
    if(!$this->has('enclosure'))
    {
      $enclosure->parentNode->removeChild($enclosure);
    }
    else
    {
      $enclosure=$this->get('enclosure');
      // we're going to code this for rss only right now todo
      // we need subclasses of sfDomFeed to has itemFactories todo
      foreach($enclosure->attributes as $attr_name => $attr_node)
      {
        if($enclosure->has($attr_name))
        {
          $enclosure->setAttribute($attr_name,$enclosure->get($attr_name));
        }
        else
        {
          $enclosure->removeChild($attr_node);
        }
      }
    }

    return null; // modifying the DOMElement via pass by value
  }
}
