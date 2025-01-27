<?php

namespace Application;

use Autowp\ExternalLoginService\Facebook;
use Autowp\ExternalLoginService\Github;
use Autowp\ExternalLoginService\GooglePlus;
use Autowp\ExternalLoginService\Linkedin;
use Autowp\ExternalLoginService\Twitter;
use Autowp\ExternalLoginService\Vk;
use Autowp\ZFComponents\Resources;
use Exception;
use Zend\I18n\Translator\Loader\PhpArray;
use Zend\InputFilter\InputFilterAbstractServiceFactory;
use Zend\Mvc\I18n\TranslatorFactory;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\Factory\InvokableFactory;

$host = getenv('AUTOWP_HOST');
$hostCookie = ($host == 'localhost' ? '' : '.' . $host);

$mailTypes = [
    'in-memory' => [
        'type' => 'in-memory'
    ],
    'smtp' => [
        'type'    => 'smtp',
        'options' => [
            'host'              => getenv('AUTOWP_MAIL_SMTP_HOST'),
            'connection_class'  => 'login',
            'connection_config' => [
                'username' => getenv('AUTOWP_MAIL_SMTP_USERNAME'),
                'password' => getenv('AUTOWP_MAIL_SMTP_PASSWORD'),
                'ssl'      => 'tls'
            ],
        ],
    ]
];

$mailType = getenv('AUTOWP_MAIL_TYPE');
if (! $mailType) {
    throw new Exception("Mail type not provided");
}
if (! isset($mailTypes[$mailType])) {
    throw new Exception("Mail type `$mailType` not found");
}
$mailTransport = $mailTypes[$mailType];

return [
    'controllers' => [
        'factories' => [
            Controller\BrandsController::class          => Controller\Frontend\Service\BrandsControllerFactory::class,
            Controller\CatalogueController::class       => Controller\Frontend\Service\CatalogueControllerFactory::class,
            Controller\CategoryController::class        => Controller\Frontend\Service\CategoryControllerFactory::class,
            Controller\CommentsController::class        => Controller\Frontend\Service\CommentsControllerFactory::class,
            Controller\DonateController::class          => Controller\Frontend\Service\DonateControllerFactory::class,
            Controller\FactoriesController::class       => Controller\Frontend\Service\FactoriesControllerFactory::class,
            Controller\IndexController::class           => Controller\Frontend\Service\IndexControllerFactory::class,
            Controller\InboxController::class           => InvokableFactory::class,
            Controller\PictureController::class         => Controller\Frontend\PictureControllerFactory::class,
            Controller\PictureFileController::class     => Controller\Frontend\Service\PictureFileControllerFactory::class,
            Controller\TelegramController::class        => Controller\Frontend\Service\TelegramControllerFactory::class,
            Controller\Frontend\YandexController::class => Controller\Frontend\Service\YandexControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'forbiddenAction'     => Controller\Plugin\ForbiddenAction::class,
            'inputFilterResponse' => Controller\Api\Plugin\InputFilterResponse::class,
            'inputResponse'       => Controller\Api\Plugin\InputResponse::class
        ],
        'factories' => [
            'car'         => Controller\Plugin\Service\CarFactory::class,
            'catalogue'   => Controller\Plugin\Service\CatalogueFactory::class,
            'fileSize'    => Controller\Plugin\Service\FileSizeFactory::class,
            'language'    => Controller\Plugin\Service\LanguageFactory::class,
            'log'         => Controller\Plugin\Service\LogFactory::class,
            'oauth2'      => Factory\OAuth2PluginFactory::class,
            'pic'         => Controller\Plugin\Service\PicFactory::class,
            'pictureVote' => Controller\Plugin\Service\PictureVoteFactory::class,
            'sidebar'     => Controller\Plugin\Service\SidebarFactory::class,
            'translate'   => Controller\Plugin\Service\TranslateFactory::class,
        ]
    ],
    'translator' => [
        'locale' => 'ru',
        'fallbackLocale' => 'en',
        'translation_file_patterns' => [
            [
                'type'     => PhpArray::class,
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php'
            ],
            [
                'type'     => PhpArray::class,
                'base_dir' => __DIR__ . '/../language/plural',
                'pattern'  => '%s.php'
            ],
            [
                'type'     => PhpArray::class,
                'base_dir' => \Zend\I18n\Translator\Resources::getBasePath(),
                'pattern'  => \Zend\I18n\Translator\Resources::getPatternForValidator()
            ],
            [
                'type'     => PhpArray::class,
                'base_dir' => \Zend\I18n\Translator\Resources::getBasePath(),
                'pattern'  => \Zend\I18n\Translator\Resources::getPatternForCaptcha()
            ],
            [
                'type'     => PhpArray::class,
                'base_dir' => Resources::getBasePath(),
                'pattern'  => Resources::getPatternForViewHelpers()
            ]
        ],
    ],

    'service_manager' => [
        'factories' => [
            Acl::class                           => Permissions\AclFactory::class,
            Comments::class                      => Service\CommentsFactory::class,
            DuplicateFinder::class               => Service\DuplicateFinderFactory::class,
            FileSize::class                      => InvokableFactory::class,
            HostManager::class                   => Service\HostManagerFactory::class,
            HostnameCheckRouteListener::class    => HostnameCheckRouteListenerFactory::class,
            Language::class                      => Service\LanguageFactory::class,
            LanguagePicker::class                => Service\LanguagePickerFactory::class,
            MainMenu::class                      => Service\MainMenuFactory::class,
            Model\Brand::class                   => Model\BrandFactory::class,
            Model\BrandNav::class                => Model\Service\BrandNavFactory::class,
            Model\CarOfDay::class                => Model\Service\CarOfDayFactory::class,
            Model\Catalogue::class               => Model\Service\CatalogueFactory::class,
            Model\Categories::class              => Model\Service\CategoriesFactory::class,
            Model\Contact::class                 => Model\ContactFactory::class,
            Model\Item::class                    => Model\ItemFactory::class,
            Model\ItemAlias::class               => Model\ItemAliasFactory::class,
            Model\ItemParent::class              => Model\ItemParentFactory::class,
            Model\Log::class                     => Model\Service\LogFactory::class,
            Model\Modification::class            => Model\ModificationFactory::class,
            Model\Perspective::class             => Model\PerspectiveFactory::class,
            Model\Picture::class                 => Model\PictureFactory::class,
            Model\PictureItem::class             => Model\PictureItemFactory::class,
            Model\PictureModerVote::class        => Model\PictureModerVoteFactory::class,
            Model\PictureView::class             => Model\PictureViewFactory::class,
            Model\PictureVote::class             => Model\Service\PictureVoteFactory::class,
            Model\Referer::class                 => Model\RefererFactory::class,
            Model\Twins::class                   => Model\TwinsFactory::class,
            Model\UserPicture::class             => Model\Service\UserPictureFactory::class,
            Model\UserAccount::class             => Model\UserAccountFactory::class,
            Model\UserItemSubscribe::class       => Model\UserItemSubscribeFactory::class,
            Model\VehicleType::class             => Model\VehicleTypeFactory::class,
            PictureNameFormatter::class          => Service\PictureNameFormatterFactory::class,
            Service\Mosts::class                 => Service\MostsFactory::class,
            Service\PictureService::class        => Service\PictureServiceFactory::class,
            Service\SpecificationsService::class => Service\SpecificationsServiceFactory::class,
            Service\TelegramService::class       => Service\TelegramServiceFactory::class,
            Service\UsersService::class          => Service\UsersServiceFactory::class,
            ItemNameFormatter::class             => Service\ItemNameFormatterFactory::class,
            'translator'                         => TranslatorFactory::class,
            Provider\UserId\OAuth2UserIdProvider::class => Provider\UserId\OAuth2UserIdProviderFactory::class,
        ],
        'aliases' => [
            'ZF\OAuth2\Provider\UserId' => Provider\UserId\OAuth2UserIdProvider::class
        ],
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory'
        ]
    ],

    'telegram' => [
        'accessToken' => getenv('AUTOWP_TELEGRAM_ACCESS_TOKEN'),
        'token'       => getenv('AUTOWP_TELEGRAM_TOKEN'),
        'webhook'     => 'https://www.autowp.ru/telegram/webhook/token/' . getenv('AUTOWP_TELEGRAM_TOKEN')
    ],

    'twitter' => [
        'username' => getenv('AUTOWP_TWITTER_USERNAME'),
        'oauthOptions' => [
            'consumerKey'    => getenv('AUTOWP_TWITTER_OAUTH_KEY'),
            'consumerSecret' => getenv('AUTOWP_TWITTER_OAUTH_SECRET')
        ],
        'token' => [
            'oauth_token'        => getenv('AUTOWP_TWITTER_TOKEN_OAUTH'),
            'oauth_token_secret' => getenv('AUTOWP_TWITTER_TOKEN_OAUTH_SECRET')
        ]
    ],

    'facebook' => [
        'app_id' => getenv('AUTOWP_FACEBOOK_APP_ID'),
        'app_secret' => getenv('AUTOWP_FACEBOOK_APP_SECRET'),
        'page_access_token' => getenv('AUTOWP_FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

    'hosts' => [
        'en' => [
            'hostname' => 'en.' . $host,
            'timezone' => 'Europe/London',
            'name'     => 'English',
            'flag'     => 'flag-icon flag-icon-gb',
            'cookie'   => $hostCookie,
            'aliases'  => [
                'en.autowp.ru',
                $host,
                'www' . $host
            ]
        ],
        'zh' => [
            'hostname' => 'zh.' . $host,
            'timezone' => 'Asia/Shanghai',
            'name'     => '中文 (beta)',
            'flag'     => 'flag-icon flag-icon-cn',
            'cookie'   => $hostCookie,
            'aliases'  => []
        ],
        'ru' => [
            'hostname' => getenv('AUTOWP_HOST_RU'),
            'timezone' => 'Europe/Moscow',
            'name'     => 'Русский',
            'flag'     => 'flag-icon flag-icon-ru',
            'cookie'   => getenv('AUTOWP_HOST_COOKIE_RU'),
            'aliases'  => [
                'ru.autowp.ru'
            ]
        ],
        'pt-br' => [
            'hostname' => 'br.' . $host,
            'timezone' => 'Brazil/West',
            'name'     => 'Português brasileiro',
            'flag'     => 'flag-icon flag-icon-br',
            'cookie'   => $hostCookie,
            'aliases'  => []
        ],
        'fr' => [
            'hostname' => 'fr.' . $host,
            'timezone' => 'Europe/Paris',
            'name'     => 'Français (beta)',
            'flag'     => 'flag-icon flag-icon-fr',
            'cookie'   => $hostCookie,
            'aliases'  => []
        ],
        'be' => [
            'hostname' => 'be.' . $host,
            'timezone' => 'Europe/Minsk',
            'name'     => 'Беларуская',
            'flag'     => 'flag-icon flag-icon-by',
            'cookie'   => $hostCookie,
            'aliases'  => []
        ],
        'uk' => [
            'hostname' => 'uk.' . $host,
            'timezone' => 'Europe/Kiev',
            'name'     => 'Українська (beta)',
            'flag'     => 'flag-icon flag-icon-ua',
            'cookie'   => $hostCookie,
            'aliases'  => []
        ],
    ],

    'hostname_whitelist' => ['www.autowp.ru', 'ru.autowp.ru', 'en.autowp.ru',
        'i.' . $host, 'en.' . $host, 'fr.' . $host, 'ru.' . $host,
        'zh.' . $host, 'be.' . $host, 'br.' . $host, 'uk.' . $host, 'www.' . $host, $host],
    'force_https' => (bool) getenv('AUTOWP_FORCE_HTTPS'),

    'pictures_hostname' => getenv('AUTOWP_PICTURES_HOST'),

    'content_languages' => ['en', 'ru', 'uk', 'be', 'fr', 'it', 'zh', 'pt', 'de', 'es'],

    /*'acl' => [
        'cache'         => 'long',
        'cacheLifetime' => 3600
    ],*/

    'textstorage' => [
        'textTableName'     => 'textstorage_text',
        'revisionTableName' => 'textstorage_revision',
    ],

    'feedback' => [
        'from'     => 'no-reply@autowp.ru',
        'fromname' => 'robot autowp.ru',
        'to'       => 'autowp@gmail.com',
        'subject'  => 'AutoWP Feedback'
    ],

    'validators' => [
        'aliases' => [
            'ItemCatnameNotExists' => Validator\Item\CatnameNotExists::class,
        ],
        'factories' => [
            Validator\Attr\AttributeId::class            => Validator\Attr\AttributeIdFactory::class,
            Validator\Attr\TypeId::class                 => Validator\Attr\TypeIdFactory::class,
            Validator\Attr\UnitId::class                 => Validator\Attr\UnitIdFactory::class,
            Validator\Item\CatnameNotExists::class       => Validator\Item\CatnameNotExistsFactory::class,
            Validator\ItemParent\CatnameNotExists::class => Validator\ItemParent\CatnameNotExistsFactory::class,
            Validator\User\EmailExists::class            => Validator\User\EmailExistsFactory::class,
            Validator\User\EmailNotExists::class         => Validator\User\EmailNotExistsFactory::class,
            Validator\User\Login::class                  => Validator\User\LoginFactory::class ,
        ],
    ],

    'external_login_services' => [
        Vk::class => [
            'clientId'     => getenv('AUTOWP_ELS_VK_CLIENTID'),
            'clientSecret' => getenv('AUTOWP_ELS_VK_SECRET'),
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ],
        GooglePlus::class => [
            'clientId'     => getenv('AUTOWP_ELS_GOOGLEPLUS_CLIENTID'),
            'clientSecret' => getenv('AUTOWP_ELS_GOOGLEPLUS_SECRET'),
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ],
        Twitter::class => [
            'consumerKey'    => getenv('AUTOWP_ELS_TWITTER_CLIENTID'),
            'consumerSecret' => getenv('AUTOWP_ELS_TWITTER_SECRET'),
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ],
        Facebook::class => [
            'clientId'     => getenv('AUTOWP_ELS_FACEBOOK_CLIENTID'),
            'clientSecret' => getenv('AUTOWP_ELS_FACEBOOK_SECRET'),
            'scope'        => ['public_profile'],
            'graphApiVersion' => 'v3.2',
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ],
        Github::class => [
            'clientId'     => getenv('AUTOWP_ELS_GITHUB_CLIENTID'),
            'clientSecret' => getenv('AUTOWP_ELS_GITHUB_SECRET'),
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ],
        Linkedin::class => [
            'clientId'     => getenv('AUTOWP_ELS_LINKEDIN_CLIENTID'),
            'clientSecret' => getenv('AUTOWP_ELS_LINKEDIN_SECRET'),
            'redirectUri'  => 'https://en.'.$host.'/login/callback'
        ]
    ],

    'gulp-rev' => [
        'manifest' => __DIR__ . '/../../../public_html/dist/manifest.json',
        'prefix'   => '/dist/'
    ],

    'mosts_min_vehicles_count' => (int)getenv('AUTOWP_MOSTS_MIN_VEHICLES_COUNT'),

    'yandex' => [
        'secret' => getenv('AUTOWP_YANDEX_SECRET'),
        'price'  => (int)getenv('AUTOWP_YANDEX_PRICE')
    ],

    'vk' => [
        'token'    => getenv('AUTOWP_VK_TOKEN'),
        'owner_id' => getenv('AUTOWP_VK_OWNER_ID')
    ],

    'input_filters' => [
        'factories' => [
            InputFilter\AttrUserValueCollectionInputFilter::class => InputFilter\AttrUserValueCollectionInputFilterFactory::class
        ],
        'abstract_factories' => [
            InputFilterAbstractServiceFactory::class
        ]
    ],

    'users' => [
        'salt'      => getenv('AUTOWP_USERS_SALT'),
        'emailSalt' => getenv('AUTOWP_EMAIL_SALT')
    ],

    'mail' => [
        'transport' => $mailTransport
    ],

    'recaptcha' => [
        'publicKey'  => getenv('AUTOWP_RECAPTCHA_PUBLICKEY'),
        'privateKey' => getenv('AUTOWP_RECAPTCHA_PRIVATEKEY')
    ],

    'rollbar' => [
        'logger' => [
            'access_token' => getenv('ROLLBAR_ACCESS_TOKEN'),
            'environment'  => getenv('ROLLBAR_ENVIRONMENT')
        ],
        'debounce' => [
            'file'   => __DIR__ . '/../../../data/rollbar-debounce',
            'period' => 60
        ],
        'client_access_token' => getenv('ROLLBAR_CLIENT_ACCESS_TOKEN')
    ],

    'traffic' => [
        'url' => getenv('TRAFFIC_URL')
    ]
];
