<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Product;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $users = [];

        // Create 5 users
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $user->setName($faker->name);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Flush users first to ensure we have valid references?
        // (Not strictly necessary in Doctrine, but helpful for debugging)
        $manager->flush();

        // Create 20 products
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setTitle($faker->sentence(3));
            $product->setDescription($faker->paragraph(3));
            $product->setPrice(sprintf("%.2f", $faker->randomFloat(2, 5, 1000)));
            $product->setImage('https://picsum.photos/seed/' . $faker->uuid . '/800/600');
            
            $date = $faker->dateTimeBetween('-1 month', 'now');
            $product->setCreatedAt(\DateTimeImmutable::createFromMutable($date));
            
            // Assign a random user as owner
            $product->setOwner($users[array_rand($users)]);
            
            $manager->persist($product);
        }

        $manager->flush();
    }
}
