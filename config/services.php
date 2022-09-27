<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;
use Terminal42\NotificationCenterBundle\EventListener\Backend\BackendMenuListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\FormListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\GatewayListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\LanguageListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\MessageListener;
use Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer\NotificationListener;
use Terminal42\NotificationCenterBundle\EventListener\ProcessFormDataListener;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\MessageType\CoreFormMessageType;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;
use Terminal42\NotificationCenterBundle\NotificationCenter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()->autoconfigure();

    $services->set(FormListener::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;

    $services->set(GatewayListener::class)
        ->args([
            service(GatewayRegistry::class),
            service('contao.mailer.available_transports'),
        ])
    ;

    $services->set(LanguageListener::class)
        ->args([
            service('database_connection'),
            service(ConfigLoader::class),
            service('contao.intl.locales'),
            service(TranslatorInterface::class),
            service('assets.packages'),
            service(NotificationCenter::class),
        ])
    ;

    $services->set(MessageListener::class)
        ->args([
            service(ConfigLoader::class),
            service('contao.framework'),
        ])
    ;

    $services->set(NotificationListener::class)
        ->args([
            service(MessageTypeRegistry::class),
            service('contao.framework'),
        ])
    ;

    $services->set(BackendMenuListener::class)
        ->tag('kernel.event_listener', ['event' => ContaoCoreEvents::BACKEND_MENU_BUILD])
    ;

    $services->set(GatewayRegistry::class)
        ->args([
            tagged_iterator(Terminal42NotificationCenterExtension::GATEWAY_TAG),
        ])
    ;

    $services->set(MessageTypeRegistry::class)
        ->args([
            tagged_iterator(Terminal42NotificationCenterExtension::TYPE_TAG),
        ])
    ;

    $services->set(ConfigLoader::class)
        ->args([
            service('database_connection'),
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.reset'])
    ;

    $services->set(MailerGateway::class)
        ->args([service_locator([
            'mailer' => service('mailer'),
            'contao.string.simple_token_parser' => service('contao.string.simple_token_parser'),
            'contao.framework' => service('contao.framework'),
        ])])
    ;

    $services->set(CoreFormMessageType::class);

    $services->set(NotificationCenter::class)
        ->args([
            service('database_connection'),
            service(MessageTypeRegistry::class),
            service(GatewayRegistry::class),
            service(ConfigLoader::class),
            service('event_dispatcher'),
            service('request_stack'),
        ])
    ;

    $services->set(ProcessFormDataListener::class)
        ->args([
            service(NotificationCenter::class),
        ])
    ;
};