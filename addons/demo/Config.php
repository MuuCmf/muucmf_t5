<?php

return [
    'can_show'=> [
        'title'=>'是否允许显示',
        'type'=>'radio',
        'options'=> [
            '0'=>'不允许',
            '1'=>'允许'
        ],
        'value'=>'0',
        'tip'=>'开启测试，请谨慎开启'
    ],

    'title'=> [
        'title'=>'文本框',
        'type'=>'text',
        'value'=>'0',
        'tip'=>'文本框演示'
    ],

    'select'=> [
        'title'=>'下拉框',
        'type'=>'select',
        'options'=> [
            '0'=>'演示选项1',
            '1'=>'演示选项2'
        ],
        'value'=>'0',
        'tip'=>'下拉框演示'
    ],
];