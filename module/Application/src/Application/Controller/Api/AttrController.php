<?php

namespace Application\Controller\Api;

use Exception;

use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Paginator;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

use Autowp\User\Model\User;

use Application\Controller\Plugin\ForbiddenAction;
use Application\Hydrator\Api\RestHydrator;
use Application\Model\Item;
use Application\Service\SpecificationsService;

/**
 * Class AttrController
 * @package Application\Controller\Api
 *
 * @method \Autowp\User\Controller\Plugin\User user($user = null)
 * @method ForbiddenAction forbiddenAction()
 * @method string language()
 * @method ApiProblemResponse inputFilterResponse(InputFilter $inputFilter)
 */
class AttrController extends AbstractRestfulController
{
    /**
     * @var Item
     */
    private $item;

    /**
     * @var SpecificationsService
     */
    private $specsService = null;

    /**
     * @var User
     */
    private $userModel;

    /**
     * @var RestHydrator
     */
    private $conflictHydrator;

    /**
     * @var RestHydrator
     */
    private $userValueHydrator;

    /**
     * @var RestHydrator
     */
    private $valueHydrator;

    /**
     * @var InputFilter
     */
    private $conflictListInputFilter;

    /**
     * @var InputFilter
     */
    private $userValueListInputFilter;

    /**
     * @var TableGateway
     */
    private $userValueTable;

    /**
     * @var InputFilter
     */
    private $userValuePatchQueryFilter;

    /**
     * @var InputFilter
     */
    private $userValuePatchDataFilter;

    /**
     * @var InputFilter
     */
    private $attributeListInputFilter;

    /**
     * @var InputFilter
     */
    private $attributePostInputFilter;

    /**
     * @var InputFilter
     */
    private $attributeItemGetInputFilter;

    /**
     * @var InputFilter
     */
    private $attributeHydrator;

    /**
     * @var InputFilter
     */
    private $valueListInputFilter;

    /**
     * @var InputFilter
     */
    private $attributeItemPatchInputFilter;

    /**
     * @var InputFilter
     */
    private $zoneAttributeListInputFilter;

    /**
     * @var InputFilter
     */
    private $zoneAttributePostInputFilter;

    /**
     * @var InputFilter
     */
    private $listOptionIndexInputFilter;

    /**
     * @var InputFilter
     */
    private $listOptionPostInputFilter;

    /**
     * @var TableGateway
     */
    private $zoneTable;

    /**
     * @var TableGateway
     */
    private $zoneAttributeTable;

    /**
     * @var TableGateway
     */
    private $typeTable;

    /**
     * @var TableGateway
     */
    private $listOptionTable;

    public function __construct(
        Item $item,
        SpecificationsService $specsService,
        User $userModel,
        RestHydrator $conflictHydrator,
        RestHydrator $userValueHydrator,
        RestHydrator $attributeHydrator,
        RestHydrator $valueHydrator,
        InputFilter $conflictListInputFilter,
        InputFilter $userValueListInputFilter,
        InputFilter $userValuePatchQueryFilter,
        InputFilter $userValuePatchDataFilter,
        InputFilter $attributeListInputFilter,
        InputFilter $attributePostInputFilter,
        InputFilter $attributeItemGetInputFilter,
        InputFilter $valueListInputFilter,
        InputFilter $attributeItemPatchInputFilter,
        InputFilter $zoneAttributeListInputFilter,
        InputFilter $zoneAttributePostInputFilter,
        InputFilter $listOptionIndexInputFilter,
        InputFilter $listOptionPostInputFilter,
        TableGateway $zoneTable,
        TableGateway $zoneAttributeTable,
        TableGateway $typeTable,
        TableGateway $listOptionTable
    ) {
        $this->item = $item;
        $this->specsService = $specsService;
        $this->userModel = $userModel;
        $this->conflictHydrator = $conflictHydrator;
        $this->conflictListInputFilter = $conflictListInputFilter;
        $this->userValueTable = $specsService->getUserValueTable();
        $this->userValueHydrator = $userValueHydrator;
        $this->attributeHydrator = $attributeHydrator;
        $this->valueHydrator = $valueHydrator;
        $this->userValueListInputFilter = $userValueListInputFilter;
        $this->attributePostInputFilter = $attributePostInputFilter;
        $this->userValuePatchQueryFilter = $userValuePatchQueryFilter;
        $this->userValuePatchDataFilter = $userValuePatchDataFilter;
        $this->attributeListInputFilter = $attributeListInputFilter;
        $this->attributeItemGetInputFilter = $attributeItemGetInputFilter;
        $this->valueListInputFilter = $valueListInputFilter;
        $this->attributeItemPatchInputFilter = $attributeItemPatchInputFilter;
        $this->zoneAttributeListInputFilter = $zoneAttributeListInputFilter;
        $this->zoneAttributePostInputFilter = $zoneAttributePostInputFilter;
        $this->listOptionIndexInputFilter = $listOptionIndexInputFilter;
        $this->listOptionPostInputFilter = $listOptionPostInputFilter;
        $this->zoneTable = $zoneTable;
        $this->zoneAttributeTable = $zoneAttributeTable;
        $this->typeTable = $typeTable;
        $this->listOptionTable = $listOptionTable;
    }

    public function conflictIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        $this->conflictListInputFilter->setData($this->params()->fromQuery());

        if (! $this->conflictListInputFilter->isValid()) {
            return $this->inputFilterResponse($this->conflictListInputFilter);
        }

        $values = $this->conflictListInputFilter->getValues();

        $data = $this->specsService->getConflicts($user['id'], $values['filter'], (int)$values['page'], 30);

        $this->conflictHydrator->setOptions([
            'fields'   => $values['fields'],
            'language' => $this->language(),
            'user_id'  => $user ? $user['id'] : null
        ]);

        $items = [];
        foreach ($data['conflicts'] as $conflict) {
            $items[] = $this->conflictHydrator->extract($conflict);
        }

        return new JsonModel([
            'items'     => $items,
            'paginator' => $data['paginator']->getPages()
        ]);
    }

    public function userValueIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->userValueListInputFilter->setData($this->params()->fromQuery());

        if (! $this->userValueListInputFilter->isValid()) {
            return $this->inputFilterResponse($this->userValueListInputFilter);
        }

        $values = $this->userValueListInputFilter->getValues();

        $select = new Sql\Select($this->userValueTable->getTable());

        $select->order('update_date DESC');

        $userId = (int)$values['user_id'];
        $itemId = (int)$values['item_id'];

        if (! $userId && ! $itemId) {
            return $this->forbiddenAction();
        }

        if ($userId) {
            $select->where(['user_id' => $userId]);
        }

        if ($itemId) {
            $select->where(['item_id' => $itemId]);
        }

        if ($values['exclude_user_id']) {
            $select->where(['user_id <> ?' => $values['exclude_user_id']]);
        }

        if ($values['zone_id']) {
            $select
                ->join(
                    'attrs_zone_attributes',
                    'attrs_user_values.attribute_id = attrs_zone_attributes.attribute_id',
                    []
                )
                ->where(['attrs_zone_attributes.zone_id' => $values['zone_id']]);
        }

        $paginator = new Paginator\Paginator(
            new Paginator\Adapter\DbSelect($select, $this->userValueTable->getAdapter())
        );

        $paginator
            ->setItemCountPerPage($values['limit'] ? $values['limit'] : 30)
            ->setPageRange(20)
            ->setCurrentPageNumber($values['page']);

        $this->userValueHydrator->setOptions([
            'fields'   => $values['fields'],
            'language' => $this->language()
        ]);

        $items = [];
        foreach ($paginator->getCurrentItems() as $row) {
            $items[] = $this->userValueHydrator->extract($row);
        }


        return new JsonModel([
            'paginator' => $paginator->getPages(),
            'items'     => $items
        ]);
    }

    public function userValueItemDeleteAction()
    {
        if (! $this->user()->isAllowed('specifications', 'admin')) {
            return $this->forbiddenAction();
        }

        $attributeId = (int)$this->params('attribute_id');
        $itemId = (int)$this->params('item_id');
        $userId = (int)$this->params('user_id');

        $this->specsService->deleteUserValue($attributeId, $itemId, $userId);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function userValuePatchAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->userValuePatchQueryFilter->setData($this->params()->fromQuery());

        if (! $this->userValuePatchQueryFilter->isValid()) {
            return $this->inputFilterResponse($this->userValuePatchQueryFilter);
        }

        $query = $this->userValuePatchQueryFilter->getValues();


        $this->userValuePatchDataFilter->setData($this->processBodyContent($this->getRequest()));

        if (! $this->userValuePatchDataFilter->isValid()) {
            return $this->inputFilterResponse($this->userValuePatchDataFilter);
        }

        $data = $this->userValuePatchDataFilter->getValues();

        $srcItemId = (int)$query['item_id'];

        if ($srcItemId) {
            $eUserValueRows = $this->userValueTable->select([
                'item_id' => $srcItemId
            ]);

            $dstItemId = (int)$data['item_id'];

            if ($dstItemId) {
                foreach ($eUserValueRows as $eUserValueRow) {
                    if ($dstItemId) {
                        $srcPrimaryKey = [
                            'item_id'      => $eUserValueRow['item_id'],
                            'attribute_id' => $eUserValueRow['attribute_id'],
                            'user_id'      => $eUserValueRow['user_id']
                        ];
                        $dstPrimaryKey = [
                            'item_id'      => $dstItemId,
                            'attribute_id' => $eUserValueRow['attribute_id'],
                            'user_id'      => $eUserValueRow['user_id']
                        ];
                        $set = [
                            'item_id' => $dstItemId
                        ];

                        $cUserValueRow = $this->userValueTable->select($dstPrimaryKey)->current();

                        if ($cUserValueRow) {
                            $rowId = implode('/', [
                                $dstItemId,
                                $eUserValueRow['attribute_id'],
                                $eUserValueRow['user_id']
                            ]);
                            throw new Exception("Value row $rowId already exists");
                        }

                        $attrRow = $this->specsService->getAttributeTable()->select([
                            'id' => $eUserValueRow['attribute_id']
                        ])->current();

                        if (! $attrRow) {
                            throw new Exception("Attr not found");
                        }

                        $dataTable = $this->specsService->getUserValueDataTable($attrRow['type_id']);

                        $eDataRows = [];
                        foreach ($dataTable->select($srcPrimaryKey) as $row) {
                            $eDataRows[] = $row;
                        }

                        foreach ($eDataRows as $eDataRow) {
                            // check for data row existance
                            $filter = $dstPrimaryKey;
                            if ($attrRow['multiple']) {
                                $filter['ordering'] = $eDataRow['ordering'];
                            }
                            $cDataRow = $dataTable->select($filter)->current();

                            if ($cDataRow) {
                                throw new Exception("Data row already exists");
                            }
                        }

                        $this->userValueTable->update($set, $srcPrimaryKey);

                        foreach ($eDataRows as $eDataRow) {
                            $filter = $srcPrimaryKey;
                            if ($attrRow['multiple']) {
                                $filter['ordering'] = $eDataRow['ordering'];
                            }

                            $dataTable->update($set, $filter);
                        }
                    }

                    if ($dstItemId) {
                        $this->specsService->updateActualValues($dstItemId);
                        if ($srcItemId) {
                            $this->specsService->updateActualValues($eUserValueRow['item_id']);
                        }
                    }
                }
            }
        }

        if ($data['items']) {
            foreach ($data['items'] as $item) {
                if ($item['user_id'] != $user['id']) {
                    return $this->forbiddenAction();
                }

                $this->specsService->setUserValue2(
                    $item['user_id'],
                    $item['attribute_id'],
                    $item['item_id'],
                    $item['value'],
                    (bool)$item['empty']
                );
            }
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(200);
    }

    /**
     * @suppress PhanDeprecatedFunction
     */
    public function attributePostAction()
    {
        if (! $this->user()->isAllowed('attrs', 'edit')) {
            return $this->forbiddenAction();
        }

        $attributeTable = $this->specsService->getAttributeTable();

        $request = $this->getRequest();

        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->jsonDecode($request->getContent());
        } else {
            /* @phan-suppress-next-line PhanUndeclaredMethod */
            $data = $request->getPost()->toArray();
        }

        $this->attributePostInputFilter->setData($data);

        if (! $this->attributePostInputFilter->isValid()) {
            return $this->inputFilterResponse($this->attributePostInputFilter);
        }

        $values = $this->attributePostInputFilter->getValues();

        $parentId = $values['parent_id'] ? $values['parent_id'] : null;

        $select = $attributeTable->getSql()->select()
            ->columns(['max' => new Sql\Expression('max(position)')])
            ->where(['parent_id' => $parentId]);

        $row = $attributeTable->selectWith($select)->current();

        $max = $row ? (int)$row['max'] : 0;

        $set = [
            'name'        => $values['name'],
            'parent_id'   => $parentId,
            'type_id'     => $values['type_id'] ? $values['type_id'] : null,
            'description' => $values['description'],
            'unit_id'     => $values['unit_id'] ? $values['unit_id'] : null,
            'precision'   => strlen($values['precision']) > 0 ? $values['precision'] : null,
            'position'    => $max + 1
        ];

        $attributeTable->insert($set);

        $id = $attributeTable->getLastInsertValue();

        $url = $this->url()->fromRoute('api/attr/attribute/item/get', [
            'id' => $id
        ]);
        $this->getResponse()->getHeaders()->addHeaderLine('Location', $url);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }

    public function attributeItemGetAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->attributeItemGetInputFilter->setData($this->params()->fromQuery());

        if (! $this->attributeItemGetInputFilter->isValid()) {
            return $this->inputFilterResponse($this->attributeItemGetInputFilter);
        }

        $values = $this->attributeItemGetInputFilter->getValues();

        $attribute = $this->specsService->getAttribute($this->params('id'));

        if (! $attribute) {
            return $this->notFoundAction();
        }

        $this->attributeHydrator->setOptions([
            'fields'   => $values['fields'],
            'language' => $this->language(),
            'user_id'  => $user ? $user['id'] : null
        ]);

        return new JsonModel($this->attributeHydrator->extract($attribute));
    }

    public function attributeIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->attributeListInputFilter->setData($this->params()->fromQuery());

        if (! $this->attributeListInputFilter->isValid()) {
            return $this->inputFilterResponse($this->attributeListInputFilter);
        }

        $values = $this->attributeListInputFilter->getValues();

        $attributes = $this->specsService->getAttributes([
            'parent'    => (int) $values['parent_id'],
            'zone'      => $values['zone_id'],
            'recursive' => (bool) $values['recursive']
        ]);

        $this->attributeHydrator->setOptions([
            'fields'   => $values['fields'],
            'language' => $this->language(),
            'user_id'  => $user ? $user['id'] : null
        ]);

        $items = [];
        foreach ($attributes as $row) {
            $items[] = $this->attributeHydrator->extract($row);
        }

        return new JsonModel([
            'items'     => $items
        ]);
    }

    public function valueIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->valueListInputFilter->setData($this->params()->fromQuery());

        if (! $this->valueListInputFilter->isValid()) {
            return $this->inputFilterResponse($this->valueListInputFilter);
        }

        $values = $this->valueListInputFilter->getValues();

        $select = $this->specsService->getValueTable()->getSql()->select();

        $select->order('update_date DESC');

        $itemId = (int)$values['item_id'];

        if (! $itemId) {
            return $this->forbiddenAction();
        }

        if ($itemId) {
            $select->where(['item_id' => $itemId]);
        }

        if ($values['zone_id']) {
            $select
                ->join(
                    'attrs_zone_attributes',
                    'attrs_values.attribute_id = attrs_zone_attributes.attribute_id',
                    []
                )
                ->where(['attrs_zone_attributes.zone_id' => $values['zone_id']]);
        }

        $paginator = new Paginator\Paginator(
            new Paginator\Adapter\DbSelect($select, $this->userValueTable->getAdapter())
        );

        $paginator
            ->setItemCountPerPage($values['limit'])
            ->setPageRange(20)
            ->setCurrentPageNumber($values['page']);

        $this->valueHydrator->setOptions([
            'fields'   => $values['fields'],
            'language' => $this->language(),
            'user_id'  => $user ? $user['id'] : null
        ]);

        $items = [];
        foreach ($paginator->getCurrentItems() as $row) {
            $items[] = $this->valueHydrator->extract($row);
        }


        return new JsonModel([
            'paginator' => $paginator->getPages(),
            'items'     => $items
        ]);
    }

    public function zoneIndexAction()
    {
        $zones = [];
        foreach ($this->zoneTable->select([]) as $row) {
            $zones[] = [
                'id'   => (int)$row['id'],
                'name' => $row['name']
            ];
        }

        return new JsonModel([
            'items' => $zones
        ]);
    }

    public function attributeItemPatchAction()
    {
        if (! $this->user()->isAllowed('attrs', 'edit')) {
            return $this->forbiddenAction();
        }

        $attributeTable = $this->specsService->getAttributeTable();

        $attribute = $attributeTable->select(['id' => (int)$this->params('id')])->current();
        if (! $attribute) {
            return $this->notFoundAction();
        }

        $data = $this->processBodyContent($this->getRequest());

        $fields = [];
        foreach (array_keys($data) as $key) {
            if ($this->attributeItemPatchInputFilter->has($key)) {
                $fields[] = $key;
            }
        }

        if (! $fields) {
            return new ApiProblemResponse(new ApiProblem(400, 'No fields provided'));
        }

        $this->attributeItemPatchInputFilter->setValidationGroup($fields);

        $this->attributeItemPatchInputFilter->setData($data);

        if (! $this->attributeItemPatchInputFilter->isValid()) {
            return $this->inputFilterResponse($this->attributeItemPatchInputFilter);
        }

        $values = $this->attributeItemPatchInputFilter->getValues();

        $set = [];

        if (isset($values['name'])) {
            $set['name'] = $values['name'];
        }

        if (isset($values['type_id'])) {
            $set['type_id'] = $values['type_id'] ? $values['type_id'] : null;
        }

        if (isset($values['description'])) {
            $set['description'] = $values['description'];
        }

        if (isset($values['unit_id'])) {
            $set['unit_id'] = $values['unit_id'] ? $values['unit_id'] : null;
        }

        if (isset($values['precision'])) {
            $set['precision'] = strlen($values['precision']) > 0 ? $values['precision'] : null;
        }

        if ($set) {
            $attributeTable->update($set, [
                'id' => $attribute['id']
            ]);
        }

        if (isset($values['move'])) {
            switch ($values['move']) {
                case 'up':
                    $select = new Sql\Select($attributeTable->getTable());
                    $select->where(['attrs_attributes.position < ?' => $attribute['position']])
                        ->order('attrs_attributes.position DESC')
                        ->limit(1);
                    if ($attribute['parent_id']) {
                        $select->where(['attrs_attributes.parent_id' => $attribute['parent_id']]);
                    } else {
                        $select->where(['attrs_attributes.parent_id IS NULL']);
                    }
                    $prev = $attributeTable->selectWith($select)->current();

                    if ($prev) {
                        $prevPos = $prev['position'];
                        $pagePos = $attribute['position'];

                        $this->setAttributePosition($prev['id'], 10000);
                        $this->setAttributePosition($attribute['id'], $prevPos);
                        $this->setAttributePosition($prev['id'], $pagePos);
                    }
                    break;

                case 'down':
                    $select = new Sql\Select($attributeTable->getTable());
                    $select->where(['attrs_attributes.position > ?' => $attribute['position']])
                        ->order('attrs_attributes.position ASC')
                        ->limit(1);
                    if ($attribute['parent_id']) {
                        $select->where(['attrs_attributes.parent_id' => $attribute['parent_id']]);
                    } else {
                        $select->where(['attrs_attributes.parent_id IS NULL']);
                    }
                    $next = $attributeTable->selectWith($select)->current();

                    if ($next) {
                        $nextPos = $next['position'];
                        $pagePos = $attribute['position'];

                        $this->setAttributePosition($next['id'], 10000);
                        $this->setAttributePosition($attribute['id'], $nextPos);
                        $this->setAttributePosition($next['id'], $pagePos);
                    }
                    break;
            }
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(200);
    }

    private function setAttributePosition(int $attributeId, int $position)
    {
        $this->specsService->getAttributeTable()->update([
            'position' => $position
        ], [
            'id' => $attributeId
        ]);
    }

    public function zoneAttributeIndexAction()
    {
        $this->zoneAttributeListInputFilter->setData($this->params()->fromQuery());

        if (! $this->zoneAttributeListInputFilter->isValid()) {
            return $this->inputFilterResponse($this->zoneAttributeListInputFilter);
        }

        $values = $this->zoneAttributeListInputFilter->getValues();

        $select = $this->zoneAttributeTable->getSql()->select();

        $select->where(['zone_id' => (int)$values['zone_id']]);

        $items = [];
        foreach ($this->zoneAttributeTable->selectWith($select) as $row) {
            $items[] = [
                'zone_id'      => (int)$row['zone_id'],
                'attribute_id' => (int)$row['attribute_id'],
            ];
        }

        return new JsonModel([
            'items' => $items
        ]);
    }

    /**
     * @suppress PhanDeprecatedFunction
     */
    public function zoneAttributePostAction()
    {
        if (! $this->user()->isAllowed('attrs', 'edit')) {
            return $this->forbiddenAction();
        }

        $request = $this->getRequest();

        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->jsonDecode($request->getContent());
        } else {
            /* @phan-suppress-next-line PhanUndeclaredMethod */
            $data = $request->getPost()->toArray();
        }

        $this->zoneAttributePostInputFilter->setData($data);

        if (! $this->zoneAttributePostInputFilter->isValid()) {
            return $this->inputFilterResponse($this->zoneAttributePostInputFilter);
        }

        $values = $this->zoneAttributePostInputFilter->getValues();

        $select = new Sql\Select($this->zoneAttributeTable->getTable());
        $select->columns(['max' => new Sql\Expression('MAX(position)')])
            ->where(['zone_id' => $values['zone_id']]);

        $row = $this->zoneAttributeTable->selectWith($select)->current();
        $maxPosition = $row ? $row['max'] : 0;


        $this->zoneAttributeTable->insert([
            'zone_id'      => $values['zone_id'],
            'attribute_id' => $values['attribute_id'],
            'position'     => $maxPosition + 1
        ]);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }

    public function zoneAttributeItemDeleteAction()
    {
        if (! $this->user()->isAllowed('attrs', 'edit')) {
            return $this->forbiddenAction();
        }

        $zoneId = (int)$this->params('zone_id');
        $attributeId = (int)$this->params('attribute_id');

        $this->zoneAttributeTable->delete([
            'zone_id'      => $zoneId,
            'attribute_id' => $attributeId
        ]);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function attributeTypeIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $items = [];
        foreach ($this->typeTable->select([]) as $type) {
            $items[] = [
                'id'   => (int)$type['id'],
                'name' => $type['name']
            ];
        }

        return new JsonModel([
            'items' => $items
        ]);
    }

    public function unitIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        return new JsonModel([
            'items' => array_values($this->specsService->getUnits())
        ]);
    }

    public function listOptionIndexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        if (! $this->user()->isAllowed('specifications', 'edit')) {
            return $this->forbiddenAction();
        }

        $this->listOptionIndexInputFilter->setData($this->params()->fromQuery());

        if (! $this->listOptionIndexInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listOptionIndexInputFilter);
        }

        $values = $this->listOptionIndexInputFilter->getValues();

        $listOptions = $this->specsService->getListOptionsArray($values['attribute_id']);

        return new JsonModel([
            'items' => $listOptions
        ]);
    }

    /**
     * @suppress PhanDeprecatedFunction
     */
    public function listOptionPostAction()
    {
        if (! $this->user()->isAllowed('attrs', 'edit')) {
            return $this->forbiddenAction();
        }

        $request = $this->getRequest();

        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->jsonDecode($request->getContent());
        } else {
            /* @phan-suppress-next-line PhanUndeclaredMethod */
            $data = $request->getPost()->toArray();
        }

        $this->listOptionPostInputFilter->setData($data);

        if (! $this->listOptionPostInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listOptionPostInputFilter);
        }

        $values = $this->listOptionPostInputFilter->getValues();

        $select = $this->listOptionTable->getSql()->select()
            ->columns(['max' => new Sql\Expression('MAX(position)')])
            ->where(['attribute_id' => $values['attribute_id']]);

        $row = $this->listOptionTable->selectWith($select)->current();
        $max = $row ? (int)$row['max'] : 0;


        $this->listOptionTable->insert([
            'name'          => $values['name'],
            'attribute_id'  => $values['attribute_id'],
            'parent_id'     => $values['parent_id'] ? $values['parent_id'] : null,
            'position'      => 1 + $max
        ]);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }
}
