<?php
namespace Ens\JobeetBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ens\JobeetBundle\Entity\Category;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{
    public function showAction($slug, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('EnsJobeetBundle:Category')->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $totalJobs = $em->getRepository('EnsJobeetBundle:Job')->countActiveJobs($category->getId());


        $category->setActiveJobs($em->getRepository('EnsJobeetBundle:Job')->getActiveJobs());

        $format = $this->getRequest()->getRequestFormat();


        // Special pagination

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $em->getRepository('EnsJobeetBundle:Job')->getActiveJobs(),
            $this->get('request')->query->get('page', $page),
            10
        );

        return $this->render('EnsJobeetBundle:Category:show.'.$format.'.twig', array(
            'category' => $category,
            'currentPage' => $page,
            'totalJobs' => $totalJobs,
            'pagination' => $pagination,
            'feedId' => sha1($this->get('router')->generate('EnsJobeetBundle_category', array('slug' =>  $category->getSlug(), '_format' => 'atom'), true)),
        ));
    }
}