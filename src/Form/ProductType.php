<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Títol del producte',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Bicicleta de muntanya']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripció detallada',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Explica com està el producte...']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Preu',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control']
            ])
            ->add('image', UrlType::class, [
                'label' => 'URL de la imatge',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
