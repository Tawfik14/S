<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Film' => Media::TYPE_MOVIE,
                    'Série' => Media::TYPE_SERIES,
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Titre'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (unique, pour l’URL)',
                'attr' => ['placeholder' => 'ex: inception'],
            ])
            ->add('tagline', TextType::class, [
                'label' => 'Tagline',
                'required' => false,
            ])
            ->add('synopsis', TextareaType::class, [
                'label' => 'Synopsis',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('genresText', TextType::class, [
                'mapped' => false,
                'label' => 'Genres (séparés par des virgules)',
                'required' => false,
                'attr' => ['placeholder' => 'Action, Science‑fiction, Drame'],
            ])
            ->add('runtime', IntegerType::class, [
                'label' => 'Durée (min)',
                'required' => false,
            ])
            ->add('rating', NumberType::class, [
                'label' => 'Note (/10)',
                'required' => false,
                'scale' => 1,
            ])
            ->add('posterUrl', TextType::class, [
                'label' => 'URL Affiche (poster)',
                'required' => false,
            ])
            ->add('backdropUrl', TextType::class, [
                'label' => 'URL Backdrop (bannière)',
                'required' => false,
            ])
            ->add('trailerUrl', TextType::class, [
                'label' => 'URL Trailer (YouTube, Vimeo...)',
                'required' => false,
            ])
            ->add('releaseDate', DateType::class, [
                'label' => 'Date de sortie',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'inline-flex items-center justify-center rounded-lg bg-rose-600 hover:bg-rose-500 px-4 py-2 font-medium'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Media::class]);
    }
}

