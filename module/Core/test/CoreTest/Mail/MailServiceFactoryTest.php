<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

namespace CoreTest\Mail;

use PHPUnit\Framework\TestCase;

use Auth\Options\ModuleOptions as AuthOptions;
use Core\Mail\FileTransport;
use Core\Mail\MailService;
use Core\Mail\MailServiceFactory;
use Core\Options\MailServiceOptions;
use Core\Queue\MongoQueue;
use Interop\Container\ContainerInterface;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use SlmQueue\Queue\QueuePluginManager;

/**
 * Class MailServiceFactoryTest
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @package CoreTest\Mail
 * @covers \Core\Mail\MailServiceFactory
 * @since 0.30.1
 */
class MailServiceFactoryTest extends TestCase
{
    public function testInvokation()
    {
        $queues = $this->createMock(ContainerInterface::class);
        $mailQueue = $this->getMockBuilder(MongoQueue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queues->expects($this->any())->method('get')->with('mail')->willReturn($mailQueue);
        $container = $this->createMock(ContainerInterface::class);
        $target = $this->getMockBuilder(MailServiceFactory::class)
            ->setMethods(['getTransport'])
            ->getMock()
        ;
        $transport = $this->createMock(TransportInterface::class);

        $authOptions = new AuthOptions();
        $mailOptions = new MailServiceOptions();

        $container->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['Config',[]],
                    ['Auth/Options',$authOptions],
                    ['Core/MailServiceOptions',$mailOptions],
                    [QueuePluginManager::class,$queues],
            ]
        );


        $target->expects($this->any())
            ->method('getTransport')
            ->willReturn($transport);


        /* @var \Core\Mail\MailService $service */
        /* @var \Laminas\ServiceManager\Factory\FactoryInterface $target */
        $service = $target($container, 'some-name');
        $this->assertInstanceOf(
            MailService::class,
            $service
        );
        $this->assertSame($transport, $service->getTransport());
        $this->assertSame($mailQueue, $service->getQueue());
    }

    public function testGetTransport()
    {
        $options = new MailServiceOptions([]);
        $factory = new MailServiceFactory();

        $options->setTransportClass('smtp');
        $this->assertInstanceOf(Smtp::class, $factory->getTransport($options));

        $options->setTransportClass('file');
        $this->assertInstanceOf(FileTransport::class, $factory->getTransport($options));

        $options->setTransportClass('sendmail');
        $this->assertInstanceOf(Sendmail::class, $factory->getTransport($options));

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessageRegExp('/\"foo\" is not a valid/');

        $options->setTransportClass('foo');
        $this->assertInstanceOf(Sendmail::class, $factory->getTransport($options));
    }
}
