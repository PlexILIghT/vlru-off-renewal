<?php

namespace App\Form;

use App\Entity\BigFolkDistrict;
use App\Entity\Blackout;
use App\Entity\Building;
use App\Entity\City;
use App\Entity\District;
use App\Entity\FolkDistrict;
use App\Entity\Street;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuildingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number')
            ->add('isFake')
            ->add('type')
            ->add('coordinates')
            ->add('street', EntityType::class, [
                'class' => Street::class,
                'choice_label' => 'id',
            ])
            ->add('district', EntityType::class, [
                'class' => District::class,
                'choice_label' => 'id',
            ])
            ->add('folkDistrict', EntityType::class, [
                'class' => FolkDistrict::class,
                'choice_label' => 'id',
            ])
            ->add('bigFolkDistrict', EntityType::class, [
                'class' => BigFolkDistrict::class,
                'choice_label' => 'id',
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'id',
            ])
            ->add('blackouts', EntityType::class, [
                'class' => Blackout::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Building::class,
        ]);
    }
}
