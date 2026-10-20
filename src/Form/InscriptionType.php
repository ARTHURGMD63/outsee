<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Catalogue;
use App\Enum\TypeCompte;
use App\Enum\TypeLieu;
use App\Form\Model\DemandeInscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<DemandeInscription> */
final class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => TypeCompte::class,
                'choices' => [TypeCompte::Etudiant, TypeCompte::Partenaire],
                'choice_label' => static fn (TypeCompte $type): string => TypeCompte::Etudiant === $type ? 'Étudiant' : 'Établissement',
                'expanded' => true,
                'label' => 'Je suis',
            ])
            ->add('prenom', TextType::class, ['label' => 'Prénom', 'attr' => ['autocomplete' => 'given-name']])
            ->add('nom', TextType::class, ['label' => 'Nom', 'attr' => ['autocomplete' => 'family-name']])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'attr' => ['autocomplete' => 'username']])
            ->add('motDePasse', PasswordType::class, [
                'label' => 'Mot de passe',
                'help' => '8 caractères au minimum.',
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('ville', ChoiceType::class, ['label' => 'Ville', 'choices' => Catalogue::choix(Catalogue::VILLES)])
            ->add('ecole', ChoiceType::class, [
                'label' => 'École',
                'choices' => Catalogue::choix(Catalogue::ECOLES),
                'placeholder' => 'Choisir',
                'required' => false,
            ])
            ->add('promo', ChoiceType::class, [
                'label' => 'Promo',
                'choices' => Catalogue::choix(Catalogue::PROMOS),
                'placeholder' => 'Choisir',
                'required' => false,
            ])
            ->add('interets', ChoiceType::class, [
                'label' => 'Tes centres d\'intérêt',
                'choices' => Catalogue::choix(Catalogue::INTERETS),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('nomEtablissement', TextType::class, ['label' => 'Nom de l\'établissement', 'required' => false])
            ->add('typeEtablissement', EnumType::class, [
                'class' => TypeLieu::class,
                'label' => 'Type d\'établissement',
                'choice_label' => static fn (TypeLieu $type): string => $type->libelle(),
            ])
            ->add('cgu', CheckboxType::class, [
                'label' => 'J\'accepte les conditions générales d\'utilisation',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DemandeInscription::class]);
    }
}
