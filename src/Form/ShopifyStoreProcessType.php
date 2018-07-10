<?php

namespace App\Form;

use App\Entity\ShopifyStoreProcess;
use App\Form\Field\ShopifyStoreType as ShopifyStoreField;
use App\Service\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopifyStoreProcessType extends AbstractType
{
    protected $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'shopifyStore',
                ShopifyStoreField::class,
                [
                    'user' => $this->currentUserService->getUser(),
                ]
            )
            ->add('parameters', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ShopifyStoreProcess::class,
            ]
        );
    }
}
