<?php

namespace Bernard\Tests\Queue;

use Bernard\Message\Envelope;
use Bernard\Queue\PersistentQueue;

class PersistentQueueTest extends AbstractQueueTest
{
    public function setUp()
    {
        $this->connection = $this->getMockBuilder('Bernard\Connection')->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMock('Bernard\Serializer');
    }

    public function testDequeue()
    {
        $messageWrapper = new Envelope($this->getMock('Bernard\Message'));

        $this->connection->expects($this->at(1))->method('pop')->with($this->equalTo('queue:send-newsletter'))
            ->will($this->returnValue('deserialized'));

        $this->connection->expects($this->at(2))->method('pop')->with($this->equalTo('queue:send-newsletter'))
            ->will($this->returnValue(null));

        $this->serializer->expects($this->once())->method('deserialize')->with($this->equalTo('deserialized'))
            ->will($this->returnValue($messageWrapper));

        $queue = $this->createQueue('send-newsletter');

        $this->assertSame($messageWrapper, $queue->dequeue());
        $this->assertInternalType('null', $queue->dequeue());
    }

    public function testKeyIsPrefixedWithQueue()
    {
        $this->assertEquals('queue:send-newsletter', $this->createQueue('send-newsletter')->getKey());
    }

    public function dataClosedMethods()
    {
        $methods = parent::dataClosedMethods();
        $methods[] = array('register', array());

        return $methods;
    }

    protected function createQueue($name)
    {
        return new PersistentQueue($name, $this->connection, $this->serializer);
    }
}
