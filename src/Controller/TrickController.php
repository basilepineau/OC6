<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CommentMain;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CommentMainType;
use App\Form\TrickType;
use App\Service\PictureService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickController extends AbstractController
{
    private $entityManager;
    private $pictureService;

    public function __construct(EntityManagerInterface $entityManager, PictureService $pictureService)
    {
        $this->entityManager = $entityManager;
        $this->pictureService = $pictureService;
    }

    #[Route('/trick/new', name: 'app_trick_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, PictureService $pictureService): Response
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
                $newFilename = $this->pictureService->upload($file);

                $picture->setUrl($newFilename);
                
                $trick->addPicture($picture);
            }

            foreach ($videos as $video) {
                $url = $video->getUrl();

                // On ne récupère que la partie variable du lien d'une vidéo (paramètre 'v' de l'url)
                $queryString = parse_url($url, PHP_URL_QUERY);
                parse_str($queryString, $params);
                $videoId = $params['v'] ?? null;
        
                $video->setUrl($videoId); 
                $trick->addVideo($video);
            }
    
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
            $commentMain->setUser($this->getUser());

            $this->entityManager->persist($commentMain);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your comment has been posted !');

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

    #[Route('/trick/{slug}/edit-comment-main/{id}', name: 'app_trick_edit_comment_main', methods: ['POST'])]
    public function editCommentMain($slug, $id, Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }

        $comment = $this->entityManager->getRepository(CommentMain::class)->find($id);
        if (!$comment || $comment->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The comment does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }

        $text = $request->request->get('text');
        $comment->setText($text);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => 'comment updated successfully.',
            'text' => $text,
            'id' => $comment->getId()
        ], Response::HTTP_OK);

        return new JsonResponse(['error' => 'Invalid data provided.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/trick/{slug}/delete-comment-main/{id}', name: 'app_trick_delete_comment_main', methods: ['POST', 'DELETE'])]
    public function deleteCommentMain($slug, $id, Request $request, ParameterBagInterface $params): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }
    
        $comment = $this->entityManager->getRepository(CommentMain::class)->find($id);
        if (!$comment || $comment->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The comment does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }
    
        if ($this->isCsrfTokenValid('delete-comment-main' . $comment->getId(), $request->request->get('_token'))) {
    
            // Supprimer l'entité Picture
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
    
            return new JsonResponse(['success' => 'The comment has been deleted successfully.'], Response::HTTP_OK);
        } else {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }
    }

    #[Route('/trick/{slug}/add-picture', name: 'app_trick_add_picture', methods: ['POST'])]
    public function addPicture($slug, Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('picture');

        $newFilename = $this->pictureService->upload($file);

        $picture = new Picture();
        $picture->setUrl($newFilename);
        $trick->addPicture($picture);

        $this->entityManager->persist($picture);
        $this->entityManager->flush();

        // Generate a new CSRF token for the delete action
        $csrfToken = $csrfTokenManager->getToken('delete-picture' . $picture->getId())->getValue();


        return new JsonResponse([
            'success' => 'Picture added successfully.',
            'url' => $newFilename,
            'id' => $picture->getId(),
            'deleteUrl' => '/trick/'.$trick->getSlug().'/delete-picture/'. $picture->getId(),
            'csrfToken' => $csrfToken
        ], Response::HTTP_OK);
    }

    #[Route('/trick/{slug}/edit-picture/{id}', name: 'app_trick_edit_picture', methods: ['POST'])]
    public function editPicture($slug, $id, Request $request, ParameterBagInterface $params, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }

        $picture = $this->entityManager->getRepository(Picture::class)->find($id);
        if (!$picture || $picture->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The picture does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }

        if ($request->files->get('picture')) {
            // Delete the old picture from the server
            $uploadDirectory = $params->get('upload_directory');
            $oldPicturePath = $uploadDirectory . '/' . $picture->getUrl();
            if (file_exists($oldPicturePath)) {
                unlink($oldPicturePath);
            }

            // Upload the new picture
            $newFilename = $this->pictureService->upload($request->files->get('picture'));
            $picture->setUrl($newFilename);

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => 'Picture updated successfully.',
                'url' => $newFilename,
                'id' => $picture->getId(),
                'csrfToken' => $csrfTokenManager->getToken('delete-picture' . $picture->getId())->getValue()
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Invalid data provided.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/trick/{slug}/delete-picture/{id}', name: 'app_trick_delete_picture', methods: ['POST', 'DELETE'])]
    public function deletePicture($slug, $id, Request $request, ParameterBagInterface $params): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }
    
        $picture = $this->entityManager->getRepository(Picture::class)->find($id);
        if (!$picture || $picture->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The picture does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }
    
        if ($this->isCsrfTokenValid('delete-picture' . $picture->getId(), $request->request->get('_token'))) {
            // Supprimer l'image du serveur
            $url = $picture->getUrl();
            $uploadDirectory = $params->get('upload_directory');
            $entirePath = $uploadDirectory . '/' . $url;
    
            if (file_exists($entirePath)) {
                if (!unlink($entirePath)) {
                    return new JsonResponse(['error' => 'Failed to delete the file from the server.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return new JsonResponse(['error' => 'File not found: ' . $entirePath], Response::HTTP_NOT_FOUND);
            }
    
            // Supprimer l'entité Picture
            $this->entityManager->remove($picture);
            $this->entityManager->flush();
    
            return new JsonResponse(['success' => 'The picture has been deleted successfully.'], Response::HTTP_OK);
        } else {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }
    }

    #[Route('/trick/{slug}/add-video', name: 'app_trick_add_video', methods: ['POST'])]
    public function addVideo($slug, Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }

        $videoUrl = $request->request->get('video_url');
        if (!$videoUrl) {
            return new JsonResponse(['error' => 'Invalid video URL.'], Response::HTTP_BAD_REQUEST);
        }

        $queryString = parse_url($videoUrl, PHP_URL_QUERY);
        parse_str($queryString, $params);
        $url = $params['v'] ?? null;

        if (!$url) {
            return new JsonResponse(['error' => 'Invalid YouTube URL format.'], Response::HTTP_BAD_REQUEST);
        }

        $video = new Video();
        $video->setUrl($url);
        $trick->addVideo($video);

        $this->entityManager->persist($video);
        $this->entityManager->flush();

        // Generate a new CSRF token for the delete action
        $csrfToken = $csrfTokenManager->getToken('delete-video' . $video->getId())->getValue();

        return new JsonResponse([
            'success' => 'Video added successfully.',
            'url' => $url,
            'id' => $video->getId(),
            'deleteUrl' => '/trick/'.$trick->getSlug().'/delete-video/'. $video->getId(),
            'csrfToken' => $csrfToken
        ], Response::HTTP_OK);
    }

    #[Route('/trick/{slug}/edit-video/{id}', name: 'app_trick_edit_video', methods: ['POST'])]
    public function editVideo($slug, $id, Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }

        $video = $this->entityManager->getRepository(Video::class)->find($id);
        if (!$video || $video->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The video does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }

        $youtubeUrl = $request->request->get('url');
        if ($youtubeUrl) {
            $queryString = parse_url($youtubeUrl, PHP_URL_QUERY);
            parse_str($queryString, $params);
            $url = $params['v'] ?? null;

            if ($url) {
                $video->setUrl($url);
                $this->entityManager->flush();

                return new JsonResponse([
                    'success' => 'Video updated successfully.',
                    'url' => $url,
                    'id' => $video->getId(),
                    'csrfToken' => $csrfTokenManager->getToken('edit-video' . $video->getId())->getValue()
                ], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['error' => 'Invalid data provided.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/trick/{slug}/delete-video/{id}', name: 'app_trick_delete_video', methods: ['POST', 'DELETE'])]
    public function deleteVideo($slug, $id, Request $request): JsonResponse
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slug]);
        if (!$trick) {
            return new JsonResponse(['error' => 'The trick does not exist.'], Response::HTTP_NOT_FOUND);
        }
    
        $video = $this->entityManager->getRepository(Video::class)->find($id);
        if (!$video || $video->getTrick() !== $trick) {
            return new JsonResponse(['error' => 'The video does not exist or does not belong to this trick.'], Response::HTTP_NOT_FOUND);
        }
    
        if ($this->isCsrfTokenValid('delete-video' . $video->getId(), $request->request->get('_token'))) {
            // Supprimer l'entité video
            $this->entityManager->remove($video);
            $this->entityManager->flush();
    
            return new JsonResponse(['success' => 'The video has been deleted successfully.'], Response::HTTP_OK);
        } else {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }
    }
    
}
