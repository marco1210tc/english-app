<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    private $students;

    public function __construct()
    {
        $this->set();
    }

    public function set()
    {
        $students = collect([
                (object)[
                    'id'   => 1,
                    'code' => 'STU-001',
                    'pin'    => '1234',
                    'user' => (object)[
                        'name'   => 'SofíaDelCar Martínez',
                        'avatar' => 'https://ui-avatars.com/api/?name=Sofia+Martinez&background=0D8ABC&color=fff',
                    ],
                    'school' => (object)[
                        'grade'     => '1° Básico',
                        'section'   => 'A',
                        'classroom' => '1A',
                    ],
                    'meta' => (object)[
                        'initial'   => 'S',
                        'full_name' => 'Sofía Martínez',
                    ],
                ],

                (object)[
                    'id'   => 2,
                    'code' => 'STU-002',
                    'pin'    => '4321',
                    'user' => (object)[
                        'name'   => 'Diego Rojas',
                        'avatar' => 'https://ui-avatars.com/api/?name=Diego+Rojas&background=FF6B6B&color=fff',
                    ],
                    'school' => (object)[
                        'grade'     => '2° Básico',
                        'section'   => 'B',
                        'classroom' => '2B',
                    ],
                    'meta' => (object)[
                        'initial'   => 'D',
                        'full_name' => 'Diego Rojas',
                    ],
                ],

                (object)[
                    'id'   => 3,
                    'code' => 'STU-003',
                    'pin'    => '1111',
                    'user' => (object)[
                        'name'   => 'Lucía Fernández',
                        'avatar' => 'https://ui-avatars.com/api/?name=Lucia+Fernandez&background=FFD93D&color=000',
                    ],
                    'school' => (object)[
                        'grade'     => '1° Básico',
                        'section'   => 'B',
                        'classroom' => '1B',
                    ],
                    'meta' => (object)[
                        'initial'   => 'L',
                        'full_name' => 'Lucía Fernández',
                    ],
                ],

                (object)[
                    'id' => 4,
                    'code' => 'STU-004',
                    'pin'    => '1111',
                    'user' => (object)[
                        'name'   => 'Miguel Hernández',
                        'avatar' => 'https://ui-avatars.com/api/?name=Miguel+Hernandez&background=FFD93D&color=000',
                    ],
                    'school' => (object)[
                        'grade'     => '1° Básico',
                        'section'   => 'B',
                        'classroom' => '1B',
                    ],
                    'meta' => (object)[
                        'initial'   => 'M',
                        'full_name' => 'Miguel Hernández',
                    ],
                ],
                (object)[
                    'id' => 5,
                    'code' => 'STU-005',
                    'pin'    => '1111',
                    'user' => (object)[
                        'name'   => 'Ana López',
                        'avatar' => 'https://ui-avatars.com/api/?name=Miguel+Hernandez&background=FFD93D&color=000',
                    ],
                    'school' => (object)[
                        'grade'     => '1° Básico',
                        'section'   => 'B',
                        'classroom' => '1B',
                    ],
                    'meta' => (object)[
                        'initial'   => 'A',
                        'full_name' => 'Ana López',
                    ],
                    (object)[
                        'id'   => 6,
                        'code' => 'STU-006',
                        'pin'  => '2580',
                        'user' => (object)[
                            'name'   => 'Camila Torres',
                            'avatar' => 'https://ui-avatars.com/api/?name=Camila+Torres&background=6C5CE7&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '3° Básico',
                            'section'   => 'A',
                            'classroom' => '3A',
                        ],
                        'meta' => (object)[
                            'initial'   => 'C',
                            'full_name' => 'Camila Torres',
                        ],
                    ],

                    (object)[
                        'id'   => 7,
                        'code' => 'STU-007',
                        'pin'  => '9075',
                        'user' => (object)[
                            'name'   => 'Valentina Rivera',
                            'avatar' => 'https://ui-avatars.com/api/?name=Valentina+Rivera&background=E84393&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '3° Básico',
                            'section'   => 'B',
                            'classroom' => '3B',
                        ],
                        'meta' => (object)[
                            'initial'   => 'V',
                            'full_name' => 'Valentina Rivera',
                        ],
                    ],

                    (object)[
                        'id'   => 8,
                        'code' => 'STU-008',
                        'pin'  => '3344',
                        'user' => (object)[
                            'name'   => 'Carlos Muñoz',
                            'avatar' => 'https://ui-avatars.com/api/?name=Carlos+Munoz&background=00B894&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '2° Básico',
                            'section'   => 'A',
                            'classroom' => '2A',
                        ],
                        'meta' => (object)[
                            'initial'   => 'C',
                            'full_name' => 'Carlos Muñoz',
                        ],
                    ],

                    (object)[
                        'id'   => 9,
                        'code' => 'STU-009',
                        'pin'  => '7788',
                        'user' => (object)[
                            'name'   => 'Josefa Salazar',
                            'avatar' => 'https://ui-avatars.com/api/?name=Josefa+Salazar&background=FDCB6E&color=000',
                        ],
                        'school' => (object)[
                            'grade'     => '2° Básico',
                            'section'   => 'C',
                            'classroom' => '2C',
                        ],
                        'meta' => (object)[
                            'initial'   => 'J',
                            'full_name' => 'Josefa Salazar',
                        ],
                    ],

                    (object)[
                        'id'   => 10,
                        'code' => 'STU-010',
                        'pin'  => '9900',
                        'user' => (object)[
                            'name'   => 'Tomás Aguilera',
                            'avatar' => 'https://ui-avatars.com/api/?name=Tomas+Aguilera&background=0984E3&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '4° Básico',
                            'section'   => 'A',
                            'classroom' => '4A',
                        ],
                        'meta' => (object)[
                            'initial'   => 'T',
                            'full_name' => 'Tomás Aguilera',
                        ],
                    ],

                    (object)[
                        'id'   => 11,
                        'code' => 'STU-011',
                        'pin'  => '5566',
                        'user' => (object)[
                            'name'   => 'Paula Arancibia',
                            'avatar' => 'https://ui-avatars.com/api/?name=Paula+Arancibia&background=E17055&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '4° Básico',
                            'section'   => 'B',
                            'classroom' => '4B',
                        ],
                        'meta' => (object)[
                            'initial'   => 'P',
                            'full_name' => 'Paula Arancibia',
                        ],
                    ],

                    (object)[
                        'id'   => 12,
                        'code' => 'STU-012',
                        'pin'  => '2244',
                        'user' => (object)[
                            'name'   => 'Mateo Castillo',
                            'avatar' => 'https://ui-avatars.com/api/?name=Mateo+Castillo&background=6AB04C&color=fff',
                        ],
                        'school' => (object)[
                            'grade'     => '5° Básico',
                            'section'   => 'A',
                            'classroom' => '5A',
                        ],
                        'meta' => (object)[
                            'initial'   => 'M',
                            'full_name' => 'Mateo Castillo',
                        ],
                    ],
                ]
            ]);
        $this->students = $students;
    }
    public function students()
    {
        return $this->students;
    }
}
