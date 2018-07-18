<?php

namespace App\Form;

use App\Entity\AmazonChannelProcess;
use App\Form\Field\AmazonChannelType as AmazonChannelField;
use App\Service\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AmazonChannelProcessType extends AbstractType
{
    private $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'amazonChannel',
                AmazonChannelField::class,
                [
                    'user' => $this->currentUserService->getUser(),
                ]
            )
            ->add('type', IntegerType::class)
            ->add('parameters', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => AmazonChannelProcess::class,
            ]
        );
    }
}
