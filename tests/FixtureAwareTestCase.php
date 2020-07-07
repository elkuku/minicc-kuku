<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

/**
 * Class FixtureAwareTestCase
 *
 * @package App\Tests
 */
abstract class FixtureAwareTestCase extends DataFixtureTestCase
{
    private ORMExecutor $fixtureExecutor;
    private ContainerAwareLoader $fixtureLoader;

    protected function setUp(): void
    {
        static::bootKernel();
        parent::setUp();
    }

    /**
     * Adds a new fixture to be loaded.
     */
    protected function addFixture(FixtureInterface $fixture): void
    {
        $this->getFixtureLoader()->addFixture($fixture);
    }

    /**
     * Executes all the fixtures that have been loaded so far.
     */
    protected function executeFixtures(): void
    {
        $this->getFixtureExecutor()->execute(
            $this->getFixtureLoader()->getFixtures()
        );
    }

    /**
     * Get the class responsible for loading the data fixtures.
     * And this will also load in the ORM Purger which purges the database before loading in the data fixtures
     */
    private function getFixtureExecutor(): ORMExecutor
    {
        if (!$this->fixtureExecutor) {
            /** @var EntityManager $entityManager */
            $entityManager = static::$kernel->getContainer()->get('doctrine')
                ->getManager();
            $this->fixtureExecutor = new ORMExecutor(
                $entityManager,
                new ORMPurger($entityManager)
            );
        }

        return $this->fixtureExecutor;
    }

    /**
     * Get the Doctrine data fixtures loader
     */
    private function getFixtureLoader(): ContainerAwareLoader
    {
        if (!$this->fixtureLoader) {
            $this->fixtureLoader = new ContainerAwareLoader(
                static::$kernel->getContainer()
            );
        }

        return $this->fixtureLoader;
    }
}
