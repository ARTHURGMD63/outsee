<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Catalogue;
use App\Entity\Utilisateur;
use App\Enum\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<Utilisateur> */
final class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom']);

        if ($options['etudiant']) {
            $builder
                ->add('ville', ChoiceType::class, ['label' => 'Ville', 'choices' => Catalogue::choix(Catalogue::VILLES), 'required' => false])
                ->add('ecole', ChoiceType::class, ['label' => 'École', 'choices' => Catalogue::choix(Catalogue::ECOLES), 'required' => false, 'placeholder' => 'Aucune'])
                ->add('promo', ChoiceType::class, ['label' => 'Promo', 'choices' => Catalogue::choix(Catalogue::PROMOS), 'required' => false, 'placeholder' => 'Aucune'])
                ->add('interets', ChoiceType::class, [
                    'label' => 'Centres d\'intérêt',
                    'choices' => Catalogue::choix(Catalogue::INTERETS),
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ])
                ->add('comptePrive', CheckboxType::class, [
                    'label' => 'Compte privé : seuls mes abonnés acceptés voient mon activité',
                    'required' => false,
                ]);
        }

        $builder->add('theme', EnumType::class, [
            'class' => Theme::class,
            'label' => 'Thème',
            'choice_label' => static fn (Theme $theme): string => Theme::Sombre === $theme ? 'Sombre' : 'Clair',
            'expanded' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Utilisateur::class, 'etudiant' => true]);
        $resolver->setAllowedTypes('etudiant', 'bool');
    }
}
