<?php

namespace App\DataFixtures;

use App\Entity\TelegramUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TelegramUserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');
        $users = [];

        for ($i = 1; $i <= 1000; $i++) {
            $user = new TelegramUser();

            $user->setChatId(100000000 + $i);
            $user->setUsername($faker->userName());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setIsActive(true);
            $user->setIsAdmin(false);
            $user->setParticipant($faker->boolean(70));
            $user->setReferralLink('ref_' . uniqid());
            $user->setCreatedAt(
                $faker->dateTimeBetween('-30 days', 'now')
            );

            $manager->persist($user);
            $users[] = $user;
        }

        foreach ($users as $user) {
            if (rand(1, 100) <= 60) {
                $referrer = $users[array_rand($users)];
                if ($referrer !== $user) {
                    $user->setReferrer($referrer->getId());
                }
            }
        }

        $manager->flush();
    }
}
