<?php

namespace Application\Controller\Api;

use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Paginator;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblemResponse;

use Autowp\Image\Storage;
use Autowp\User\Controller\Plugin\User;

use Application\Controller\Plugin\ForbiddenAction;
use Application\Controller\Plugin\Pic;
use Application\Hydrator\Api\RestHydrator;
use Application\Model\Item;
use Application\Model\Log;
use Application\Model\Picture;
use Application\Model\PictureItem;

/**
 * Class PictureItemController
 * @package Application\Controller\Api
 *
 * @method User user($user = null)
 * @method ForbiddenAction forbiddenAction()
 * @method ApiProblemResponse inputFilterResponse(InputFilter $inputFilter)
 * @method string language()
 * @method void log(string $message, array $objects)
 * @method Storage imageStorage()
 * @method Pic pic()
 */
class PictureItemController extends AbstractRestfulController
{
    /**
     * @var PictureItem
     */
    private $pictureItem;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var RestHydrator
     */
    private $hydrator;

    /**
     * @var InputFilter
     */
    private $itemInputFilter;

    /**
     * @var InputFilter
     */
    private $listInputFilter;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var Picture
     */
    private $picture;

    public function __construct(
        PictureItem $pictureItem,
        Log $log,
        RestHydrator $hydrator,
        InputFilter $listInputFilter,
        InputFilter $itemInputFilter,
        Item $item,
        Picture $picture
    ) {
        $this->pictureItem = $pictureItem;
        $this->log = $log;
        $this->hydrator = $hydrator;
        $this->listInputFilter = $listInputFilter;
        $this->itemInputFilter = $itemInputFilter;
        $this->item = $item;
        $this->picture = $picture;
    }

    private function canChangePerspective($picture)
    {
        if ($this->user()->inheritsRole('moder')) {
            return true;
        }

        $currentUser = $this->user()->get();
        if (! $currentUser) {
            return false;
        }

        if ($picture['owner_id'] == $currentUser['id']) {
            if ($picture['status'] == Picture::STATUS_INBOX) {
                return true;
            }
        }

        return false;
    }

    public function indexAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $user = $this->user()->get();

        $this->listInputFilter->setData($this->params()->fromQuery());

        if (! $this->listInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listInputFilter);
        }

        $data = $this->listInputFilter->getValues();

        $table = $this->pictureItem->getTable();

        $select = $table->getSql()->select();

        if ($data['item_id']) {
            $select->where(['picture_item.item_id' => $data['item_id']]);
        }

        if ($data['picture_id']) {
            $select->where(['picture_item.picture_id' => $data['picture_id']]);
        }

        if ($data['type']) {
            $select->where(['picture_item.type' => $data['type']]);
        }

        if ($data['order']) {
            switch ($data['order']) {
                case 'status':
                    $select->join('pictures', 'picture_item.picture_id = pictures.id', [])
                        ->order(['pictures.status']);
                    break;
            }
        }

        $paginator = new Paginator\Paginator(
            new Paginator\Adapter\DbSelect($select, $table->getAdapter())
        );

        $paginator
            ->setItemCountPerPage(500)
            ->setCurrentPageNumber($this->params()->fromQuery('page'));

        $this->hydrator->setOptions([
            'language' => $this->language(),
            'fields'   => $data['fields'],
            'user_id'  => $user ? $user['id'] : null
        ]);

        $items = [];
        foreach ($paginator->getCurrentItems() as $row) {
            $items[] = $this->hydrator->extract($row);
        }

        return new JsonModel([
            'paginator' => get_object_vars($paginator->getPages()),
            'items'     => $items
        ]);
    }

    public function itemAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $userId = $this->user()->get()['id'];

        $picture = $this->picture->getRow(['id' => (int)$this->params('picture_id')]);
        if (! $picture) {
            return $this->notFoundAction();
        }

        $item = $this->item->getRow(['id' => (int)$this->params('item_id')]);
        if (! $item) {
            return $this->notFoundAction();
        }

        $type = (int)$this->params('type');

        $this->itemInputFilter->setData($this->params()->fromQuery());

        if (! $this->itemInputFilter->isValid()) {
            return $this->inputFilterResponse($this->itemInputFilter);
        }

        $data = $this->itemInputFilter->getValues();

        $this->hydrator->setOptions([
            'language' => $this->language(),
            'fields'   => $data['fields'],
            'user_id'  => $userId
        ]);

        $row = $this->pictureItem->getPictureItemData($picture['id'], $item['id'], $type);
        if (! $row) {
            return $this->notFoundAction();
        }

        return new JsonModel($this->hydrator->extract($row));
    }

    public function deleteAction()
    {
        $canMove = $this->user()->isAllowed('picture', 'move');
        if (! $canMove) {
            return $this->forbiddenAction();
        }

        $picture = $this->picture->getRow(['id' => (int)$this->params('picture_id')]);
        if (! $picture) {
            return $this->notFoundAction();
        }

        $item = $this->item->getRow(['id' => (int)$this->params('item_id')]);
        if (! $item) {
            return $this->notFoundAction();
        }

        $type = (int)$this->params('type');

        if ($this->pictureItem->isExists($picture['id'], $item['id'], $type)) {
            $this->pictureItem->remove($picture['id'], $item['id'], $type);

            $this->log(sprintf(
                'Картинка %s отвязана от %s',
                htmlspecialchars('#' . $picture['id']),
                htmlspecialchars('#' . $item['id'])
            ), [
                'items'    => $item['id'],
                'pictures' => $picture['id']
            ]);

            if ($picture['image_id']) {
                $this->imageStorage()->changeImageName($picture['image_id'], [
                    'pattern' => $this->picture->getFileNamePattern($picture['id'])
                ]);
            }
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function createAction()
    {
        $canMove = $this->user()->isAllowed('picture', 'move');
        if (! $canMove) {
            return $this->forbiddenAction();
        }

        $userId = $this->user()->get()['id'];

        $pictureId = (int)$this->params('picture_id');
        $itemId    = (int)$this->params('item_id');
        $type      = (int)$this->params('type');

        $picture = $this->picture->getRow(['id' => $pictureId]);
        if (! $picture) {
            return $this->notFoundAction();
        }

        $item = $this->item->getRow(['id' => $itemId]);
        if (! $item) {
            return $this->notFoundAction();
        }

        $data = $this->processBodyContent($this->getRequest());

        $this->pictureItem->add($picture['id'], $item['id'], $type);

        $perspectiveId = isset($data['perspective_id']) ? (int)$data['perspective_id'] : null;

        $this->pictureItem->setProperties($picture['id'], $item['id'], PictureItem::PICTURE_CONTENT, [
            'perspective' => $perspectiveId ? $perspectiveId : null
        ]);

        if ($picture['image_id']) {
            $this->imageStorage()->changeImageName($picture['image_id'], [
                'pattern' => $this->picture->getFileNamePattern($picture['id'])
            ]);
        }

        $namespace = new Container('Moder_Car');
        $namespace->lastCarId = $item['id'];

        $this->log->addEvent($userId, sprintf(
            'Картинка %s связана с %s',
            htmlspecialchars('#' . $picture['id']),
            htmlspecialchars('#' . $item['id'])
        ), [
            'items'    => $item['id'],
            'pictures' => $picture['id']
        ]);

        $url = $this->url()->fromRoute('api/picture-item/item/create', [
            'picture_id' => $picture['id'],
            'item_id'    => $item['id'],
            'type'       => $type
        ]);
        $this->getResponse()->getHeaders()->addHeaderLine('Location', $url);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }

    public function updateAction()
    {
        $pictureId = (int)$this->params('picture_id');
        $itemId    = (int)$this->params('item_id');
        $type      = (int)$this->params('type');

        $picture = $this->picture->getRow(['id' => $pictureId]);
        if (! $picture) {
            return $this->notFoundAction();
        }

        if (! $this->canChangePerspective($picture)) {
            return $this->forbiddenAction();
        }

        $data = $this->processBodyContent($this->getRequest());

        if (isset($data['perspective_id'])) {
            $perspectiveId = (int)$data['perspective_id'];

            $this->pictureItem->setProperties($picture['id'], $itemId, $type, [
                'perspective' => $perspectiveId ? $perspectiveId : null
            ]);

            $this->log(sprintf(
                'Установка ракурса картинки %s',
                htmlspecialchars($this->pic()->name($picture, $this->language()))
            ), [
                'pictures' => $picture['id']
            ]);
        }

        if (isset($data['area'])) {
            if (! $this->user()->inheritsRole('moder')) {
                return $this->forbiddenAction();
            }

            $item = $this->item->getRow(['id' => $itemId]);
            if (! $item) {
                return $this->notFoundAction();
            }

            $left = round($data['area']['left']);
            $top = round($data['area']['top']);
            $width = round($data['area']['width']);
            $height = round($data['area']['height']);

            $left = max(0, $left);
            $left = min($picture['width'], $left);
            $width = max(1, $width);
            $width = min($picture['width'], $width);

            $top = max(0, $top);
            $top = min($picture['height'], $top);
            $height = max(1, $height);
            $height = min($picture['height'], $height);

            if ($left > 0 || $top > 0 || $width < $picture['width'] || $height < $picture['height']) {
                $area = [
                    'left'   => $left,
                    'top'    => $top,
                    'width'  => $width,
                    'height' => $height
                ];
            } else {
                $area = [
                    'left'   => null,
                    'top'    => null,
                    'width'  => null,
                    'height' => null
                ];
            }
            $this->pictureItem->setProperties($picture['id'], $item['id'], $type, [
                'area' => $area
            ]);

            $this->log(sprintf(
                'Выделение области на картинке %s',
                htmlspecialchars($this->pic()->name($picture, $this->language()))
            ), [
                'pictures' => $picture['id'],
                'items'    => $item['id']
            ]);
        }

        if (isset($data['item_id'])) {
            $canMove = $this->user()->isAllowed('picture', 'move');
            if (! $canMove) {
                return $this->forbiddenAction();
            }

            $srcItem = $this->item->getRow(['id' => $itemId]);
            if (! $srcItem) {
                return $this->notFoundAction();
            }
            $dstItem = $this->item->getRow(['id' => (int)$data['item_id']]);
            if (! $dstItem) {
                return $this->notFoundAction();
            }

            $this->pictureItem->changePictureItem($picture['id'], $type, $srcItem['id'], $dstItem['id']);

            $userId = $this->user()->get()['id'];

            $this->log->addEvent($userId, sprintf(
                'Картинка %s перемещена из %s в %s',
                htmlspecialchars('#' . $picture['id']),
                htmlspecialchars('#' . $srcItem['id']),
                htmlspecialchars('#' . $dstItem['id'])
            ), [
                'items'    => [$srcItem['id'], $dstItem['id']],
                'pictures' => $picture['id']
            ]);

            $namespace = new Container('Moder_Car');
            $namespace->lastCarId = $dstItem['id'];
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(200);
    }
}
