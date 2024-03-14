<?php

use Contao\Image;
use Contao\StringUtil;

$GLOBALS['TL_DCA']['tl_iso_product_booking'] = [
    // Config
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'ptable' => 'tl_iso_product',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,start,stop' => 'index'
            ]
        ]
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['start'],
            'headerFields' => ['name', 'type', 'sku'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'copy' => [
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg'
            ],
            'cut' => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg'
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg'
            ]
        ]
    ],
    'palettes' => [
        '__selector__' => [],
        'default' => '{booking_legend},start,stop,count;{meta_legend},comment,document_number;'
    ],

    // Subpalettes
    'subpalettes' => [],

    // Fields
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product.title',
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'product_collection_id' => [
            'foreignKey' => 'tl_iso_product_collection.name',
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'product_collection_item_id' => [
            'foreignKey' => 'tl_iso_product_collection_item.id',
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'start' => [
            'exclude' => true,
            'inputType' => 'text',
            'flag' => 8,
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'stop' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'count' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'natural'],
            'sql' => "int(10) NOT NULL default '1'",
        ],
        'comment' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr'],
            'sql' => "text NULL",
        ],
        'document_number' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50 wizard', 'readonly' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];