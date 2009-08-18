<?php

/*
 * This file is part of the sfDomFeedPlugin package.
 * (c) 2009 Jon Williams <jwilliams@limewire.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProxyParameterHolder adds support for synonyms of common words
 * @package    sfSynonymParameterHolder
 * @author     Jon Williams <jwilliams@limewire.com>
 */
class sfSynonymParameterHolder extends sfProxyParameterHolder
{
  protected static $dictionary=null; // second sfParameterHolder instance!
  public function __construct($object=null)
  {
    if(is_null(self::$dictionary))
    {
      self::$dictionary=new sfParameterHolder();
      self::$dictionary->add($this->loadDictionary());
    }
    $this->wrapped_object=$object;
  }

  public function loadDictionary()
  {
   $plugin_path=realpath(dirname(__FILE__).'/../'); 
    return Array(
      'length'=>Array(
        'size',
      ),
    );
  }


  public function has($name)
  {
    if(parent::has($name) return true;
    foreach(self::$dictionary->get($name,Array() as $name)
    {
      if(parent::has($name)) return true;
    }
    return false;
  }
}
