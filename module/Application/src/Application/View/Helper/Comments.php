<?php

namespace Application\View\Helper;

use Autowp\Comments\CommentsService;

use Zend\View\Helper\AbstractHelper;

class Comments extends AbstractHelper
{
    /**
     * @var CommentsService
     */
    private $comments;

    private $form;

    public function __construct($form, CommentsService $comments)
    {
        $this->form = $form;
        $this->comments = $comments;
    }

    public function __invoke(array $options)
    {
        $defaults = [
            'type'    => null,
            'item_id' => null
        ];
        $options = array_replace($defaults, $options);

        $type = (int)$options['type'];
        $item = (int)$options['item_id'];

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $user = $this->view->user()->get();

        $comments = $this->comments->get($type, $item, $user ? $user['id'] : 0);

        if ($user) {
            $this->comments->updateTopicView($type, $item, $user['id']);
        }

        $canAddComments = (bool)$user;
        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $canRemoveComments = $this->view->user()->isAllowed('comment', 'remove');

        $form = null;
        if ($canAddComments) {
            $form = $this->form;

            $form->setAttribute('action', $this->view->url('comments/add', [
                'type_id'    => $type,
                'item_id'    => $item
            ]));
            // TODO: 'canModeratorAttention' => $this->view->user()->isAllowed('comment', 'moderator-attention'),
        }

        if ($user) {
            $this->comments->markSubscriptionAwaiting($type, $item, $user['id']);
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->view->partial('application/comments/comments', [
            'comments'          => $comments,
            'itemId'            => $item,
            'type'              => $type,
            'canAddComments'    => $canAddComments,
            'canRemoveComments' => $canRemoveComments,
            'form'              => $form
        ]);
    }
}
