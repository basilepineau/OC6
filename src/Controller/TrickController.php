<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CommentMain;
use App\Entity\Trick;
use App\Form\CommentMainType;
use App\Form\TrickType;
use App\Service\ImageUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickController extends AbstractController
{
    private $entityManager;
    private $imageUploader;

    public function __construct(EntityManagerInterface $entityManager, ImageUploader $imageUploader)
    {
        $this->entityManager = $entityManager;
        $this->imageUploader = $imageUploader;
    }

    #[Route('/trick/new', name: 'app_trick_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ImageUploader $imageUploader): Response
    {
        $trick = new Trick();
        $trick->setCreatedAt(new \DateTimeImmutable()); 
        $trick->setUser($this->getUser());
        $form = $this->createForm(TrickType::class, $trick);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $pictures = $form->get('pictures')->getData();
            $videos = $form->get('videos')->getData();

            foreach ($pictures as $picture) {

                $file = $picture->getFile();
                $newFilename = $this->imageUploader->upload($file);

                $picture->setUrl($newFilename);
                
                $trick->addPicture($picture);
            }

            // Add videos to the trick
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }
    
            // Save the trick entity
            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash('success', 'Trick successfully created!');

    
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }
    
        return $this->render('trick/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    

    #[Route('/trick/{slug}', name: 'app_trick_show')]
    public function show(string $slug, Request $request): Response
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createNotFoundException('The trick does not exist.');
        }

        $commentMain = new CommentMain();
        $commentMain->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CommentMainType::class, $commentMain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentMain->setTrick($trick);

            $this->entityManager->persist($commentMain);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_trick_show', ['slug' => $slug]);
        }

        $commentMains = $this->entityManager->getRepository(CommentMain::class)->findByTrickOrderedByDate($trick->getId());
        
        $user = $this->getUser();

        return $this->render('trick/show.html.twig', ['trick' => $trick, 'commentMains' => $commentMains, 'user' => $user, 'form' => $form->createView()]);
    }

    #[Route('/trick/{slug}/edit', name: 'app_trick_edit')]
    public function edit($slug, Request $request): Response
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            throw $this->createNotFoundException('The trick does not exist');
        }

        $form = $this->createForm(TrickType::class, $trick, [
            'remove_pictures' => true,
            'remove_videos' => true
        ]);

        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Trick updated successfully.');
    
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trick/edit.html.twig', ['form' => $form->createView(), 'trick' => $trick, 'categories' => $categories]);
    }

    #[Route('/trick/{slug}/delete', name: 'app_trick_delete')]
    public function delete($slug, Request $request, ParameterBagInterface $params): Response
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            throw $this->createNotFoundException('The trick does not exist.');
        }

        if ($this->isCsrfTokenValid('delete'.$trick->getSlug(), $request->request->get('_token'))) {
            // Supprimer du serveur les images associées au trick du serveur
            $trickPictures = $trick->getPictures();
            foreach($trickPictures as $trickPicture) {
                $url = $trickPicture->getUrl();
                $uploadDirectory = $params->get('upload_directory');
                $entirePath = $uploadDirectory.'/'.$url;
                if (!file_exists($entirePath)){
                    throw new \Exception('File not found: ' . $entirePath);
                }
                if (!unlink($entirePath)) {
                    throw new \Exception('Failed to delete file: ' . $entirePath);
                }
            }

            $this->entityManager->remove($trick);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Trick deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_homepage');
    }
}
