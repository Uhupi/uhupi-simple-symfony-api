<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Tasks;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 200; $i++) {
            $task = new Tasks();
            $task->setTitle("Task $i");
            $task->setDescription("Lass uns eine Beschreibung für Task $i definieren");
            
            $statuses = ['pending', 'in_progress', 'completed'];
            $task->setStatus($statuses[array_rand($statuses)]);
            
            $dueDate = (new \DateTime())->modify("+" . rand(1, 365) . " days");
            $task->setDueDate($dueDate);
            
            $task->setCreatedAt(new \DateTimeImmutable());
            $task->setUpdatedAt(new \DateTime());
            
            $manager->persist($task);
        }

        $manager->flush();
    }
}
