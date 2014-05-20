<?php

namespace Heapstersoft\Base\AdminBundle\Vich;

/**
 * Description of IdNamer
 *
 * @author Tabaré Caorsi <tabare@heapstersoft.com>
 */
class IdNamer implements \Vich\UploaderBundle\Naming\NamerInterface
{
    public function name($obj, $field)
    {
        $newFname = md5(uniqid().$obj->getId()).".".$obj->getImageObj()->guessExtension();
        return $newFname;
    }
}

?>
