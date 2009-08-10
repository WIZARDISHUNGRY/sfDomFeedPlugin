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
  public function genEnclosure(DOMElement $enclosure_node)
  {
    if(!$this->has('enclosure'))
    {
      $enclosure_node->parentNode->removeChild($enclosure_node);
    }
    else
    {
      $enclosure=$this->get('enclosure');
      // we're going to code this for rss only right now todo
      // we need subclasses of sfDomFeed to has itemFactories todo
      $remove=Array(); // list of attribute nodes to remove after loop -- php needs iterators
      $add=Array(); // worst
      for($i=0;$i<$enclosure_node->attributes->length;$i++)
      {
        $attr=$enclosure_node->attributes->item($i);
        if($enclosure->has($attr->name))
        {
          $add[$attr->name]=$enclosure->get($attr->name);
        }
        else
        {
          $remove[]=$attr->name;
        }
      }
      foreach($remove as $name)$enclosure_node->removeAttribute($name);
      foreach($add as $name=>$value)$enclosure_node->setAttribute($name,$value);
    }

    return null; // modifying the DOMElement via pass by value
  }
}
