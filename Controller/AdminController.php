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
abstract class AdminController extends Controller
{
    protected $bundleName = null;
    protected $entityName = null;
    protected $formType = null;
    protected $actionTitles = array();
    protected $listFields = array();
    protected $showFields = array();
    protected $routePrefix = '';

    public function __construct()
    {
        $this->listFields = $this->setupListFields();
        $this->showFields = $this->setupShowFields();
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    { 
        $templateString = $this->resolveTemplateString('index');
        $actionTitle = $this->getActionTitle('list', 'List');
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository($this->bundleName.':'.$this->entityName);
        $dql = "SELECT x FROM ".$this->bundleName.':'.$this->entityName." x";

        if(method_exists($repository, 'getListDql'))
        {
            $dql = $repository->getListDql();
        }

        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );

        //$entities = $em->getRepository($this->bundleName.':'.$this->entityName)->findAll();
       
        return $this->render(
            $templateString, 
            array('pagination'=>$pagination,
                  'action_title'=>$actionTitle,
                  'listFields'=>$this->listFields) +
            $this->getAllRoutes()
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction()
    { 
        $templateString = $this->resolveTemplateString('new');
        
        $actionTitle = $this->getActionTitle('new', 'Creation');
        $entity = new $this->entityClass();
        $form   = $this->createForm(new $this->formType(), $entity);
        
        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'form'   => $form->createView(),
                  'action_title'=>$actionTitle) +
            $this->getAllRoutes()
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function createAction(Request $request)
    { 
        $templateString = $this->resolveTemplateString('new');
        $routes = $this->getAllRoutes();
        $actionTitle = $this->getActionTitle('new', 'Creation');
        $entity = new $this->entityClass();
        $form   = $this->createForm(new $this->formType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->trans("Created Sucessfully!"));
            
            return $this->redirect($this->generateUrl($routes['list_route'], array('id' => $entity->getId())));
        }

        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'form'   => $form->createView(),
                  'action_title'=>$actionTitle)  +
            $routes
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editAction($id)
    { 
        $templateString = $this->resolveTemplateString('edit');
        $actionTitle = $this->getActionTitle('edit', 'Edition');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->bundleName.':'.$this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$this->entityName.' entity.');
        }
        
        $form   = $this->createForm(new $this->formType(), $entity);
        
        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'form'   => $form->createView(),
                  'action_title'=>$actionTitle) +
            $this->getAllRoutes()
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    { 
        $templateString = $this->resolveTemplateString('edit');
        $routes = $this->getAllRoutes();
        $actionTitle = $this->getActionTitle('edit', 'Edition');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->bundleName.':'.$this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$this->entityName.' entity.');
        }
        
        $editForm   = $this->createForm(new $this->formType(), $entity);
        $editForm->bind($request);
        
        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->trans("Your changes were saved!"));
            
            return $this->redirect($this->generateUrl($routes['edit_route'], array('id' => $id)));
        }
      
        
        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'form'   => $editForm->createView(),
                  'action_title'=>$actionTitle) +
            $routes
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function showAction($id)
    { 
        $templateString = $this->resolveTemplateString('show');
        $actionTitle = $this->getActionTitle('show', 'Details');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->bundleName.':'.$this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$this->entityName.' entity.');
        }
        
        $deleteForm = $this->createDeleteForm($id);
        
        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'show_fields'=>$this->showFields,
                  'action_title'=>$actionTitle,
                  'delete_form'=>$deleteForm->createView()) +
            $this->getAllRoutes()
        );
    }
    
    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    { 
        $templateString = $this->resolveTemplateString('show');
        $routes = $this->getAllRoutes();
        $actionTitle = $this->getActionTitle('delete', 'Details');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->bundleName.':'.$this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$this->entityName.' entity.');
        }
        
        $deleteForm = $this->createDeleteForm($id);
        
        if($request->isMethod('post'))
        {
            $deleteForm->bind($request);

            if ($deleteForm->isValid()) {
                $em->remove($entity);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', $this->trans('Deleted succesfully!'));
                return $this->redirect($this->generateUrl($routes['list_route']));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'An error was found while trying to delete.');
            }
            
            
        }
        
        return $this->render(
            $templateString, 
            array('entity' => $entity,
                  'show_delete'=>true,
                  'show_fields'=>$this->showFields,
                  'action_title'=>$actionTitle,
                  'delete_form'=>$deleteForm->createView()) +
            $routes
        );
    }
    
    protected function getActionTitle($actionName)
    {
        $title = "";
        if(!isset($this->actionTitles[$actionName]))
        {
            $title = ucwords(strtolower($actionName.' '.$this->entityName));
        }
        else
        {
            $title = $this->actionTitles[$actionName];
        }
        
        return $this->trans($title);
    }

    protected function resolveTemplateString($template)
    {
        $templateString = 'AdminBundle:Admin:'.$template.'.html.twig';
        $template = $this->getRequest()->attributes->get('_template');
        if ($template != null)
        {
            $templateString = $template->get('bundle') . ':' .
                    $template->get('controller') . ':' .
                    $template->get('name') . '.' . $template->get('format') . '.' . $template->get('engine')
            ;
        }
        
        return $templateString;
    }
    
    protected function setupListFields()
    {
        return array('id'=>'Id');
    }
    
    protected function setupShowFields()
    {
        return array('id'=>'Id');
    }
    
    protected function getAllRoutes()
    {
        $listRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_index';
        $createRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_create';
        $showRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_show';
        $deleteRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_delete';
        $editRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_edit';
        $updateRoute = '_admin_'.strtolower($this->bundleName).$this->routePrefix.'_update';
        
        return array('update_route'=>$updateRoute,
                    'delete_route'=>$deleteRoute,
                    'list_route'=>$listRoute,
                    'edit_route'=>$editRoute,
                    'show_route'=>$showRoute,
                    'create_route'=>$createRoute,
                  
        );
    }
    
    protected function trans($message, $vals = array(), $domain = 'messages')
    {
        $t = $this->get('translator')->trans(
            $message,
            $vals,
            $domain
        );

        return $t;
    }
    
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
