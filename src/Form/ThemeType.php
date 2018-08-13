<?php

namespace App\Form;

use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('folder', TextType::class)
            ->add('version', TextType::class)
            ->add('author', TextType::class)
            ->add('website', UrlType::class)
            ->add('parameters', CollectionType::class, [
                "entry_type" => ThemeParametersType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "prototype" => true,
                'entry_options' => array(
                    'attr' => array('class' => 'email-box'),
                ),
            ])
            ->add("submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }
}
