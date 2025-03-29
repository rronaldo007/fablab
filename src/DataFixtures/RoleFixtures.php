<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Define roles and their descriptions, including the super user role
        $rolesData = [
            ['role_name' => 'candidate', 'description' => 'User who applies as candidate.'],
            ['role_name' => 'administrator', 'description' => 'User who administers the platform.'],
            ['role_name' => 'jury', 'description' => 'User who evaluates candidates.'],
            ['role_name' => 'super_administrator', 'description' => 'User with full system privileges.'],
        ];

        foreach ($rolesData as $data) {
            $role = new Role();
            $role->setRoleName($data['role_name']);
            $role->setDescription($data['description']);
            $manager->persist($role);
        }

        $manager->flush();
    }
}
