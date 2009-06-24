<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomStorage.
 *
 * @package    sfDomStorage
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfDomStorage
{
    protected $storage; // sfParameterHolder, generic datastore

    public function __construct()
    {
        $this->storage=new sfParameterHolder();
    }

    public function __call($name, $arguments)
    {
        if(strstr($name,'get')===0)
        {
            $key=strtolower(preg_replace('/^get/','',$name,1));
            return $this->storage->get($key); //todo allow backfetching?
        }
        elseif(strstr($name,'set')===0 && array_key_exists(0,$arguments))
        {
            $key=strtolower(preg_replace('/^set/','',$name,1));
            $this->storage->set($key,$arguments[0]);
        }
        elseif(strstr($name,'has')===0)
        {
            $key=strtolower(preg_replace('/^has/','',$name,1));
            return $this->storage->has($key);
        }
    }

    public function initialize($data_array)
    {
        foreach($data_array as $k=>$v)
            $this->storage[strtolower($k)]=$v;
        return $this;
    }

    public function decorate(DOMNode $node)
    {
        $dom=$node->ownerDocument;
        for ($i = 0; $i < $node->childNodes->length; $i++)
        {
            $child=$node->childNodes->item($i);
            $key=strtolower($child->nodeName);
            if(array_key_exists($key,$this->storage)) // register mapping stuff here todo
            {
                while($child->hasChildNodes())
                    $child->removeChild($child->childNodes->item(0));

                $child->appendChild($dom->createTextNode($this->storage[$key]));
                // should be a way of doing this for other stuff todo
            }
        }
       return $node;
    }
 }
