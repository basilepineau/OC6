<?php

namespace App\Factory;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Picture>
 */
final class PictureFactory extends PersistentProxyObjectFactory{
    private $uploadDirectory;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */

    public function __construct(ParameterBagInterface $params)
    {
        // Récupérer la valeur de la variable d'environnement
        $this->uploadDirectory = $params->get('upload_directory');
    }

    public static function class(): string
    {
        return Picture::class;
    }

    protected function defaults(): array|callable
    {
        // Chemin vers l'image d'exemple
        $imageSamplePath = __DIR__ . '/../../public/images/fixtures/sample.jpg'; 

        // Générer un nom de fichier aléatoire pour chaque image dupliquée
        $randomFileName = uniqid('picture_', true) . '.jpg';

        return [
            'trick' => TrickFactory::new(),
            'url' => $randomFileName, // Stocker le nom de l'image dupliquée
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(Picture $picture): void {
                // Chemin où la nouvelle image sera uploadée, basé sur la variable d'environnement
                $newFilePath = __DIR__ . '/../../public/assets/uploads'. '/' . $picture->getUrl(); 

                // Copier l'image sample vers un fichier avec un nom unique
                if (!file_exists($this->uploadDirectory)) {
                    mkdir($this->uploadDirectory, 0755, true);
                }

                // Copier l'image sample dans le nouveau fichier
                copy(__DIR__ . '/../../public/assets/fixtures/pictures/sample.jpg', $newFilePath);
            });
    }
}
