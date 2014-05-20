<?php

namespace Heapstersoft\Base\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Route("/admin", name="_admin")
     * @Template()
     * 
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        return array();
    }
}
