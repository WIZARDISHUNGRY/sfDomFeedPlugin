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
abstract class sfDomFeed extends sfDomStorage /* , sfDomFeedAbstraction */
{

    protected $dom; // DOMDocument
    protected $plugin_path;
    protected $items=Array();
    protected $family; // e.g. RSS or Atom
    protected $xpath_item; // XPath expression for feed item
    protected $xpath_channel; // for the root channel
    protected $decorate_rules = Array( // feed is global; item is foreach item
        'feed'=>Array(),'item'=>Array()); // xpath query=>transform (string or array-callback)

    protected $extensions; // instances of sfDomFeedExtension

    public function __construct($feed_array=array(),$extensions=array())
    {
        $version='1.0';
        parent::__construct(); /// we call initialize() later so we don't need to pass feed_array in yet
        $encoding='UTF-8'; // sensible default
        $dom=$this->dom=new DOMDocument($version,$encoding);
        $this->setEncoding($encoding); // needed to avoid trying to set encoding to ''
        $this->context=sfContext::getInstance();
        $this->plugin_path=realpath(dirname(__FILE__).'/../');

        $prefix='sfDomFeedExtension';
        $this->extensions=Array();
        foreach($extensions as $extension)
        {
            $class_name=$prefix.ucfirst($extension); // todo change this to camelize
            $extension = new $class_name;
            if(!$extension instanceof sfDomFeedExtension) throw new sfException("$class_name is not a sfDomFeedExtension");
            // unfortunately ^ class_exists() is useless here; it will error instead of exception.
            
            $this->extensions[]=$extension;
        }

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
            $this->items=$data_array['feed_items'];
            unset($data_array['feed_items']);
        }
        return parent::initialize($data_array);
    }

    // simple methods to preserve compat with sfFeed2Plugin

    public function asXml()
    {
        // I suppose presuming that we're emiting XML is a *little presumputous*

        // the following probably should be refactored
        // todo don't send encoding if we're going to send an html error message
        $this->context->getResponse()->setContentType('application/'.$this->family.'+xml; charset='.$this->getEncoding());
        $dom=$this->dom->cloneNode(TRUE); // may be expensive to do a deep clone
        return $this->decorateDom($dom)->saveXML();
    }

    /**
    * Retrieves the feed items.
    *
    * @return array an array of sfDomFeedItem objects
    */
    public function getItems()
    {
        return $this->items;
    }
 
    /**
    * Defines the items of the feed.
    *
    * Caution: in previous versions, this method used to accept all kinds of objects.
    * Now only objects of class sfDomFeedItem are allowed.
    *
    * @param array an array of sfDomFeedItem objects
    *
    * @return sfFeed the current sfFeed object
    */
    public function setItems($items = array())
    {
        $this->items = array();
        $this->addItems($items);
    
        return $this;
    }
 
    /**
    * Adds one item to the feed.
    *
    * @param sfDomFeedItem an item object
    *
    * @return sfFeed the current sfFeed object
    */
    public function addItem($item)
    {
        if (!($item instanceof sfDomFeedItem))
        {
        // the object is of the wrong class
        $error = 'Parameter of addItem() is not of class sfDomFeedItem';
    
        throw new sfDomFeedException($error);
        }
        //$item->setFeed($this);
        // not sure we need this 
        $this->items[] = $item;
    
        return $this;
    }
    
    /**
    * Adds several items to the feed.
    *
    * @param array an array of sfDomFeedItem objects
    *
    * @return sfFeed the current sfFeed object
    */
    public function addItems($items)
    {
        if(is_array($items))
        {
        foreach($items as $item)
        {
            $this->addItem($item);
        }
        }
    
        return $this;
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
        $dom->encoding=$this->getEncoding();
        $xp=new DOMXPath($dom);
        $channel = $xp->query($this->xpath_channel);
        $channel = $channel->item(0);
        $this->decorate($this,$channel,$this->fetchRulesFeed());

        $item_nodes = $xp->query($this->xpath_item);

        if(count($item_nodes)!=1)
            throw new sfDomFeedException('XPath query of '.$this->family.
                ' template for feed item got an unexpected (!1) number of feed items: '.count($items));

        $template_item_node=$item_nodes->item(0);
        $items_parent=$template_item_node->parentNode;
        $items_parent->removeChild($template_item_node);
        $items=Array(); // holds dom nodes until they can be readded (simplifies xpath expressions)
        $item_rules=$this->prependItemXpath($this->fetchRulesItem(),$this->xpath_item);

        foreach($this->items as $feed_item)
        {
            $node = $template_item_node->cloneNode(TRUE);
            $items_parent->appendChild($node);
            $feed_item->decorate($this,$node,$item_rules);
            $items_parent->removeChild($node); // so the xpath expressions for template items work identically in this context
            $items[]=$node; // we could do some kind of sort key here todo
        }
        foreach($items as $node)
            $items_parent->appendChild($node); // readd them to the dom
        

        return $dom;
    }

    protected function prependItemXpath($rules,$prefix)
    {
        // prepend an xpath expression to each of the decorate rules
        $prepended_rules=array();
        foreach($rules as $xpath => $rule)
        {
            $prepended_rules[$prefix.$xpath]=$rule;
        }
        return $prepended_rules;
    }

    public function setEncoding($encoding)
    {
        // done to synchornize with wrapped DOMDocument
        $this->dom->encoding=$encoding;
        parent::setEncoding($encoding);
    }
    public function genUrl(DOMElement $url)
    {
      return sfContext::getInstance()->getController()->genUrl($url->textContent,true);
    }

  /*
            sfDomFeedAbstraction methods
  */
  public function fetchRulesItem()
  {
    $rules = sfMixer::callMixins();
    if(!is_array($rules)) throw new sfDomFeedException("assertation failed");
    foreach($this->extensions as $extension)
      $rules = array_merge($rules,$extension->fetchRulesItem());
    return $rules;
  }
  public function fetchRulesFeed()
  {
    $rules = sfMixer::callMixins();
    if(!is_array($rules)) throw new sfDomFeedException("assertation failed ".gettype($rules));
    foreach($this->extensions as $extension)
      $rules = array_merge($rules,$extension->fetchRulesFeed());
    return $rules;
  }
}

sfMixer::register('sfDomFeed',Array('sfDomFeedAbstraction','fetchRulesFeed'));
sfMixer::register('sfDomFeed',Array('sfDomFeedAbstraction','fetchRulesItem'));
