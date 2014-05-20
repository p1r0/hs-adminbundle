<?php

namespace Heapstersoft\Base\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 *
 */
class AboutController extends Controller
{
    
    /**
     * @Route("/about", name="_admin_adminbundle_about")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        return array('phpversion'=>phpversion());
    }

}
