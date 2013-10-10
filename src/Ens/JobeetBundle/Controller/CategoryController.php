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
        $jobsPerPage = $this->container->getParameter('max_jobs_on_category');
        $lastPage = ceil($totalJobs / $jobsPerPage);
        $previousPage = $page > 1 ? $page - 1 : 1;
        $nextPage = $page < $lastPage ? $page + 1 : $lastPage;

        $category->setActiveJobs($em->getRepository('EnsJobeetBundle:Job')->getActiveJobs($category->getId(), $jobsPerPage, ($page - 1) * $jobsPerPage));

        $format = $this->getRequest()->getRequestFormat();

        return $this->render('EnsJobeetBundle:Category:show.'.$format.'.twig', array(
            'category' => $category,
            'lastPage' => $lastPage,
            'previousPage' => $previousPage,
            'currentPage' => $page,
            'nextPage' => $nextPage,
            'totalJobs' => $totalJobs,
            'feedId' => sha1($this->get('router')->generate('EnsJobeetBundle_category', array('slug' =>  $category->getSlug(), '_format' => 'atom'), true)),
        ));
    }
}