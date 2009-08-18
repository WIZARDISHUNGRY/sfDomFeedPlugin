<?php
/**
 * sfFeedEnclosure.
 *
 * @package    sfDomFeed
 */
class sfDomFeedEnclosure extends sfDomStorage
{

  public function has($k)
  {
    $h=parent::has($k);
    echo "@ has($k) = $h @\n";
    return $h;
  }
}
