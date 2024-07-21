<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        $repository = $this->entityManager->getRepository(Trick::class);
        $tricks = $repository->findAll();
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'tricks' => $tricks,
            'user' => $user
        ]);
    }
}
