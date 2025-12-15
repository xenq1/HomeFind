<?php
namespace App\Form;

use App\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Property Name'])
            ->add('price', TextType::class, [
                'label' => 'Price (₱)'
            ])
            ->add('location', TextType::class)
            ->add('area', IntegerType::class, ['label' => 'Area (m²)'])
            ->add('type', ChoiceType::class, [
                'required' => false,
                'label' => 'Type',
                'choices' => [
                    'House' => 'House',
                    'Apartment' => 'Apartment',
                    'Condo' => 'Condo',
                    'Studio' => 'Studio',
                    'Townhouse' => 'Townhouse',
                    'Land' => 'Land',
                ],
                'placeholder' => 'Select a type',
            ])
            ->add('listingType', ChoiceType::class, [
                'label' => 'Listing Type',
                'choices' => [
                    'For Sale' => 'for_sale',
                    'For Rent' => 'for_rent',
                ],
                'placeholder' => 'Select listing type',
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => 'Image File',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}
