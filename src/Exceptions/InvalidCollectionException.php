<?php
/** */
namespace AnrDaemon\Exceptions;

/** Invalid cache key exception

*/
class InvalidCollectionException
extends \InvalidArgumentException
implements \Psr\SimpleCache\InvalidArgumentException
{

}
