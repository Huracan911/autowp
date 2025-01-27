<?php

namespace Application\Controller\Api;

use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblemResponse;

use Autowp\User\Controller\Plugin\User;

use Application\Controller\Plugin\ForbiddenAction;

/**
 * Class PictureModerVoteTemplateController
 * @package Application\Controller\Api
 *
 * @method User user($user = null)
 * @method ForbiddenAction forbiddenAction()
 * @method ApiProblemResponse inputFilterResponse(InputFilter $inputFilter)
 */
class PictureModerVoteTemplateController extends AbstractRestfulController
{
    /**
     * @var TableGateway
     */
    private $table;

    /**
     * @var InputFilter
     */
    private $createInputFilter;

    public function __construct(InputFilter $createInputFilter, TableGateway $table)
    {
        $this->table = $table;
        $this->createInputFilter = $createInputFilter;
    }

    public function indexAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $user = $this->user()->get();

        $select = new Sql\Select($this->table->getTable());
        $select
            ->columns(['id', 'reason', 'vote'])
            ->where(['user_id' => $user['id']])
            ->order('reason');

        $items = [];
        foreach ($this->table->selectWith($select) as $row) {
            $items[] = [
                'id'   => (int)$row['id'],
                'name' => $row['reason'],
                'vote' => (int)$row['vote']
            ];
        }

        return new JsonModel([
            'items' => $items
        ]);
    }

    public function itemAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $user = $this->user()->get();

        $select = new Sql\Select($this->table->getTable());
        $select
            ->columns(['id', 'reason', 'vote'])
            ->where([
                'user_id' => $user['id'],
                'id'      => (int)$this->params('id')
            ]);

        $row = $this->table->selectWith($select)->current();
        if (! $row) {
            return $this->notFoundAction();
        }

        return new JsonModel([
            'id'   => (int)$row['id'],
            'name' => $row['reason'],
            'vote' => (int)$row['vote']
        ]);
    }

    public function deleteAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $this->table->delete([
            'user_id' => $this->user()->get()['id'],
            'id'      => (int)$this->params('id')
        ]);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function createAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $user = $this->user()->get();

        $this->createInputFilter->setData(
            $this->processBodyContent($this->getRequest())
        );

        if (! $this->createInputFilter->isValid()) {
            return $this->inputFilterResponse($this->createInputFilter);
        }

        $data = $this->createInputFilter->getValues();


        $this->table->insert([
            'user_id' => $user['id'],
            'reason'  => $data['name'],
            'vote'    => $data['vote']
        ]);

        $id = $this->table->getLastInsertValue();

        $this->getResponse()->getHeaders()->addHeaderLine(
            'Location',
            $this->url()->fromRoute('api/picture-moder-vote-template/item/get', [
                'id' => $id
            ])
        );

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }
}
