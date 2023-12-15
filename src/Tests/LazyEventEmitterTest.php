<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Event\LazyEventEmitter;
use Crm\ApplicationModule\Tests\Events\TestEvent;
use Crm\ApplicationModule\Tests\Events\TestListenerA;
use Crm\ApplicationModule\Tests\Events\TestListenerB;
use League\Event\AbstractEvent;
use PHPUnit\Framework\Attributes\DataProvider;

class LazyEventEmitterTest extends CrmTestCase
{
    private LazyEventEmitter $lazyEventEmitter;

    private TestListenerA $testListenerA;

    private TestListenerB $testListenerB;

    public function setUp(): void
    {
        parent::setUp();

        // reset lazy emitter between tests (we need to clean $listeners and $listenersToRemove)
        $this->container->removeService(LazyEventEmitter::class);
        $lazyEventEmitterServices = $this->container->findByType(LazyEventEmitter::class);
        $this->lazyEventEmitter = $this->container->createService($lazyEventEmitterServices[0]);

        $this->testListenerA = new TestListenerA();

        $this->testListenerB = new TestListenerB();

        if (!$this->container->hasService(TestListenerA::class)) {
            $this->container->addService(TestListenerA::class, $this->testListenerA);
        }
        if (!$this->container->hasService(TestListenerB::class)) {
            $this->container->addService(TestListenerB::class, $this->testListenerB);
        }

        // clear event storage before test to have "clean slate"
        $this->lazyEventEmitter->removeAllListeners(TestEvent::class);
        $this->lazyEventEmitter->removeAllListeners(AbstractEvent::class);
    }

    #[DataProvider('addListenerDataProvider')]
    public function testAddListener(array $eventListeners, array $expectedForEvent)
    {
        foreach ($eventListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener['instance']) && $listener['instance']) {
                    $listener['listener'] = $this->inject($listener['listener']);
                }
                $this->lazyEventEmitter->addListener(
                    $event,
                    $listener['listener'],
                    $listener['priority'] ?? LazyEventEmitter::P_NORMAL,
                );
            }
        }

        foreach ($expectedForEvent as $event => $expected) {
            $listeners = $this->lazyEventEmitter->getListeners($event);

            $this->assertCount($expected['count'], $listeners);

            $this->assertEquals(array_map(fn($listener) => $this->inject($listener), $expected['sortedListeners']), $listeners);
        }
    }

    public static function addListenerDataProvider()
    {
        return [
            'noListeners' => [
                'eventListeners' => [
                    AbstractEvent::class => []
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ]
                ]
            ],
            'stringListener' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 1,
                        'sortedListeners' => [TestListenerA::class]
                    ]
                ]
            ],
            'classListener' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 1,
                        'sortedListeners' => [TestListenerA::class]
                    ]
                ]
            ],
            'listenersPriority' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                            'priority' => LazyEventEmitter::P_LOW
                        ],
                        [
                            'listener' => TestListenerB::class,
                            'instance' => false,
                            'priority' => LazyEventEmitter::P_HIGH
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 2,
                        'sortedListeners' => [TestListenerB::class, TestListenerA::class]
                    ]
                ]
            ],
            'multipleEventsListeners' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                            'priority' => LazyEventEmitter::P_LOW
                        ],
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerB::class,
                            'instance' => true,
                            'priority' => LazyEventEmitter::P_HIGH
                        ],
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ],
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 1,
                        'sortedListeners' => [TestListenerA::class]
                    ],
                    TestEvent::class => [
                        'count' => 2,
                        'sortedListeners' => [TestListenerB::class, TestListenerA::class]
                    ]
                ]
            ],
        ];
    }

    #[DataProvider('removeListenerDataProvider')]
    public function testRemoveListener(array $eventListeners, array $removeListeners, array $expectedForEvent)
    {
        foreach ($eventListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener['instance']) && $listener['instance']) {
                    $listener['listener'] = $this->inject($listener['listener']);
                }
                $this->lazyEventEmitter->addListener(
                    $event,
                    $listener['listener'],
                );
            }
        }

        foreach ($removeListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener['instance']) && $listener['instance']) {
                    $listener['listener'] = $this->inject($listener['listener']);
                }
                $this->lazyEventEmitter->removeListener(
                    $event,
                    $listener['listener'],
                );
            }
        }

        foreach ($expectedForEvent as $event => $expected) {
            $listeners = $this->lazyEventEmitter->getListeners($event);

            $this->assertCount($expected['count'], $listeners);

            $this->assertEquals(array_map(fn($listener) => $this->inject($listener), $expected['sortedListeners']), $listeners);
        }
    }

    #[DataProvider('removeListenerDataProvider')]
    public function testRemoveListenerRemovalBeforeRegistration(array $eventListeners, array $removeListeners, array $expectedForEvent)
    {
        // remove listeners before they are registered (to test listenersToRemove queue)
        foreach ($removeListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener['instance']) && $listener['instance']) {
                    $listener['listener'] = $this->inject($listener['listener']);
                }
                $this->lazyEventEmitter->removeListener(
                    $event,
                    $listener['listener'],
                );
            }
        }

        foreach ($eventListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (isset($listener['instance']) && $listener['instance']) {
                    $listener['listener'] = $this->inject($listener['listener']);
                }
                $this->lazyEventEmitter->addListener(
                    $event,
                    $listener['listener'],
                );
            }
        }

        foreach ($expectedForEvent as $event => $expected) {
            $listeners = $this->lazyEventEmitter->getListeners($event);

            $this->assertCount($expected['count'], $listeners);

            $this->assertEquals(array_map(fn($listener) => $this->inject($listener), $expected['sortedListeners']), $listeners);
        }
    }

    public static function removeListenerDataProvider()
    {
        return [
            'noListeners' => [
                'eventListeners' => [
                    AbstractEvent::class => []
                ],
                'removeListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ]
                ]
            ],
            'stringListener' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerB::class,
                            'instance' => false,
                        ]
                    ]
                ],
                'removeListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerB::class,
                            'instance' => true,
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ],
                    TestEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ]
                ]
            ],
            'classListener' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                        ]
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerB::class,
                            'instance' => true,
                        ]
                    ]
                ],
                'removeListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerB::class,
                            'instance' => true,
                        ]
                    ]
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ],
                    TestEvent::class => [
                        'count' => 0,
                        'sortedListeners' => []
                    ]
                ]
            ],
            'multipleListeners' => [
                'eventListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                        ],
                        [
                            'listener' => TestListenerB::class,
                            'instance' => false,
                        ]
                    ],
                    TestEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => true,
                        ],
                        [
                            'listener' => TestListenerB::class,
                            'instance' => false,
                        ]
                    ]
                ],
                'removeListeners' => [
                    AbstractEvent::class => [
                        [
                            'listener' => TestListenerA::class,
                            'instance' => false,
                        ]
                    ],
                ],
                'expectedForEvent' => [
                    AbstractEvent::class => [
                        'count' => 1,
                        'sortedListeners' => [TestListenerB::class]
                    ],
                    TestEvent::class => [
                        'count' => 2,
                        'sortedListeners' => [TestListenerA::class, TestListenerB::class]
                    ]
                ]
            ],
        ];
    }
}
