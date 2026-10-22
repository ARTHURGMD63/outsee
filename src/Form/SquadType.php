<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Squad;
use App\Enum\NiveauSquad;
use App\Enum\TypeSquad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/** @extends AbstractType<Squad> */
final class SquadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => 'Titre', 'attr' => ['placeholder' => 'Footing du dimanche']])
            ->add('type', EnumType::class, [
                'class' => TypeSquad::class,
                'label' => 'Activité',
                'choice_label' => static fn (TypeSquad $type): string => $type->libelle(),
            ])
            ->add('niveau', EnumType::class, [
                'class' => NiveauSquad::class,
                'label' => 'Niveau',
                'choice_label' => static fn (NiveauSquad $niveau): string => $niveau->libelle(),
            ])
            ->add('dateHeure', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\GreaterThan('now', message: 'La sortie doit être à venir.')],
            ])
            ->add('lieu', TextType::class, ['label' => 'Lieu de rendez-vous', 'required' => false])
            ->add('quota', IntegerType::class, ['label' => 'Participants maximum'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('creeParAssociation', CheckboxType::class, ['label' => 'Je la propose au nom d\'une association', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Squad::class]);
    }
}
