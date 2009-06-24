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

    public function __construct($feed_array=null,$version='1.0',$encoding='UTF-8')
    {
        parent::_construct();
        $dom=$this->dom=new DOMDocument($version,$encoding);
        $this->context=sfContext::getInstance();
        $this->plugin_path=realpath(dirname(__FILE__).'/../');

        if($feed_array)
        {
            $this->initialize($feed_array);
        }

        if(! $dom->load($this->getFamilyTemplatePath(),LIBXML_NOERROR))
            throw new sfDomFeedException("DOMDocument::load failed");
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
        $this->decorate($channel);

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
            $feed_item->decorate($node);
            $items_parent->appendChild($node);
        }

        return $dom;
    }
}
