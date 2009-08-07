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
abstract class sfDomStorage
{
    protected $storage; // sfParameterHolder, generic datastore

    public function __construct($object=null,$data_array=array())
    {
        // semantically which should happen first here?
        // perhaps we should test $storage to see if any of these are overriding it
        $this->storage=new sfProxyParameterHolder($object);
        $this->initialize($data_array);
    }

    public function __call($name, $arguments)
    {
        if(strpos($name,'get')===0)
        {
            $key=strtolower(preg_replace('/^get/','',$name,1));
            return $this->storage->get($key); //todo allow backfetching?
        }
        elseif(strpos($name,'set')===0 && array_key_exists(0,$arguments))
        {
            $key=strtolower(preg_replace('/^set/','',$name,1));
            return $this->storage->set($key,$arguments[0]); // forgot the return here
        }
        elseif(strpos($name,'has')===0)
        {
            $key=strtolower(preg_replace('/^has/','',$name,1));
            return $this->storage->has($key);
        }
        return sfMixer::callMixins(); // We're going to need mixin support
    }

    public function has($name)
    {
      return $this->storage->has($name);
    }

    public function get($name)
    {
      return $this->storage->get($name);
    }

    public function set($name,$value)
    {
      return $this->storage->set($name,$value);
    }

    public function initialize($data_array)
    {
        $this->storage->add($data_array);
        return $this;
    }

    protected function decorate(sfDomStorage $root, DOMNode $node,$rules=Array())
    {
        $dom=$node->ownerDocument;
        $xp=new DOMXPath($dom);
        for ($i = 0; $i < $node->childNodes->length; $i++)
        {
            $child=$node->childNodes->item($i);
            if($child instanceof DOMElement)
            {
                $key=strtolower($child->nodeName);
                if($this->storage->has($key)) // register mapping stuff here todo
                {
                    while($child->hasChildNodes())
                        $child->removeChild($child->childNodes->item(0));

                    $child->appendChild($dom->createTextNode($this->storage->get($key)));
                    // should be a way of doing this for other stuff todo
                }
            }
        }
        
        foreach($rules as $xpath_expr => $rule)
        {
            $nodes = @$xp->query($xpath_expr);
            if(! $nodes instanceof DOMNodeList)
                throw new sfDomFeedException('Invalid XPath expression -- '.$xpath_expr);

            // fixme how do we deal with no matches here
            if($nodes->length==0)
                throw new sfDomFeedException('Warning: nothing matched XPath expression -- '.$xpath_expr.
                    ', document follows: '.$dom->saveXML());


            for($i = 0; $i < $nodes->length; $i++)
            {
                $rule_node=$nodes->item($i);
                if(is_array($rule))
                {
                    // rule is a callback array -- first argument has a few special values; see parseCallback();
                    $value=call_user_func_array($this->parseCallback($rule,$root),Array(&$rule_node));
                    // callback takes the node to be decorated byRef so we decorate in the callback
                    
                    $value=$this->serializeObject($value);
                    // RSS, Atom, etc can define their own serializers; useful for DateTime etc.

                    if($value)
                    {
                        $rule_node->nodeValue=$value;
                    }
                }
                else
                {
                    // rule is a string literal
                    $rule_node->nodeValue=$rule;
                }
            }
        }

        return $node;
    }

    protected function parseCallback($rule,sfDomStorage $root)
    {
        if(count($rule)==2)
        {
            list($object,$method)=$rule;
            if(is_string($object)&&$method)
            {
                switch($object)
                {
                    case 'item': //rename this?
                        $object=$this;
                        break;
                    case 'feed':
                        $object=$root;
                        break;
                    default:
                        throw new sfDomFeedException("callback-style DOM rule has an unknown named object -- "
                            ."$xpath_expr => $object->$method()");
                        break;
                }
            }

            if(!is_object($object))
            {
                throw new sfDomFeedException("DOM rule object is not an object -- "
                    ."$xpath_expr => " . gettype($object) );
            }

            if(!is_string($method))
            {
                throw new sfDomFeedException("DOM rule method is not a string -- "
                    ."$xpath_expr => $object->$method()");
            }

            $cb = Array($object,$method);
        }
        elseif(count($rule)==1)
        {
            // callback is a function (passed as an array of a single item where the item is a function name)
            $cb=$rule[0];
        }
        else
        {
            $size=count($rule);
            throw new sfDomFeedException("DOM rule callback structure had unexpected size -- $xpath_expr => ($size)");
        }

        if(!is_callable($cb))
        {
            throw new sfDomFeedException("DOM rule callback is not callable -- "
                ."$xpath_expr => ".(count($rule)==2?"$object->$method()":"$cb()"));
        }

        return $cb;
    }

    public function serializeObject($object)
    {
        // not really an object but any type
        // not really serialization but "converstion to xml character data"

        $type = is_object($object)?get_class($object):gettype($object); // I know PHP.net warns about this but bite me.
        // this doesn't behave the way things should with inherited obj types

        $method='serialize'.sfInflector::camelize($type);

        if(method_exists($this,$method))
        {
            return call_user_func(Array($this,$method),$object);
        }
        else
        {
            return $object; // implict string conversion will do
        }

        return;
    }
    public function serializeBoolean($v)
    {
        return $v?'true':'false';
    }
 }
