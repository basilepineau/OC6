<?php

namespace App\Service;

use App\Entity\Trick;
use App\Repository\PictureRepository;
use App\Repository\VideoRepository;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TrickMediaService
{
    private $pictureRepository;
    private $videoRepository;
    private $csrfTokenManager;

    public function __construct(PictureRepository $pictureRepository, VideoRepository $videoRepository, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->pictureRepository = $pictureRepository;
        $this->videoRepository = $videoRepository;
        $this->csrfTokenManager = $csrfTokenManager;
    }

// Dans TrickMediaService.php
public function getUpdatedMedia(Trick $trick)
{
    $pictures = $this->pictureRepository->findBy(['trick' => $trick]);
    $videos = $this->videoRepository->findBy(['trick' => $trick]);

    // Formater les images
    $formattedPictures = [];
    foreach ($pictures as $picture) {
        $formattedPictures[] = [
            'id' => $picture->getId(),
            'url' => $picture->getUrl(),
            'trickSlug' => $trick->getSlug(),
            'csrfToken' => $this->csrfTokenManager->getToken('delete-picture' . $picture->getId())->getValue(),
        ];
    }

    // Formater les vidéos
    $formattedVideos = [];
    foreach ($videos as $video) {
        $formattedVideos[] = [
            'id' => $video->getId(),
            'url' => $video->getUrl(),
            'trickSlug' => $trick->getSlug(),
            'csrfToken' => $this->csrfTokenManager->getToken('delete-video' . $video->getId())->getValue(),
        ];
    }

    return [
        'pictures' => $formattedPictures,
        'videos' => $formattedVideos,
    ];
}

}