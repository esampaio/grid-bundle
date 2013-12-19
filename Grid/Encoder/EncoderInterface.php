<?php

namespace PedroTeixeira\Bundle\GridBundle\Grid\Encoder;

/**
 * Encoder interface to implement different data encoding types
 */
interface EncoderInterface
{
    public function __construct(\Symfony\Component\DependencyInjection\Container $container);
    public function setQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder);
    public function supportsPagination();
    public function setColumns($columns);
    public function getMimeType();
    public function encode();
}
