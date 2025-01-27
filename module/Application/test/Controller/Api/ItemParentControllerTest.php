<?php

namespace ApplicationTest\Controller\Api;

use Zend\Http\Header\Cookie;
use Zend\Http\Request;
use Zend\Json\Json;

use Application\Test\AbstractHttpControllerTestCase;
use Application\Controller\Api\ItemParentController;

class ItemParentControllerTest extends AbstractHttpControllerTestCase
{
    protected $applicationConfigPath = __DIR__ . '/../../../../../config/application.config.php';

    /**
     * @suppress PhanUndeclaredMethod
     */
    public function testCategoriesFirstOrder()
    {
        $this->getRequest()->getHeaders()->addHeader(Cookie::fromString('Cookie: remember=admin-token'));
        $this->dispatch('https://www.autowp.ru/api/item-parent', Request::METHOD_GET, [
            'order' => 'categories_first'
        ]);

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName(ItemParentController::class);
        $this->assertMatchedRouteName('api/item-parent/list');
        $this->assertActionName('index');

        $json = Json::decode($this->getResponse()->getContent(), Json::TYPE_ARRAY);

        $this->assertNotEmpty($json);
    }
}
