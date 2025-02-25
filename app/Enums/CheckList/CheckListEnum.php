<?php

namespace App\Enums\CheckList;

enum CheckListEnum: int
{
    case NgoDirectorNid = 1;
    case NgoDirectorProfile = 2;
    case director_work_permit = 3;
    case ngo_representor_letter = 4;
    case ministry_of_economy_work_permit = 5;
    case articles_of_association = 6;
    case ngo_register_form_en = 8;
    case ngo_register_form_ps = 9;
    case ngo_register_form_fa = 10;
}
