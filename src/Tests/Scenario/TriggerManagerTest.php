<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Tests\Scenario;

use Crm\ApplicationModule\Models\Scenario\SkipTriggerException;
use Crm\ApplicationModule\Models\Scenario\TriggerData;
use Crm\ApplicationModule\Models\Scenario\TriggerHandlerInterface;
use Crm\ApplicationModule\Models\Scenario\TriggerManager;
use Crm\ScenariosModule\Engine\Dispatcher as JobDispatcher;
use Crm\ScenariosModule\Repositories\JobsRepository;
use Exception;
use PHPUnit\Framework\TestCase;
use Tomaj\Hermes\Dispatcher;
use Tomaj\Hermes\MessageInterface;

class TriggerManagerTest extends TestCase
{
    public function testRegisterEvent(): void
    {
        $hermesDispatcher = $this->createMock(Dispatcher::class);
        $hermesDispatcher->expects($this->once())
            ->method('registerHandler')
            ->with('event_type', $this->isInstanceOf(TriggerManager::class));

        $jobDispatcher = $this->createMock(JobDispatcher::class);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getEventType')->willReturn('event_type');
        $triggerHandler->method('getKey')->willReturn('key');

        $triggerManager->registerTriggerHandler($triggerHandler);

        $eventHandlers = $triggerManager->getTriggerHandlers();
        $this->assertCount(1, $eventHandlers);

        $firstEvent = current($eventHandlers);
        $this->assertSame('key', $firstEvent->getKey());
    }

    public function testRegisterEventTwice(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Trigger handler Test (test) is already registered.");

        $hermesDispatcher = $this->createMock(Dispatcher::class);
        $jobDispatcher = $this->createMock(JobDispatcher::class);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getName')->willReturn('Test');
        $triggerHandler->method('getKey')->willReturn('test');

        $triggerManager->registerTriggerHandler($triggerHandler);
        $this->assertCount(1, $triggerManager->getTriggerHandlers());

        $triggerManager->registerTriggerHandler($triggerHandler);
    }

    public function testHandleHermesEvent(): void
    {
        $hermesDispatcher = $this->createMock(Dispatcher::class);

        $jobDispatcher = $this->createMock(JobDispatcher::class);
        $jobDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('test', 1, ['user_id' => 2], [JobsRepository::CONTEXT_HERMES_MESSAGE_TYPE => 'test_event']);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getKey')->willReturn('test');
        $triggerHandler->method('getOutputParams')->willReturn(['user_id']);
        $triggerHandler->method('getEventType')->willReturn('test_event');
        $triggerHandler->expects($this->once())
            ->method('handleEvent')
            ->with(['some_payload'])
            ->willReturn(new TriggerData(1, ['user_id' => 2]));

        $triggerManager->registerTriggerHandler($triggerHandler);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('test_event');
        $message->method('getPayload')->willReturn(['some_payload']);

        $this->assertTrue($triggerManager->handle($message));
    }

    public function testHandleHermesEventWithThrownSkipTriggerException(): void
    {
        $hermesDispatcher = $this->createMock(Dispatcher::class);

        $jobDispatcher = $this->createMock(JobDispatcher::class);
        $jobDispatcher->expects($this->never())->method('dispatch');

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getKey')->willReturn('test');
        $triggerHandler->method('getEventType')->willReturn('test_event');
        $triggerHandler->expects($this->once())
            ->method('handleEvent')
            ->with(['some_payload'])
            ->willThrowException(new SkipTriggerException());

        $triggerManager->registerTriggerHandler($triggerHandler);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('test_event');
        $message->method('getPayload')->willReturn(['some_payload']);

        $this->assertTrue($triggerManager->handle($message));
    }

    public function testHandleHermesEventWithThrownException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Error while handling a trigger handler Test: some error");

        $hermesDispatcher = $this->createMock(Dispatcher::class);

        $jobDispatcher = $this->createMock(JobDispatcher::class);
        $jobDispatcher->expects($this->never())->method('dispatch');

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getName')->willReturn('Test');
        $triggerHandler->method('getKey')->willReturn('test');
        $triggerHandler->method('getEventType')->willReturn('test_event');
        $triggerHandler->expects($this->once())
            ->method('handleEvent')
            ->with(['some_payload'])
            ->willThrowException(new Exception('some error'));

        $triggerManager->registerTriggerHandler($triggerHandler);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('test_event');
        $message->method('getPayload')->willReturn(['some_payload']);

        $triggerManager->handle($message);
    }

    public function testHandleHermesEventWithMissingParamsInTriggerData(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Error while handling a trigger handler Test: Output param 'another_id' is missing in trigger data payload.");

        $hermesDispatcher = $this->createMock(Dispatcher::class);

        $jobDispatcher = $this->createMock(JobDispatcher::class);
        $jobDispatcher->expects($this->never())->method('dispatch');

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getName')->willReturn('Test');
        $triggerHandler->method('getOutputParams')->willReturn(['user_id', 'another_id']);
        $triggerHandler->method('getEventType')->willReturn('test_event');
        $triggerHandler->expects($this->once())
            ->method('handleEvent')
            ->with(['some_payload'])
            ->willReturn(new TriggerData(1, ['user_id' => 2]));

        $triggerManager->registerTriggerHandler($triggerHandler);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('test_event');
        $message->method('getPayload')->willReturn(['some_payload']);

        $triggerManager->handle($message);
    }

    public function testHandleHermesEventWithAdditionalParamsInTriggerData(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Error while handling a trigger handler Test: Payload contains an undefined param 'another_id'.");

        $hermesDispatcher = $this->createMock(Dispatcher::class);

        $jobDispatcher = $this->createMock(JobDispatcher::class);
        $jobDispatcher->expects($this->never())->method('dispatch');

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getName')->willReturn('Test');
        $triggerHandler->method('getOutputParams')->willReturn(['user_id']);
        $triggerHandler->method('getEventType')->willReturn('test_event');
        $triggerHandler->expects($this->once())
            ->method('handleEvent')
            ->with(['some_payload'])
            ->willReturn(new TriggerData(1, ['user_id' => 2, 'another_id' => 3]));

        $triggerManager->registerTriggerHandler($triggerHandler);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('test_event');
        $message->method('getPayload')->willReturn(['some_payload']);

        $triggerManager->handle($message);
    }

    public function testHandleHermesEventWithUnregisteredHandler(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown handler for trigger handler type 'unknown_event'");

        $hermesDispatcher = $this->createMock(Dispatcher::class);
        $jobDispatcher = $this->createMock(JobDispatcher::class);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $message = $this->createMock(MessageInterface::class);
        $message->method('getType')->willReturn('unknown_event');

        $triggerManager->handle($message);
    }

    public function testEventByKey(): void
    {
        $hermesDispatcher = $this->createMock(Dispatcher::class);
        $jobDispatcher = $this->createMock(JobDispatcher::class);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);

        $triggerHandler = $this->createMock(TriggerHandlerInterface::class);
        $triggerHandler->method('getKey')->willReturn('test');
        $triggerHandler->method('getEventType')->willReturn('test_event');

        $triggerManager->registerTriggerHandler($triggerHandler);

        $this->assertSame($triggerHandler, $triggerManager->getTriggerHandlerByKey('test'));
    }

    public function testEventByKeyWithUnknownEvent(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Trigger handler with key 'unknown' doesn't exist.");

        $hermesDispatcher = $this->createMock(Dispatcher::class);
        $jobDispatcher = $this->createMock(JobDispatcher::class);

        $triggerManager = new TriggerManager($hermesDispatcher, $jobDispatcher);
        $triggerManager->getTriggerHandlerByKey('unknown');
    }
}
