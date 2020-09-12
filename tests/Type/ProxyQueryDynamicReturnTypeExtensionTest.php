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

namespace Tests\Ekino\PHPStanSonata\Type;

use Ekino\PHPStanSonata\Type\ProxyQueryDynamicReturnTypeExtension;
use PHPStan\Testing\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as AdminProxyQueryInterface;
use Sonata\DatagridBundle\ProxyQuery\ProxyQueryInterface as DatagridProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery as MongoDBProxyQuery;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as ORMProxyQuery;

/**
 * @author Rémi Marseille <remi.marseille@ekino.com>
 */
class ProxyQueryDynamicReturnTypeExtensionTest extends TestCase
{
    /** @var \PHPStan\Broker\Broker */
    private $broker;

    /** @var ProxyQueryDynamicReturnTypeExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->broker = $this->createBroker();

        $this->extension = new ProxyQueryDynamicReturnTypeExtension();
        $this->extension->setBroker($this->broker);
    }

    /**
     * @dataProvider hasMethodDataProvider
     */
    public function testHasMethod(bool $expectedResult, string $className, string $method): void
    {
        $classReflection = $this->broker->getClass($className);
        self::assertSame($expectedResult, $this->extension->hasMethod($classReflection, $method));
    }

    /**
     * @return \Generator<array>
     */
    public function hasMethodDataProvider(): \Generator
    {
        yield 'wrong class & method' => [false, \stdClass::class, 'foo'];
        yield 'wrong class & valid method' => [false, \stdClass::class, 'leftJoin'];
        yield 'proxy query & valid method' => [true, ORMProxyQuery::class, 'leftJoin'];
        yield 'proxy query & wrong method' => [false, ORMProxyQuery::class, 'foo'];
        yield 'mongodb proxy query & valid method' => [true, MongoDBProxyQuery::class, 'field'];
        yield 'mongodb proxy query & wrong method' => [false, MongoDBProxyQuery::class, 'foo'];
        yield 'admin proxy query & valid method' => [true, AdminProxyQueryInterface::class, 'leftJoin'];
        yield 'admin proxy query & wrong method' => [false, AdminProxyQueryInterface::class, 'foo'];
        yield 'datagrid proxy query & valid method' => [true, DatagridProxyQueryInterface::class, 'leftJoin'];
        yield 'datagrid proxy query & wrong method' => [false, DatagridProxyQueryInterface::class, 'foo'];
    }

    /**
     * @dataProvider getMethodDataProvider
     */
    public function testGetMethod( string $className, string $method): void
    {
        $classReflection  = $this->broker->getClass($className);
        $methodReflection = $this->extension->getMethod($classReflection, $method);
        self::assertSame($method, $methodReflection->getName());
    }

    /**
     * @return \Generator<array>
     */
    public function getMethodDataProvider(): \Generator
    {
        yield 'proxy query' => [ORMProxyQuery::class, 'leftJoin'];
        yield 'mongodb proxy query' => [MongoDBProxyQuery::class, 'field'];
        yield 'admin proxy query' => [AdminProxyQueryInterface::class, 'leftJoin'];
        yield 'datagrid proxy query' => [DatagridProxyQueryInterface::class, 'leftJoin'];
    }
}
