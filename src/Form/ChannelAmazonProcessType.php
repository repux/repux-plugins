<?php

namespace App\Form;

use App\Entity\ChannelAmazonProcess;
use App\Form\Field\ChannelAmazonType as ChannelAmazonField;
use App\Service\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelAmazonProcessType extends AbstractType
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
                'channelAmazon',
                ChannelAmazonField::class,
                [
                    'user' => $this->currentUserService->getUser(),
                ]
            )
            ->add('type', TextType::class)
            ->add('parameters', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ChannelAmazonProcess::class,
            ]
        );
    }
}
