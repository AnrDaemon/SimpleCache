<?php
/** */
namespace AnrDaemon\Exceptions;

/** Invalid cache key exception

*/
class InvalidKeyException
extends \InvalidArgumentException
implements \Psr\SimpleCache\InvalidArgumentException
{

}
