<?php

namespace App\Controller;

use App\Entity\CommentMain;
use App\Entity\Trick;
use App\Form\CommentMainType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrickController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/trick/{id}', name: 'app_trick_show')]
    public function show($id, Request $request): Response
    {
        $repository = $this->entityManager->getRepository(Trick::class);
        $trick = $repository->find($id);

        if (!$trick) {
            throw $this->createNotFoundException('The trick does not exist.');
        }

        $commentMain = new CommentMain();
        $commentMain->setCreatedAt(new \DateTimeImmutable());
        // $commentMain->setAuthor($user);

        $form = $this->createForm(CommentMainType::class, $commentMain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentMain->setTrick($trick);

            $this->entityManager->persist($commentMain);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_trick_show', ['id' => $id]);
        }

        return $this->render('trick/show.html.twig', ['trick' => $trick, 'form' => $form->createView()]);
    }
}
