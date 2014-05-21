<?php

namespace Heapstersoft\Base\AdminBundle\Vich;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Description of IdNamer
 *
 * @author Tabaré Caorsi <tabare@heapstersoft.com>
 */
class IdNamer implements \Vich\UploaderBundle\Naming\NamerInterface
{
    public function name($obj, PropertyMapping $mapping)
    {
        $newFname = md5(uniqid().$obj->getId()).".".$obj->getImageObj()->guessExtension();
        return $newFname;
    }
}

?>
