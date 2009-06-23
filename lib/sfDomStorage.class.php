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
    protected $storage = array(); // generic datastore

    public function __call($name, $arguments)
    {
        if(strstr($name,'get')===0)
        {
            $key=strtolower(preg_replace('/^get/','',$name,1));
            return $this->storage[$key]; //todo allow backfetching
        }
        elseif(strstr($name,'set')===0 && array_key_exists(0,$arguments))
        {
            $key=strtolower(preg_replace('/^set/','',$name,1));
            $this->storage[$key]=$arguments[0];
        }
    }

    public function initialize($data_array)
    {
        foreach($data_array as $k=>$v)
            $this->storage[strtolower($k)]=$v;
        return $this;
    }

 }
