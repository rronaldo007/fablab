<?php

namespace App\DataFixtures;

use App\Entity\Edition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EditionFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        $edition1 = new Edition();
        $edition1->setName('Winter 2024');
        $edition1->setStartDate($now->modify('-6 months'));
        $edition1->setEndDate($now->modify('-3 months'));
        $edition1->setCurrent(false); // ❌ not current
        $manager->persist($edition1);

        $edition2 = new Edition();
        $edition2->setName('Spring 2025');
        $edition2->setStartDate($now->modify('-1 week'));
        $edition2->setEndDate($now->modify('+2 months'));
        $edition2->setCurrent(true); // ✅ current
        $manager->persist($edition2);

        $manager->flush();
    }

}
