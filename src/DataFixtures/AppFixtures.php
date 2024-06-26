<?php

namespace App\DataFixtures;

use App\Factory\CommentMainFactory;
use App\Factory\CommentResponseFactory;
use App\Factory\PictureFactory;
use App\Factory\TrickFactory;
use App\Factory\UserFactory;
use App\Factory\VideoFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        // Générer des utilisateurs
        $users = UserFactory::createMany(10);
        // Créer 10 tricks en utilisant les utilisateurs créés
        $tricks = TrickFactory::createMany(10, function() use ($users) {
            return [
                'author' => $users[array_rand($users)], // Associer aléatoirement un utilisateur existant à chaque trick
            ];
        });

        foreach ($tricks as $trick) {
            PictureFactory::createMany(mt_rand(1, 5), ['trick' => $trick]);
            VideoFactory::createMany(mt_rand(1, 5), ['trick' => $trick]);
            $commentMains = CommentMainFactory::createMany(mt_rand(0, 10), ['trick' => $trick]);
            foreach ($commentMains as $commentMain) {
                CommentResponseFactory::createMany(mt_rand(0, 4), ['commentMain' => $commentMain]);
            }
       };


        $manager->flush();
    }
}
