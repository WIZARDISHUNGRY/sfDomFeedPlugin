<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProxyParameterHolder allows one to place an existing object on top of
 * a ParameterHolder class. get and set requests matching object methods will 
 * be passed to the object. NB: NOT ALL METHODS in sfParameterHolder may
 * be implemented/function correctly.
 *
 * @package    sfProxyParameterHolder
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfProxyParameterHolder extends sfParameterHolder
{
    protected $wrapped_object;
    public function __construct($object=null)
    {
        $this->wrapped_object=$object;
    }

    public function & get($name,$default=null)
    {
        $val = $this->proxy_get($name);
        if($val!==NULL) // FALSE errors will return here -- see proxy_get()
            return $val;
        return parent::get($name,$default);
    }

    public function set($name, $value)
    {
        $this->proxy_set($name,$value);
        parent::set($name,$value);
    }

    public function has($name)
    {
        return parent::has($name) || $this->proxy_has($name);
    }

    protected function proxy_has($name,$set=false)
    {
        if(!$this->isWrapped()) return null;
        $method_name=($set?'set':'get').sfInflector::camelize($name); // todo work with Doctrine style gets/sets
        $cb=Array($this->wrapped_object,$method_name);
        if(is_callable($cb))
            return $cb;
        else
            return null;
    }

    protected function proxy_set($name,$value)
    {
        $callback=$this->proxy_has($name,true);
        if($callback)
        {
            call_user_func($callback,$value);
            // ^ can't catch errors
            return true;
        }
        else
            return false;        
    }


    protected function proxy_get($name)
    {
        $callback=$this->proxy_has($name);
        if($callback)
            return call_user_func($callback); // NB: returns false on error -- can't really distinguish between error and legit FALSE
        else
            return null;
    }

    public function isWrapped()
    {
        return $this->wrapped_object!=null;
    }
}
