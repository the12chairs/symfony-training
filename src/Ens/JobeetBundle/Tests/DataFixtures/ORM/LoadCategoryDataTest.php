<?php
namespace Ens\JobeetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Ens\JobeetBundle\Entity\Category;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $design = new Category();
        $design->setName('DesignTest');

        $programming = new Category();
        $programming->setName('ProgrammingTest');

        $manager = new Category();
        $manager->setName('ManagerTest');

        $administrator = new Category();
        $administrator->setName('AdministratorTest');

        $em->persist($design);
        $em->persist($programming);
        $em->persist($manager);
        $em->persist($administrator);

        $em->flush();

        $this->addReference('category-design', $design);
        $this->addReference('category-programming', $programming);
        $this->addReference('category-manager', $manager);
        $this->addReference('category-administrator', $administrator);
    }

    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}