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
    protected $feed_items=Array();
    protected $family; // e.g. RSS or Atom
    protected $xpath_item; // XPath expression for feed item
    protected $xpath_channel; // for the root channel
    protected $decorate_rules = Array( // feed is global; item is foreach item
        'feed'=>Array(),'item'=>Array()); // xpath query=>transform (string or array-callback)

    public function __construct($feed_array=array(),$version='1.0',$encoding='UTF-8')
    {
        parent::__construct(); /// we call initialize() later so we don't need to pass feed_array in yet
        $dom=$this->dom=new DOMDocument($version,$encoding);
        $this->context=sfContext::getInstance();
        $this->plugin_path=realpath(dirname(__FILE__).'/../');

        if($feed_array)
        {
            $this->initialize($feed_array);
        }

        if(! $dom->load($this->genTemplatePath(),LIBXML_NOERROR))
            throw new sfDomFeedException("DOMDocument::load failed");
    }

    public function initialize($data_array)
    {
        // special cases -- should be refactored to elsewhere?
        if(array_key_exists('feed_items',$data_array))
        {
            $this->feed_items=$data_array['feed_items'];
            unset($data_array['feed_items']);
        }
        return parent::initialize($data_array);
    }

    // simple methods to preserve compat with sfFeed2Plugin

    public function toXml()
    {
        $dom=$this->dom->cloneNode(TRUE); // may be expensive to do a deep clone
        return $this->decorateDom($dom)->saveXML();
    }

    public function fromXml($string)
    {
        throw new sfDomFeedException('Not implemented');
    }

    // protected methods
    
    protected function genTemplatePath()
    {
        return $this->plugin_path."/data/templates/".$this->family.'.xml'; // todo make name more canonical with a prefix "root-rss"
    }

    protected function decorateDom(DOMDocument $dom)
    {
        $xp=new DOMXPath($dom);
        $channel = $xp->query($this->xpath_channel);
        $channel = $channel->item(0);
        $this->decorate($channel,$this->decorate_rules['feed']);

        $item_nodes = $xp->query($this->xpath_item);

        if(count($item_nodes)!=1)
            throw new sfDomFeedException('XPath query of '.$this->family.
                ' template for feed item got an unexpected (!1) number of feed items: '.count($items));

        $template_item_node=$item_nodes->item(0);
        $items_parent=$template_item_node->parentNode;
        $items_parent->removeChild($template_item_node);

        foreach($this->feed_items as $feed_item)
        {
            $node = $template_item_node->cloneNode(TRUE);
            $feed_item->decorate($node,$this->decorate_rules['item']);  // todo: parsing this once per item is SLOW 
            $items_parent->appendChild($node);
        }

        return $dom;
    }
}
