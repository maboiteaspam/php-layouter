<?php
return [
    (object) [
        'id'        =>0,
        'created_at'=> date('Y-m-d H:i:s'),
        'updated_at'=> date('Y-m-d H:i:s'),
        'title'     =>'some',
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>'blog entry',
        'status'    =>'VISIBLE',
        'comments'=>[
            (object) [
                "id"            =>0,
                "blog_entry_id" =>0,
                "author"        => "John Doe",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "Agreed !",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>1,
                "blog_entry_id" =>0,
                "author"        => "John Connor",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "Yeah it s so right.",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>1,
        'created_at'=> date('Y-m-d H:i'),
        'updated_at'=> date('Y-m-d H:i'),
        'title'     =>'some 1',
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>'blog entry',
        'status'    =>'VISIBLE',
        'comments'=>[
            (object) [
                "id"            =>2,
                "blog_entry_id" =>1,
                "author"        => "Stanley Kubrick",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "Where are we going Exactly ?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>3,
                "blog_entry_id" =>1,
                "author"        => "Stanley Marsh",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "Where the rainbow ends !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>2,
        'created_at'=> date('Y-m-d H:i'),
        'updated_at'=> date('Y-m-d H:i'),
        'title'     =>'some 2',
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>'blog entry',
        'status'    =>'VISIBLE',
        'comments'  =>[
            (object) [
                "id"            =>4,
                "blog_entry_id" =>2,
                "author"        => "Kenny McCormick",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "MHMhhmhMHMhhmMH  !!?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>5,
                "blog_entry_id" =>2,
                "author"        => "Eric Cartman",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "oh Fuck they killed kenny !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>3,
        'created_at'=> date('Y-m-d H:i'),
        'updated_at'=> date('Y-m-d H:i'),
        'title'     =>'some 2',
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>'blog entry',
        'status'    =>'VISIBLE',
        'comments'  =>[
            (object) [
                "id"            =>6,
                "blog_entry_id" =>3,
                "author"        => "Kenny McCormick",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "MHMhhmhMHMhhmMH  !!?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>7,
                "blog_entry_id" =>3,
                "author"        => "Eric Cartman",
                "created_at"    =>"215-09-02 21:21:21",
                "updated_at"    =>"215-09-02 21:21:21",
                "content"       => "oh Fuck they killed kenny !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
];