<?php

namespace RabbitEvents\Foundation\Tests;

use Interop\Amqp\AmqpBind;
use Interop\Amqp\AmqpConsumer;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use RabbitEvents\Foundation\Connection;
use RabbitEvents\Foundation\Consumer;
use RabbitEvents\Foundation\Context;
use Mockery as m;

class ContextTest extends TestCase
{
    public function testContextCall()
    {
        $amqpContext = m::mock(AmqpContext::class);
        $amqpContext->shouldReceive()
            ->foo('bar')
            ->once()
            ->andReturn('result');

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('createContext')
            ->andReturn($amqpContext);

        $context = new Context($connection);

        self::assertEquals('result', $context->foo('bar'));
    }

    public function testCreateConsumer()
    {
        $amqpContext = m::mock(AmqpContext::class);
        $amqpContext->shouldReceive('createConsumer')
            ->andReturn(m::mock(AmqpConsumer::class));

        $topic = m::spy(AmqpTopic::class);

        $amqpContext->shouldReceive('createTopic')
            ->andReturn($topic);

        $amqpContext->shouldReceive()
            ->declareTopic($topic)->once();

        $amqpContext->shouldReceive()
            ->bind(m::type(AmqpBind::class));

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('createContext')
            ->andReturn($amqpContext);

        $connection->shouldReceive()
            ->getConfig('exchange')
            ->andReturn('events');

        $context = new Context($connection);

        $consumer = $context->createConsumer(m::mock(AmqpQueue::class), 'item.created');

        self::assertInstanceOf(Consumer::class, $consumer);

    }
}