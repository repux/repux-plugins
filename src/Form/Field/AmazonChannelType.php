<?php

namespace App\Form\Field;

use App\Entity\AmazonChannel;
use App\Repository\AmazonChannelRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AmazonChannelType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'class' => AmazonChannel::class,
            'query_builder' => function (Options $options) {
                /** @var AmazonChannelRepository $channelAmazonRepository */
                $channelAmazonRepository = $this->em->getRepository(AmazonChannel::class);

                return !empty($options['user']) ?
                    $channelAmazonRepository->createQueryBuilderForUser($options['user']) : null;
            },
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'amazon_channel_field';
    }
}
