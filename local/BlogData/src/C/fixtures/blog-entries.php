<?php
$lipsum = new joshtronic\LoremIpsum();

if (!function_exists("date_remove")) {
    function date_remove ($length, $d=null) {
        $d=$d===null?date('Y-m-d H:i:s'):$d;
        $date = date_create($d);
        date_sub($date, date_interval_create_from_date_string($length));
        return $date;
    }
}

return [
    (object) [
        'id'        => 0,
        'created_at'=> date_format(date_remove("0 days + 1 hour"),'Y-m-d H:i'),
        'updated_at'=> date_format(date_remove("0 days + 1 hour"),'Y-m-d H:i'),
        'title'     => $lipsum->words(rand(2,5)),
        'author'    => 'maboiteaspam',
        'img_alt'   => 'some',
        'content'   =>$lipsum->sentences(rand(1,3)),
        'status'    => 'VISIBLE',
        'comments'=>[
            (object) [
                "id"            => 0,
                "blog_entry_id" => 0,
                "author"        => "John Doe",
                "created_at"    => date_format(date_remove("0 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("0 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "Agreed !",
                'status'        => 'VISIBLE',
            ],
            (object) [
                "id"            => 1,
                "blog_entry_id" => 0,
                "author"        => "John Connor",
                "created_at"    => date_format(date_remove("0 days + 10 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("0 days + 10 minutes"),'Y-m-d H:i'),
                "content"       => "Yeah it s so right.",
                'status'        => 'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>1,
        'created_at'=> date_format(date_remove("1 days + 1 hour"),'Y-m-d H:i'),
        'updated_at'=> date_format(date_remove("1 days + 1 hour"),'Y-m-d H:i'),
        'title'     => $lipsum->words(rand(2,5)),
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>$lipsum->sentences(rand(1,3)),
        'status'    =>'VISIBLE',
        'comments'=>[
            (object) [
                "id"            =>2,
                "blog_entry_id" =>1,
                "author"        => "Stanley Kubrick",
                "created_at"    => date_format(date_remove("1 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("1 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "Where are we going Exactly ?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>3,
                "blog_entry_id" =>1,
                "author"        => "Stanley Marsh",
                "created_at"    => date_format(date_remove("1 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("1 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "Where the rainbow ends !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>2,
        'created_at'=> date_format(date_remove("2 days + 1 hour"),'Y-m-d H:i'),
        'updated_at'=> date_format(date_remove("2 days + 1 hour"),'Y-m-d H:i'),
        'title'     => $lipsum->words(rand(2,5)),
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>$lipsum->sentences(rand(1,3)),
        'status'    =>'VISIBLE',
        'comments'  =>[
            (object) [
                "id"            =>4,
                "blog_entry_id" =>2,
                "author"        => "Kenny McCormick",
                "created_at"    => date_format(date_remove("2 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("2 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "MHMhhmhMHMhhmMH  !!?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>5,
                "blog_entry_id" =>2,
                "author"        => "Eric Cartman",
                "created_at"    => date_format(date_remove("2 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("2 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "oh Fuck they killed kenny !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
    (object) [
        'id'        =>3,
        'created_at'=> date_format(date_remove("3 days + 1 hour"),'Y-m-d H:i'),
        'updated_at'=> date_format(date_remove("3 days + 1 hour"),'Y-m-d H:i'),
        'title'     => $lipsum->words(rand(2,5)),
        'author'    =>'maboiteaspam',
        'img_alt'   =>'some',
        'content'   =>$lipsum->sentences(rand(1,3)),
        'status'    =>'VISIBLE',
        'comments'  =>[
            (object) [
                "id"            =>6,
                "blog_entry_id" =>3,
                "author"        => "Kenny McCormick",
                "created_at"    => date_format(date_remove("3 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("3 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "MHMhhmhMHMhhmMH  !!?",
                'status'        =>'VISIBLE',
            ],
            (object) [
                "id"            =>7,
                "blog_entry_id" =>3,
                "author"        => "Eric Cartman",
                "created_at"    => date_format(date_remove("3 days + 5 minutes"),'Y-m-d H:i'),
                "updated_at"    => date_format(date_remove("3 days + 5 minutes"),'Y-m-d H:i'),
                "content"       => "oh Fuck they killed kenny !",
                'status'        =>'VISIBLE',
            ],
        ],
    ],
];