<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Evenement;
use App\Enum\StyleMusique;
use App\Enum\TypeLieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<Evenement> */
final class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => 'Titre'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('type', EnumType::class, [
                'class' => TypeLieu::class,
                'label' => 'Type de soirée',
                'choice_label' => static fn (TypeLieu $type): string => $type->libelle(),
            ])
            ->add('styleMusique', EnumType::class, [
                'class' => StyleMusique::class,
                'label' => 'Musique',
                'required' => false,
                'placeholder' => 'Non précisé',
                'choice_label' => static fn (StyleMusique $style): string => $style->libelle(),
            ])
            ->add('dateHeure', DateTimeType::class, ['label' => 'Début', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('dateFin', DateTimeType::class, ['label' => 'Fin', 'widget' => 'single_text', 'input' => 'datetime_immutable', 'required' => false])
            ->add('lieu', TextType::class, ['label' => 'Adresse', 'required' => false])
            ->add('quota', IntegerType::class, ['label' => 'Places'])
            ->add('prixNormal', MoneyType::class, ['label' => 'Prix normal', 'currency' => 'EUR'])
            ->add('reduction', IntegerType::class, ['label' => 'Réduction membres (%)'])
            ->add('gratuit', CheckboxType::class, ['label' => 'Entrée gratuite', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Evenement::class]);
    }
}
