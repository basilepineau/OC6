<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    private $uploadDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadDirectory = $params->get('upload_directory');
    }

    public function upload(UploadedFile $file): string
    {
        // Créez un nom unique pour le fichier
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Déplace le fichier temporaire vers le dossier de destination
        $file->move($this->uploadDirectory, $newFilename);

        // Redimensionner l'image
        $imagePath = $this->uploadDirectory . '/' . $newFilename;
        $this->resizeImageTo16by9($imagePath, 1920);

        return $newFilename;
    }

    private function resizeImageTo16by9(string $imagePath, int $maxWidth): void
    {
        // Définir le ratio 16:9
        $targetRatio = 16 / 9;
    
        // Obtenir les dimensions originales et le type de l'image
        list($origWidth, $origHeight, $type) = getimagesize($imagePath);
    
        // Calculer la hauteur maximale en fonction de la largeur maximale et du ratio 16:9
        $targetWidth = $maxWidth;
        $targetHeight = (int)($targetWidth / $targetRatio);
    
        // Ajuster la hauteur si elle dépasse les dimensions de l'image originale
        if ($targetHeight > $origHeight) {
            $targetHeight = $origHeight;
            $targetWidth = (int)($targetHeight * $targetRatio);
        }
    
        // Calculer les dimensions de recadrage
        $cropX = 0;
        $cropY = 0;
        $cropWidth = $origWidth;
        $cropHeight = $origHeight;
    
        if ($origWidth / $origHeight > $targetRatio) {
            // Recadrer les côtés si l'image est trop large
            $cropWidth = (int)($origHeight * $targetRatio);
            $cropX = ($origWidth - $cropWidth) / 2;
        } else {
            // Recadrer le haut et le bas si l'image est trop haute
            $cropHeight = (int)($origWidth / $targetRatio);
            $cropY = ($origHeight - $cropHeight) / 2;
        }
    
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
        $imageResized = imagecreatetruecolor($targetWidth, $targetHeight);
    
        // Définir la transparence pour les images PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($imageResized, false);
            imagesavealpha($imageResized, true);
            $transparent = imagecolorallocatealpha($imageResized, 0, 0, 0, 127);
            imagefilledrectangle($imageResized, 0, 0, $targetWidth, $targetHeight, $transparent);
        }
    
        // Redimensionner et recadrer l'image
        imagecopyresampled(
            $imageResized,
            $imageOriginal,
            0, 0,
            $cropX, $cropY,
            $targetWidth, $targetHeight,
            $cropWidth, $cropHeight
        );
    
        // Vérifier si l'image dépasse les dimensions maximales après redimensionnement
        if ($targetWidth > $maxWidth || $targetHeight > $maxWidth / $targetRatio) {
            $resizeRatio = min($maxWidth / $targetWidth, ($maxWidth / $targetWidth) / $targetRatio);
            $newWidth = (int)($targetWidth * $resizeRatio);
            $newHeight = (int)($targetHeight * $resizeRatio);
    
            // Créer une nouvelle image pour appliquer le redimensionnement final
            $imageFinal = imagecreatetruecolor($newWidth, $newHeight);
    
            // Définir la transparence pour les images PNG
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($imageFinal, false);
                imagesavealpha($imageFinal, true);
                $transparent = imagecolorallocatealpha($imageFinal, 0, 0, 0, 127);
                imagefilledrectangle($imageFinal, 0, 0, $newWidth, $newHeight, $transparent);
            }
    
            // Redimensionner l'image à la taille finale
            imagecopyresampled(
                $imageFinal,
                $imageResized,
                0, 0,
                0, 0,
                $newWidth, $newHeight,
                $targetWidth, $targetHeight
            );
    
            // Sauvegarder l'image finale en fonction du type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($imageFinal, $imagePath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($imageFinal, $imagePath);
                    break;
            }
    
            // Libérer la mémoire
            imagedestroy($imageFinal);
        } else {
            // Sauvegarder l'image redimensionnée
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($imageResized, $imagePath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($imageResized, $imagePath);
                    break;
            }
        }
    
        // Libérer la mémoire
        imagedestroy($imageOriginal);
        imagedestroy($imageResized);
    }
    
    
}