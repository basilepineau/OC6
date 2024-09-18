<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AvatarService
{
    private $uploadDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadDirectory = $params->get('avatar_directory');
    }

    public function upload(UploadedFile $file): string
    {
        // Créez un nom unique pour le fichier
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Déplace le fichier temporaire vers le dossier de destination
        $file->move($this->uploadDirectory, $newFilename);

        // Redimensionner l'image en 500x500 pixels
        $imagePath = $this->uploadDirectory . '/' . $newFilename;
        $this->resizeAndCropToSquare($imagePath, 500);

        return $newFilename;
    }

    private function resizeAndCropToSquare(string $imagePath, int $size): void
    {
        // Obtenir les dimensions originales et le type de l'image
        list($origWidth, $origHeight, $type) = getimagesize($imagePath);

        // Calculer les dimensions de recadrage pour obtenir un carré
        $cropSize = min($origWidth, $origHeight);
        $cropX = ($origWidth - $cropSize) / 2;
        $cropY = ($origHeight - $cropSize) / 2;

        // Charger l'image originale en fonction du type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $imageOriginal = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $imageOriginal = imagecreatefrompng($imagePath);
                break;
            default:
                throw new \Exception('Unsupported image type. Only JPG, JPEG, and PNG are supported.');
        }

        // Créer une image vide pour la redimensionnée
        $imageResized = imagecreatetruecolor($size, $size);

        // Définir la transparence pour les images PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($imageResized, false);
            imagesavealpha($imageResized, true);
            $transparent = imagecolorallocatealpha($imageResized, 0, 0, 0, 127);
            imagefilledrectangle($imageResized, 0, 0, $size, $size, $transparent);
        }

        // Redimensionner et recadrer l'image
        imagecopyresampled(
            $imageResized,
            $imageOriginal,
            0, 0,
            $cropX, $cropY,
            $size, $size,
            $cropSize, $cropSize
        );

        // Sauvegarder l'image redimensionnée en fonction du type
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($imageResized, $imagePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($imageResized, $imagePath);
                break;
        }

        // Libérer la mémoire
        imagedestroy($imageOriginal);
        imagedestroy($imageResized);
    }
}
