<?php
namespace Ens\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ens\JobeetBundle\Entity\Job;

use Ens\JobeetBundle\Form\JobType;
use Doctrine\ORM\Mapping;


/*
 * Without FOS/Rest solution
 */

class ApiController extends Controller
{
    public function listAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $jobs = array();

        $rep = $em->getRepository('EnsJobeetBundle:Affiliate');
        $affiliate = $rep->getForToken($token);

        if(!$affiliate) {
            throw $this->createNotFoundException('This affiliate account does not exist!');
        }

        $rep = $em->getRepository('EnsJobeetBundle:Job');
        $active_jobs = $rep->getActiveJobs(null, null, null, $affiliate->getId());

        foreach ($active_jobs as $job) {
            $jobs[$this->get('router')->generate('ens_job_show', array('company' => $job->getCompanySlug(), 'location' => $job->getLocationSlug(), 'id' => $job->getId(), 'position' => $job->getPositionSlug()), true)] = $job->asArray($request->getHost());
        }

        $format = $request->getRequestFormat();
        $jsonData = json_encode($jobs);

        if ($format == "json") {
            $headers = array('Content-Type' => 'application/json');
            $response = new Response($jsonData, 200, $headers);

            return $response;
        }

        return $this->render('EnsJobeetBundle:Api:jobs.' . $format . '.twig', array('jobs' => $jobs));
    }


    // Make api for single job
    public function showAction(Request $request, $token, $id)
    {
        $em = $this->getDoctrine()->getManager();


        $rep = $em->getRepository('EnsJobeetBundle:Affiliate');
        $affiliate = $rep->getForToken($token);

        if(!$affiliate) {
            throw $this->createNotFoundException('This affiliate account does not exist!');
        }

        $rep = $em->getRepository('EnsJobeetBundle:Job');
        $job = $rep->getActiveJob($id);

        $job  = $job->asArray($request->getHost());

        $format = $request->getRequestFormat();
        $jsonData = json_encode($job);




        if ($format == "json") {
            $headers = array('Content-Type' => 'application/json');
            $response = new Response($jsonData, 200, $headers);

            return $response;
        }

        return $this->render('EnsJobeetBundle:Api:jobs.' . $format . '.twig', array('job' => $job));
    }




    /**
     * JSON POST a job
     */
    public function createAction()
    {
        $entity  = new Job();
        $request = $this->getRequest();
        $data = json_decode($request);
        //return $data;
        //var_dump($data);
        //exit;
        $form = $this->createForm(new JobType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ens_job_preview', array(
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'token' => $entity->getToken(),
                'position' => $entity->getPositionSlug()
            )));
        }

        return $this->render('EnsJobeetBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }




}
