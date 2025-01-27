<?php

namespace Application;

use ArrayObject;
use Zend\Cache\Storage\StorageInterface;
use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Router\Http\TreeRouteStack;

use Autowp\Message\MessageService;

use Application\Model\Categories;

class MainMenu
{
    /**
     * @var TableGateway
     */
    private $pageTable;

    /**
     * @var TreeRouteStack
     */
    private $router;

    /**
     * @var Language
     */
    private $language;

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * @var Categories
     */
    private $categories;

    /**
     * @var array
     */
    private $icons = [
        29  => 'fa fa-fw fa-upload',
        89  => 'fa fa-fw fa-comment',
        136 => 'fa fa-fw fa-info',
        48  => 'fa fa-fw fa-user',
        90  => 'fa fa-fw fa-sign-out',
        124 => 'fa fa-fw fa fa-users',
        91  => 'fa fa-fw fa fa-pencil',
        211 => 'fa fa-fw fa-address-book'
    ];

    /**
     * @var array
     */
    private $hosts = [];

    private $translator;

    /**
     * @var LanguagePicker
     */
    private $languagePicker;

    /**
     * @var MessageService
     */
    private $message;

    public function __construct(
        TreeRouteStack $router,
        Language $language,
        StorageInterface $cache,
        $hosts,
        $translator,
        LanguagePicker $languagePicker,
        MessageService $message,
        Categories $categories,
        TableGateway $pageTable
    ) {

        $this->router = $router;
        $this->language = $language;
        $this->hosts = $hosts;
        $this->cache = $cache;

        $this->pageTable = $pageTable;

        $this->translator = $translator;
        $this->languagePicker = $languagePicker;
        $this->message = $message;
        $this->categories = $categories;
    }

    /**
     * @param int $id
     * @param boolean $logedIn
     * @param bool $full
     * @return array
     */
    private function getMenuData($id, bool $logedIn, bool $full = false)
    {
        $select = new Sql\Select($this->pageTable->getTable());
        $select
            ->columns(['id', 'url', 'class', 'guest_only', 'registered_only'])
            ->where(['pages.parent_id' => $id])
            ->order('pages.position');
        if (! $full) {
            if ($logedIn) {
                $select->where(['NOT pages.guest_only']);
            } else {
                $select->where(['NOT pages.registered_only']);
            }
        }

        $result = [];
        foreach ($this->pageTable->selectWith($select) as $row) {
            $key = 'page/' . $row['id'] . '/name';

            $name = $this->translator->translate($key);
            if (! $name) {
                $name = $this->translator->translate($key, null, 'en');
            }

            $result[] = [
                'id'    => $row['id'],
                'url'   => $row['url'],
                'name'  => $name,
                'class' => $row['class'],
                'guest_only'      => (bool)$row['guest_only'],
                'registered_only' => (bool)$row['registered_only']
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getCategoriesItems()
    {
        $language = $this->language->getLanguage();

        $key = 'ZF2_CATEGORY_MENU_9_' . $language;

        $categories = $this->cache->getItem($key, $success);
        if (! $success) {
            $categories = $this->categories->getCategoriesList(null, $language, null, 'name');

            $this->cache->setItem($key, $categories);
        }

        return $categories;
    }

    /**
     * @param boolean $logedIn
     * @param bool $full
     * @return array
     */
    private function getSecondaryMenu(bool $logedIn, bool $full = false)
    {
        $language = $this->language->getLanguage();

        $key = 'ZF2_SECOND_MENU_' .
                ($logedIn ? 'LOGED' : 'NOTLOGED') .
                ($full ? 'FULL' : 'NOTFULL') .
                '16_' . $language;

        $secondMenu = $this->cache->getItem($key, $success);
        if (! $success) {
            $secondMenu = $this->getMenuData(87, $logedIn, $full);

            foreach ($secondMenu as &$item) {
                if (isset($this->icons[$item['id']])) {
                    $item['icon'] = $this->icons[$item['id']];
                }
            }
            unset($item);

            $this->cache->setItem($key, $secondMenu);
        }

        return $secondMenu;
    }

    /**
     * @param boolean $logedIn
     * @param bool $full
     * @return array
     */
    private function getPrimaryMenu(bool $logedIn, bool $full = false)
    {
        $language = $this->language->getLanguage();

        $key = 'ZF2_MAIN_MENU_' .
                ($logedIn ? 'LOGED' : 'NOTLOGED') .
                ($full ? 'FULL' : 'NOTFULL') .
                '_10_' . $language;

        $pages = $this->cache->getItem($key, $success);
        if (! $success) {
            $pages = $this->getMenuData(2, $logedIn, $full);

            $this->cache->setItem($key, $pages);
        }

        return $pages;
    }

    /**
     * @param array|ArrayObject $user
     * @param bool $full
     * @return array
     */
    public function getMenu($user = null, $full = false)
    {
        $newMessages = 0;
        if ($user) {
            $newMessages = $this->message->getNewCount($user['id']);
        }

        $language = $this->language->getLanguage();

        $searchHostname = 'www.autowp.ru';

        foreach ($this->hosts as $itemLanguage => $item) {
            if ($itemLanguage == $language) {
                $searchHostname = $item['hostname'];
            }
        }

        $logedIn = (bool)$user;

        return [
            'pages'          => $this->getPrimaryMenu($logedIn, $full),
            'secondMenu'     => $this->getSecondaryMenu($logedIn, $full),
            'pm'             => $newMessages,
            'categories'     => $this->getCategoriesItems(),
            'languages'      => $this->languagePicker->getItems(),
            'language'       => $language,
            'searchHostname' => $searchHostname
        ];
    }
}
