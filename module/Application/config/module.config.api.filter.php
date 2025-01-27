<?php

namespace Application;

use Application\InputFilter\AttrUserValueCollectionInputFilter;
use Zend\InputFilter\InputFilter;

use Autowp\Comments\Attention;
use Autowp\Comments\CommentsService;
use Autowp\Forums\Forums;
use Autowp\Message\MessageService;
use Autowp\User\Model\User;
use Autowp\ZFComponents\Filter\SingleSpaces;

return [
    'input_filter_specs' => [
        'api_acl_roles_list' => [
            'recursive' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
        ],
        'api_acl_roles_post' => [
            'name' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
        ],
        'api_acl_roles_role_parents_post' => [
            'role' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
        ],
        'api_acl_rules_post' => [
            'role' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'resource' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'privilege' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'allowed' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_article_list' => [
            'catname' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['author', 'description']]
                    ]
                ]
            ],
        ],
        'api_attr_list_options_get' => [
            'attribute_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ]
        ],
        'api_attr_list_options_post' => [
            'attribute_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'name' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 255
                        ]
                    ]
                ]
            ],
        ],
        'api_attr_attribute_get' => [
            'zone_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'recursive' => [
                'required' => false
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['unit', 'childs', 'options']]
                    ]
                ]
            ],
        ],
        'api_attr_attribute_post' => [
            'parent_id' => [
                'required' => false,
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => Validator\Attr\AttributeId::class]
                ]
            ],
            'name' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'type_id' => [
                'required' => true,
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => Validator\Attr\TypeId::class]
                ]
            ],
            'precision' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'unit_id' => [
                'required' => false,
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => Validator\Attr\UnitId::class]
                ]
            ],
            'description' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces']
                ]
            ],
            'move' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => ['up', 'down']
                        ]
                    ]
                ]
            ]
        ],
        'api_attr_attribute_item_get' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['unit', 'childs', 'options']]
                    ]
                ]
            ],
        ],
        'api_attr_attribute_item_patch' => [
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'type_id' => [
                'required' => false,
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => Validator\Attr\TypeId::class]
                ]
            ],
            'precision' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'unit_id' => [
                'required' => false,
                'validators' => [
                    ['name' => 'Digits'],
                    ['name' => Validator\Attr\UnitId::class]
                ]
            ],
            'description' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces']
                ]
            ],
            'move' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => ['up', 'down']
                        ]
                    ]
                ]
            ]
        ],
        'api_attr_conflict_get' => [
            'filter' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                '0',
                                '1',
                                '-1',
                                'minus-weight'
                            ]
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['values']]
                    ]
                ]
            ],
        ],
        'api_attr_user_value_get' => [
            'zone_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'exclude_user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['user', 'item', 'path']]
                    ]
                ]
            ],
        ],
        'api_attr_user_value_patch_query' => [
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_attr_user_value_patch_data' => [
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'items' => [
                'type' => AttrUserValueCollectionInputFilter::class
            ]
        ],
        'api_attr_value_get' => [
            'zone_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name'    => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['value', 'value_text']]
                    ]
                ]
            ],
        ],
        'api_attr_zone_attribute_get' => [
            'zone_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_attr_zone_attribute_post' => [
            'zone_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'attribute_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_comments_get' => [
            'user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'user' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'moderator_attention' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Attention::NONE,
                                Attention::REQUIRED,
                                Attention::COMPLETED
                            ]
                        ]
                    ]
                ]
            ],
            'pictures_of_item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'no_parents' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['preview', 'text_html', 'user', 'url', 'replies', 'datetime', 'vote', 'user_vote', 'is_new', 'status']]
                    ]
                ]
            ],
            'order' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'date_desc',
                                'date_asc',
                                'vote_desc',
                                'vote_asc'
                            ]
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
        ],
        'api_comments_get_public' => [
            'user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'moderator_attention' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Attention::NONE,
                                Attention::REQUIRED,
                                Attention::COMPLETED
                            ]
                        ]
                    ]
                ]
            ],
            'pictures_of_item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'no_parents' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits']
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['text_html', 'user', 'url', 'replies', 'datetime', 'vote', 'user_vote', 'is_new']]
                    ]
                ]
            ],
            'order' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'date_desc',
                                'date_asc',
                                'vote_desc',
                                'vote_asc'
                            ]
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
        ],
        'api_comments_post' => [
            'item_id' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ],
            'type_id' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ],
            'message' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => CommentsService::MAX_MESSAGE_LENGTH
                        ]
                    ]
                ]
            ],
            'moderator_attention' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ],
            'parent_id' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ],
            'resolve' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ]
        ],
        'api_comments_put' => [
            'user_vote' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                '-1',
                                '1'
                            ]
                        ]
                    ]
                ]
            ],
            'deleted' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ],
            'item_id' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'Digits']
                ],
            ]
        ],
        'api_comments_item_get' => [
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['preview', 'text_html', 'user', 'url', 'replies', 'datetime', 'vote', 'user_vote', 'is_new', 'status', 'page']]
                    ]
                ]
            ],
        ],
        'api_contacts_list' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['avatar', 'gravatar']]
                    ]
                ]
            ],
        ],
        'api_feedback' => [
            'name' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'email' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'EmailAddress']
                ]
            ],
            'message' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ]
        ],
        'api_forum_theme_list' => [
            'theme_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['description', 'themes', 'last_topic', 'last_message', 'topics']]
                    ]
                ]
            ],
        ],
        'api_forum_theme_get' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['description', 'themes', 'last_topic', 'last_message', 'topics']]
                    ]
                ]
            ],
            'topics' => [
                'type' => InputFilter::class,
                'page' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ]
            ]
        ],
        'api_forum_topic_list' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['last_message', 'author', 'messages', 'theme', 'subscription']]
                    ]
                ]
            ],
            'subscription' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'theme_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
        ],
        'api_forum_topic_get' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['last_message', 'author', 'messages', 'theme', 'subscription']]
                    ]
                ]
            ]
        ],
        'api_forum_topic_post' => [
            'theme_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'name' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 100
                        ]
                    ]
                ]
            ],
            'text' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 1024 * 4
                        ]
                    ]
                ]
            ],
            'moderator_attention' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'subscription' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_forum_topic_put' => [
            'status' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Forums::STATUS_NORMAL,
                                Forums::STATUS_CLOSED,
                                Forums::STATUS_DELETED
                            ]
                        ]
                    ]
                ]
            ],
            'subscription' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'theme_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
        ],
        'api_inbox_get' => [
            'brand_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Digits']
                ]
            ],
            'date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
        ],
        'api_ip_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['hostname', 'rights', 'blacklist']]
                    ]
                ]
            ],
        ],
        'api_item_list' => [
            'catname' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'last_item' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'descendant' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'ancestor_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Model\Item::VEHICLE,
                                Model\Item::ENGINE,
                                Model\Item::CATEGORY,
                                Model\Item::TWINS,
                                Model\Item::BRAND,
                                Model\Item::FACTORY,
                                Model\Item::MUSEUM,
                                Model\Item::PERSON,
                                Model\Item::COPYRIGHT,
                            ]
                        ]
                    ]
                ]
            ],
            'vehicle_type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'vehicle_childs_type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'spec' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'], // Order matters in ItemController
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['childs_count', 'name_html',
                            'name_text', 'name_default', 'name_only', 'description',
                            'has_text', 'brands',
                            'spec_editor_url', 'specs_url', 'categories',
                            'twins_groups', 'url', 'more_pictures_url',
                            'preview_pictures', 'design', 'engine_vehicles',
                            'catname', 'is_concept', 'spec_id', 'begin_year',
                            'end_year', 'body', 'lat', 'lng',
                            'pictures_count', 'current_pictures_count',
                            'is_compiles_item_of_day', 'item_of_day_pictures',
                            'related_group_pictures', 'engine_id', 'attr_zone_id',
                            'descendants_count', 'has_child_specs', 'accepted_pictures_count',
                            'comments_topic_stat', 'front_picture', 'has_specs', 'alt_names']]
                    ]
                ]
            ],
            'order' => [
                'required'   => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'name',
                                'childs_count',
                                'id_desc',
                                'id_asc',
                                'age',
                                'name_nat',
                                'categories_first'
                            ]
                        ]
                    ]
                ]
            ],
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'name_exclude' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'no_parent' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'is_group' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'text' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'from_year' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'to_year' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'suggestions_to' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'engine_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'have_childs_of_type' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'have_common_childs_with' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'have_childs_with_parent_of_type' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'autocomplete' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'exclude_self_and_childs' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_types_of' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'descendant_pictures' => [
                'type' => InputFilter::class,
                'status' => [
                    'required' => false
                ],
                'type_id' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'InArray',
                            'options' => [
                                'haystack' => [
                                    Model\PictureItem::PICTURE_AUTHOR,
                                    Model\PictureItem::PICTURE_CONTENT,
                                    Model\PictureItem::PICTURE_COPYRIGHTS
                                ]
                            ]
                        ]
                    ]
                ],
                'owner_id' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ],
            ],
            'preview_pictures' => [
                'type' => InputFilter::class,
                'type_id' => [
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        [
                            'name' => 'InArray',
                            'options' => [
                                'haystack' => [
                                    Model\PictureItem::PICTURE_AUTHOR,
                                    Model\PictureItem::PICTURE_CONTENT,
                                    Model\PictureItem::PICTURE_COPYRIGHTS
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'related_groups_of' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'dateless' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_item_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['childs_count', 'name_html',
                            'name_text', 'name_default', 'name_only', 'description',
                            'has_text', 'brands',
                            'spec_editor_url', 'specs_url', 'categories',
                            'twins_groups', 'url', 'more_pictures_url',
                            'preview_pictures', 'design', 'engine_vehicles',
                            'catname', 'is_concept', 'spec_id', 'begin_year',
                            'end_year', 'body', 'lat', 'lng',
                            'pictures_count', 'current_pictures_count',
                            'is_compiles_item_of_day', 'item_of_day_pictures',
                            'related_group_pictures', 'engine_id', 'attr_zone_id',
                            'descendants_count', 'has_child_specs', 'accepted_pictures_count',
                            'comments_topic_stat', 'front_picture', 'has_specs', 'alt_names']]
                    ]
                ]
            ],
        ],
        'api_item_language_put' => [
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'text' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 4096
                        ]
                    ]
                ]
            ],
            'full_text' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 65536
                        ]
                    ]
                ]
            ],
        ],
        'api_item_link_index' => [
            'item_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
        ],
        'api_item_link_post' => [
            'item_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'url' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'type_id' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'default',
                                'official',
                                'club'
                            ]
                        ]
                    ]
                ]
            ],
        ],
        'api_item_link_put' => [
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'url' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255
                        ]
                    ]
                ]
            ],
            'type_id' => [
                'required'   => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'default',
                                'official',
                                'club'
                            ]
                        ]
                    ]
                ]
            ],
        ],
        'api_item_logo_put' => [
            'file' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'FileSize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'max' => 10 * 1024 * 1024
                        ]
                    ],
                    [
                        'name' => 'FileIsImage',
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'FileMimeType',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'mimeType' => 'image/png'
                        ]
                    ],
                    [
                        'name' => 'FileImageSize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'minWidth'  => 50,
                            'minHeight' => 50
                        ]
                    ],
                ]
            ]
        ],
        'api_item_parent_language_put' => [
            'name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\ItemParent::MAX_LANGUAGE_NAME
                        ]
                    ]
                ]
            ]
        ],
        'api_item_parent_list' => [
            'ancestor_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'concept' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
            ],
            'exclude_concept' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
            ],
            'item_type_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['item']]
                    ]
                ]
            ],
            'is_group' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'order' => [
                'required'   => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'name',
                                'childs_count',
                                'type_auto',
                                'categories_first'
                            ]
                        ]
                    ]
                ]
            ],
        ],
        'api_item_parent_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['item']]
                    ]
                ]
            ]
        ],
        'api_item_parent_post' => [
            'item_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'parent_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'type_id' => [
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Model\ItemParent::TYPE_DEFAULT,
                                Model\ItemParent::TYPE_TUNING,
                                Model\ItemParent::TYPE_SPORT,
                                Model\ItemParent::TYPE_DESIGN
                            ]
                        ]
                    ]
                ]
            ],
            'catname' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces'],
                    ['name' => 'StringToLower'],
                    ['name' => 'FilenameSafe']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => Model\ItemParent::MAX_CATNAME
                        ]
                    ]
                ]
            ]
        ],
        'api_item_parent_put' => [
            'type_id' => [
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Model\ItemParent::TYPE_DEFAULT,
                                Model\ItemParent::TYPE_TUNING,
                                Model\ItemParent::TYPE_SPORT,
                                Model\ItemParent::TYPE_DESIGN
                            ]
                        ]
                    ]
                ]
            ],
            'catname' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'SingleSpaces'],
                    ['name' => 'StringToLower'],
                    ['name' => 'FilenameSafe']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => Model\ItemParent::MAX_CATNAME
                        ]
                    ]
                ]
            ],
            'parent_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
        ],
        'api_log_list' => [
            'article_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'picture_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['user', 'pictures', 'items']]
                    ]
                ]
            ]
        ],
        'api_login' => [
            'login' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => null,
                            'max' => 50
                        ]
                    ],
                    ['name' => Validator\User\Login::class]
                ]
            ],
            'password' => [
                'required' => true
            ],
            'remember' => [
                'required'    => false,
                'allow_empty' => true
            ]
        ],
        'api_message_list' => [
            'user_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'folder' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'inbox',
                                'sent',
                                'system',
                                'dialog'
                            ]
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['author']]
                    ]
                ]
            ]
        ],
        'api_message_post' => [
            'user_id' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'text' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => MessageService::MAX_TEXT
                        ]
                    ]
                ]
            ]
        ],
        'api_new_get' => [
            'date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['pictures', 'item', 'item_pictures']]
                    ]
                ]
            ]
        ],
        'api_page_post' => [
            'parent_id' => [
                'required' => false
            ],
            'name' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_NAME
                        ]
                    ]
                ]
            ],
            'title' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_TITLE
                        ]
                    ]
                ]
            ],
            'breadcrumbs' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_BREADCRUMBS
                        ]
                    ]
                ]
            ],
            'url' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_URL
                        ]
                    ]
                ]
            ],
            'is_group_node' => [
                'required' => false
            ],
            'registered_only' => [
                'required' => false
            ],
            'guest_only' => [
                'required' => false
            ],
            'class' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_CLASS
                        ]
                    ]
                ]
            ]
        ],
        'api_page_put' => [
            'parent_id' => [
                'required' => false
            ],
            'name' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_NAME
                        ]
                    ]
                ]
            ],
            'title' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_TITLE
                        ]
                    ]
                ]
            ],
            'breadcrumbs' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_BREADCRUMBS
                        ]
                    ]
                ]
            ],
            'url' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_URL
                        ]
                    ]
                ]
            ],
            'is_group_node' => [
                'required' => false
            ],
            'registered_only' => [
                'required' => false
            ],
            'guest_only' => [
                'required' => false
            ],
            'class' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => Model\Page::MAX_CLASS
                        ]
                    ]
                ]
            ],
            'position' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'up',
                                'down'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'api_perspective_page_list' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['groups']]
                    ]
                ]
            ]
        ],
        'api_picture_list' => [
            'identity' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => [
                            'owner', 'thumbnail', 'moder_vote', 'votes',
                            'similar', 'comments_count', 'add_date', 'iptc',
                            'exif', 'image', 'items', 'special_name',
                            'copyrights', 'change_status_user', 'rights',
                            'moder_votes', 'moder_voted', 'is_last',
                            'accepted_count', 'crop', 'replaceable',
                            'perspective_item', 'siblings', 'ip',
                            'name_html', 'name_text', 'image_gallery_full',
                            'preview_large', 'dpi', 'point', 'authors',
                            'categories', 'twins', 'factories', 'of_links',
                            'copyright_blocks'
                        ]]
                    ]
                ]
            ],
            'status' => [
                'required' => false
            ],
            'car_type_id' => [
                'required' => false
            ],
            'perspective_id' => [
                'required' => false
            ],
            'exact_item_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'exact_item_link_type' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'exclude_item_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'type_id' => [
                'required' => false
            ],
            'comments' => [
                'required' => false
            ],
            'owner_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'replace' => [
                'required' => false
            ],
            'requests' => [
                'required' => false
            ],
            'special_name' => [
                'required' => false
            ],
            'lost' => [
                'required' => false
            ],
            'gps' => [
                'required' => false
            ],
            'order' => [
                'required' => false,
                'filters' => [
                    ['name' => 'Digits']
                ],
            ],
            'similar' => [
                'required' => false
            ],
            'add_date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
            'accept_date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
            'added_from' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
            'paginator' => [
                'type'    => InputFilter::class,
                'item_id' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits'],
                    ]
                ],
            ],
            'accepted_in_days' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ]
        ],
        'api_picture_list_public' => [
            'identity' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 32
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => [
                            'owner', 'thumbnail', 'votes',
                            'comments_count', 'name_html', 'name_text', 'image_gallery_full',
                            'preview_large', 'dpi', 'point', 'authors', 'categories', 'twins',
                            'factories', 'of_links', 'copyright_blocks'
                        ]]
                    ]
                ]
            ],
            'status' => [
                'required' => false
            ],
            'item_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'owner_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'order' => [
                'required' => false,
                'filters' => [
                    ['name' => 'Digits']
                ],
            ],
            'add_date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
            'accept_date' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
            'perspective_id' => [
                'required' => false
            ],
            'exact_item_id' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'exact_item_link_type' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'paginator' => [
                'type'    => InputFilter::class,
                'item_id' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits'],
                    ]
                ],
            ],
            'accepted_in_days' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ]
        ],
        'api_picture_post' => [
            'file' => [
                'required' => true,
                'validators' => [
                    [
                        'name'    => 'FileSize',
                        'options' => [
                            'max'           => 1024 * 1024 * 100,
                            'useByteString' => false
                        ]
                    ],
                    ['name' => 'FileIsImage'],
                    [
                        'name' => 'FileExtension',
                        'options' => [
                            'extension' => 'jpg,jpeg,jpe,png'
                        ]
                    ],
                    [
                        'name' => 'FileImageSize',
                        'options' => [
                            'minWidth'  => 640,
                            'minHeight' => 360,
                            'maxWidth'  => 10000,
                            'maxHeight' => 10000
                        ]
                    ]
                ]
            ],
            'comment' => [
                'required'   => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => CommentsService::MAX_MESSAGE_LENGTH
                        ]
                    ]
                ]
            ],
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'replace_picture_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'perspective_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ]
        ],
        'api_picture_edit' => [
            'taken_year' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1800,
                            'inclusive' => true
                        ]
                    ],
                    [
                        'name'    => 'LessThan',
                        'options' => [
                            'max'       => 2030,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'taken_month' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => range(1, 12)
                        ]
                    ]
                ]
            ],
            'taken_day' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => range(1, 31)
                        ]
                    ]
                ]
            ],
            'status' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Model\Picture::STATUS_INBOX,
                                Model\Picture::STATUS_ACCEPTED,
                                Model\Picture::STATUS_REMOVING,
                            ]
                        ]
                    ]
                ]
            ],
            'special_name' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => Model\Picture::MAX_NAME,
                        ]
                    ]
                ]
            ],
            'copyrights' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 65536,
                        ]
                    ]
                ]
            ],
            'crop' => [
                'required' => false,
                'left' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ],
                'top' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ],
                'width' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ],
                'height' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ],
                    'validators' => [
                        ['name' => 'Digits']
                    ]
                ]
            ],
            'replace_picture_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits']
                ]
            ],
            'point' => [
                'required' => false,
                'lat' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ]
                ],
                'lng' => [
                    'required' => false,
                    'filters'  => [
                        ['name' => 'StringTrim']
                    ]
                ]
            ],
        ],
        'api_picture_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => [
                            'owner', 'thumbnail', 'moder_vote', 'votes',
                            'similar', 'comments_count', 'add_date', 'iptc',
                            'exif', 'image', 'items', 'special_name',
                            'copyrights', 'change_status_user', 'rights',
                            'moder_votes', 'moder_voted', 'is_last',
                            'accepted_count', 'crop', 'replaceable',
                            'perspective_item', 'siblings', 'ip',
                            'name_html', 'name_text', 'image_gallery_full',
                            'preview_large', 'dpi', 'point', 'authors',
                            'categories', 'twins', 'factories', 'of_links',
                            'copyright_blocks'
                        ]]
                    ]
                ]
            ]
        ],
        'api_picture_item_list' => [
            'item_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'picture_id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ]
            ],
            'type' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                Model\PictureItem::PICTURE_CONTENT,
                                Model\PictureItem::PICTURE_AUTHOR
                            ]
                        ]
                    ]
                ]
            ],
            'order' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [
                                'status'
                            ]
                        ]
                    ]
                ]
            ],
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => [
                            'area', 'item', 'picture'
                        ]]
                    ]
                ]
            ]
        ],
        'api_picture_item_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => [
                            'area', 'item', 'picture'
                        ]]
                    ]
                ]
            ]
        ],
        'api_picture_moder_vote_template_list' => [
            'name' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => Model\PictureModerVote::MAX_LENGTH
                        ]
                    ]
                ]
            ],
            'vote' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [-1, 1]
                        ]
                    ]
                ]
            ]
        ],
        'api_restore_password_request' => [
            'email' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'                   => 'EmailAddress',
                        'break_chain_on_failure' => true
                    ],
                    ['name' => Validator\User\EmailExists::class]
                ]
            ],
        ],
        'api_restore_password_new' => [
            'code' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => null,
                            'max' => 500
                        ]
                    ],
                ]
            ],
            'password' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ]
                ]
            ],
            'password_confirm' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ],
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ]
                ]
            ]
        ],
        'api_user_item' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['last_online', 'reg_date', 'image', 'email', 'login', 'avatar', 'photo', 'gravatar', 'renames', 'is_moder', 'accounts', 'pictures_added', 'pictures_accepted_count', 'last_ip', 'timezone', 'language', 'votes_per_day',' votes_left', 'img', 'specs_weight', 'identity', 'gravatar_hash']]
                    ]
                ]
            ]
        ],
        'api_user_list' => [
            'limit' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 1,
                            'max' => 500
                        ]
                    ]
                ]
            ],
            'page' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'Digits'],
                    [
                        'name'    => 'GreaterThan',
                        'options' => [
                            'min'       => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'search' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'id' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'identity' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['last_online', 'reg_date', 'image', 'email', 'login', 'avatar', 'photo', 'gravatar', 'renames', 'is_moder' ,'accounts', 'pictures_added', 'pictures_accepted_count', 'last_ip', 'timezone', 'language', 'votes_per_day',' votes_left', 'img', 'specs_weight', 'identity', 'gravatar_hash']]
                    ]
                ]
            ]
        ],
        'api_user_put' => [
            'deleted' => [
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim']
                ]
            ],
            'name' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_NAME,
                            'max' => User::MAX_NAME
                        ]
                    ]
                ]
            ],
            'language' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => []
                        ]
                    ]
                ]
            ],
            'timezone' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => []
                        ]
                    ]
                ]
            ],
            'email' => [
                'required'   => true,
                'filters'  => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'                   => 'EmailAddress',
                        'break_chain_on_failure' => true
                    ],
                    ['name' => Validator\User\EmailNotExists::class]
                ]
            ],
            'password_old' => [
                'required' => true,
            ],
            'password' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ]
                ]
            ],
            'password_confirm' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ],
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ]
                ]
            ],
        ],
        'api_user_post' => [
            'email' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => null,
                            'max' => 50
                        ]
                    ],
                    [
                        'name'                   => 'EmailAddress',
                        'break_chain_on_failure' => true
                    ],
                    ['name' => Validator\User\EmailNotExists::class]
                ]
            ],
            'name' => [
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => SingleSpaces::class]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_NAME,
                            'max' => User::MAX_NAME
                        ]
                    ]
                ]
            ],
            'password' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ]
                ]
            ],
            'password_confirm' => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => User::MIN_PASSWORD,
                            'max' => User::MAX_PASSWORD
                        ]
                    ],
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ]
                ]
            ]
        ],
        'api_user_photo_post' => [
            'file' => [
                'required' => true,
                'validators' => [
                    /*[
                     'name' => 'FileCount',
                     'break_chain_on_failure' => true,
                     'options' => [
                     'min' => 1,
                     'max' => 1
                     ]
                     ],*/
                    [
                        'name' => 'FileSize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'max' => 4194304
                        ]
                    ],
                    [
                        'name' => 'FileIsImage',
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'FileExtension',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'extension' => 'jpg,jpeg,jpe,png,gif,bmp'
                        ]
                    ],
                    [
                        'name' => 'FileImageSize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'minWidth'  => 100,
                            'minHeight' => 100
                        ]
                    ],
                ]
            ]
        ],
        'api_voting_variant_vote_get' => [
            'fields' => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => Filter\Api\FieldsFilter::class,
                        'options' => ['fields' => ['user']]
                    ]
                ]
            ],
        ]
    ]
];
