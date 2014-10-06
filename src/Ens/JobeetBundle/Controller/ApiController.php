<?php
namespace Ens\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Query;
use Doctrine\ORM\NoResultException;

/**
 * Each entity controller must extends this class.
 *
 * @abstract
 */
abstract class ApiController extends Controller {

    /**
     * This method should return the entity's repository.
     *
     * @abstract
     * @return EntityRepository
     */
    abstract function getRepository();

    /**
     * This method should return a new entity instance to be used for the "create" action.
     *
     * @abstract
     * @return Object
     */
    abstract function getNewEntity();

    /**
     * Base "list" action.
     *
     * @return JsonResponse
     */
    protected function listAction() {
        $list = $this->getRepository()
            ->createQueryBuilder('e')
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);

        return new JsonResponse($list);
    }

    /**
     * Base "read" action.
     *
     * @param int $id
     * @return JsonResponse|NotFoundHttpException
     */
    protected function readAction($id) {
        $entityInstance = $this->getEntityForJson($id);
        if (false === $entityInstance) {
            return $this->createNotFoundException();
        }

        return new JsonResponse($entityInstance);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    protected function createApiAction() {
        $json = $this->getJsonFromRequest();
        if (false === $json) {
            throw new \Exception('Invalid JSON');
        }

        $object = $this->updateEntity($this->getNewEntity(), $json);
        if (false === $object) {
            throw new \Exception('Unable to create the entity');
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        return new JsonResponse($this->getEntityForJson($object->getId()));
    }

    /**
     * @param $id
     * @return JsonResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     */
    protected function updateApiAction($id) {
        $object = $this->getEntity($id);
        if (false === $object) {
            return $this->createNotFoundException();
        }

        $json = $this->getJsonFromRequest();
        if (false === $json) {
            throw new \Exception('Invalid JSON');
        }

        if (false === $this->updateEntity($object, $json)) {
            throw new \Exception('Unable to update the entity');
        }

        $this->getDoctrine()->getManager()->flush($object);

        return new JsonResponse($this->getEntityForJson($object->getId()));
    }

    /**
     * @param $id
     * @return JsonResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function deleteApiAction($id) {
        $object = $this->getEntity($id);
        if (false === $object) {
            return $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return new JsonResponse(array());
    }

    /**
     * Returns an entity from its ID, or FALSE in case of error.
     *
     * @param int $id
     * @return Object|boolean
     */
    protected function getEntity($id) {
        try {
            return $this->getRepository()->find($id);
        }
        catch (NoResultException $ex) {
            return false;
        }

        return false;
    }

    /**
     * Returns an entity from its ID as an associative array, or FALSE in case of error.
     *
     * @param int $id
     * @return array|boolean
     */
    protected function getEntityForJson($id) {
        try {
            return $this->getRepository()->createQueryBuilder('e')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->getQuery()->getSingleResult(Query::HYDRATE_ARRAY);
        }
        catch (NoResultException $ex) {
            return false;
        }

        return false;
    }

    /**
     * Returns the request's JSON content, or FALSE in case of error.
     *
     * @return string|boolean
     */
    protected function getJsonFromRequest() {
        $json = $this->get("request")->getContent();
        if (!$json) {
            return false;
        }

        return $json;
    }

    /**
     * Updates an entity with data from a JSON string.
     * Returns tphe entity, or FALSE in case of error.
     *
     * @param Object $entity
     * @param string $json
     * @return Object|boolean
     */
    protected function updateEntity($entity, $json) {
        $data = json_decode($json);
        if ($data == null) {
            return false;
        }

        foreach ($data as $name => $value) {
            if ($name != 'id') {
                $setter = 'set' . ucfirst($name);
                if (method_exists($entity, $setter)) {
                    call_user_func_array(array($entity, $setter), array($value));
                }
            }
        }

        return $entity;
    }
}
