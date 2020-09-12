<?php

declare(strict_types=1);

/*
 * This file is part of the ekino/phpstan-sonata project.
 *
 * (c) Ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\PHPStanSonata\Type;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\QueryBuilder;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as AdminProxyQueryInterface;
use Sonata\DatagridBundle\ProxyQuery\ProxyQueryInterface as DatagridProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery as MongoDBProxyQuery;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as ORMProxyQuery;

/**
 * @author Rémi Marseille <remi.marseille@ekino.com>
 */
class ProxyQueryDynamicReturnTypeExtension implements MethodsClassReflectionExtension, BrokerAwareExtension
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * {@inheritdoc}
     */
    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (!\in_array($classReflection->getName(), [
            AdminProxyQueryInterface::class,
            DatagridProxyQueryInterface::class,
            ORMProxyQuery::class,
            MongoDBProxyQuery::class
        ])) {
            return false;
        }

        return (class_exists(QueryBuilder::class)
                && $this->broker->getClass(QueryBuilder::class)->hasMethod($methodName))
            ||
                (class_exists(Builder::class)
                && $this->broker->getClass(Builder::class)->hasMethod($methodName));
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $className = $classReflection->getName();

        if ($className === ORMProxyQuery::class && class_exists(ORMProxyQuery::class)) {
            return $this->broker
                ->getClass(QueryBuilder::class)
                ->getNativeMethod($methodName);
        }

        if ($className === MongoDBProxyQuery::class && class_exists(MongoDBProxyQuery::class)) {
            return $this->broker
                ->getClass(Builder::class)
                ->getNativeMethod($methodName);
        }

        return $this->broker
            ->getClass(QueryBuilder::class)
            ->getNativeMethod($methodName);
    }
}
