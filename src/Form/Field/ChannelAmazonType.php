<?php

namespace App\Form\Field;

use App\Entity\ChannelAmazon;
use App\Repository\ChannelAmazonRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelAmazonType extends AbstractType
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
            'class' => ChannelAmazon::class,
            'query_builder' => function (Options $options) {
                /** @var ChannelAmazonRepository $channelAmazonRepository */
                $channelAmazonRepository = $this->em->getRepository(ChannelAmazon::class);

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
        return 'channel_amazon_field';
    }
}
