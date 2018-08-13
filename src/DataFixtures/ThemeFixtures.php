<?php
namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $theme = new Theme();
        $theme->setName("Default");
        $theme->setDescription("The default template for Videa");
        $theme->setFolder("default");
        $theme->setVersion("0.0.1");
        $theme->setAuthor("Kodmit");
        $theme->setWebsite("http://kodmit.com/");
        $theme->setParameters([
           ["parameter" => "logo_path", "value" => "/dist/imgs/logo.png"],
           ["parameter" => "background_color", "value" => "#d8d8d8"],
           ["parameter" => "background_img", "value" => "background.jpg"],
           ["parameter" => "menu_header_color", "value" => "#E71D36"],
           ["parameter" => "footer_color", "value" => "#011627"],
           ["parameter" => "topbar_color", "value" => "#00171F"],
           ["parameter" => "footer_text", "value" => "Vidéa CMS est une plateforme de partage de vidéos et de contenus."]
        ]);
        $manager->persist($theme);
        $manager->flush();
    }
}