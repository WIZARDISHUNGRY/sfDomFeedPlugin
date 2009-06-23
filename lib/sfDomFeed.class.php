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
abstract class sfDomFeed extends sfDomStorage
{

    protected $dom; // DOMDocument
    protected $plugin_path;
    protected $feed_item;
    protected $family; // e.g. RSS or Atom
    protected $xpath_item; // XPath expression for feed item
    protected $xpath_channel; // for the root channel
    protected $template_feed_item; // DOMNode template of post
    protected $storage = array(); // generic datastore

    public function __construct($feed_array=null,$version='1.0',$encoding='UTF-8')
    {
        $dom=$this->dom=new DOMDocument($version,$encoding);
        $this->context=sfContext::getInstance();
        $this->plugin_path=realpath(dirname(__FILE__).'/../');

        if($feed_array)
        {
            $this->initialize($feed_array);
        }

        if(! $dom->load($this->getFamilyTemplatePath(),LIBXML_NOERROR))
            throw new sfDomFeedException("DOMDocument::load failed");

        $xp=new DOMXPath($dom);
        $items = $xp->query($this->xpath_item);

        if(count($items)!=1)
            throw new sfDomFeedException('XPath query of '.$this->family.
                ' template for feed item got an unexpected (!1) number of feed items: '.count($items));

        $item=$items->item(0);
        $item->parentNode->removeChild($item);

        $this->template_feed_item=$item;
    }

    // simple methods to preserve compat with sfFeed2Plugin

    public function toXml()
    {
        $dom=$this->dom->cloneNode(TRUE); // may be expensive
        return $this->decorateDom($dom)->saveXML();
    }

    public function fromXml($string)
    {
        throw new sfDomFeedException('Not implemented');
    }

    // protected methods
    
    protected function getFamilyTemplatePath()
    {
        return $this->plugin_path."/data/templates/".$this->family.'.xml'; // todo make name more canonical with a prefix "root-rss"
    }

    protected function decorateDom(DOMDocument $dom)
    {
        $xp=new DOMXPath($dom);
        $channel = $xp->query($this->xpath_channel);
        $channel = $channel->item(0);
        for ($i = 0; $i < $channel->childNodes->length; $i++)
        {
            $node=$channel->childNodes->item($i);
            $key=strtolower($node->nodeName);
            if(array_key_exists($key,$this->storage)) // register mapping stuff here todo
            {
                while($node->hasChildNodes())
                    $node->removeChild($node->childNodes->item(0));

                $node->appendChild($dom->createTextNode($this->storage[$key])); // should be a way of doing this for other stuff todo
            }
        }

        foreach($this->storage as $feed_item)
        {
            $node = $this->template_feed_item->cloneNode(TRUE);
            $channel->appendChild($node);
        }

        return $dom;
    }
}
