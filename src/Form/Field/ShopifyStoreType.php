<?php

namespace App\Form\Field;

use App\Entity\ShopifyStore;
use App\Repository\ShopifyStoreRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopifyStoreType extends AbstractType
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
            'class' => ShopifyStore::class,
            'query_builder' => function (Options $options) {
                /** @var ShopifyStoreRepository $shopifyStoreRepository */
                $shopifyStoreRepository = $this->em->getRepository(ShopifyStore::class);

                return !empty($options['user'])
                    ? $shopifyStoreRepository->createQueryBuilderForUser($options['user'])
                    : null;
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
        return 'shopify_store_field';
    }
}
