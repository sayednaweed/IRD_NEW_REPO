<?php

namespace Database\Seeders;

use App\Enums\CheckList\CheckListEnum;
use App\Enums\CheckListTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\RoleEnum;
use App\Models\CheckList;
use App\Models\CheckListTrans;
use App\Models\CheckListType;
use App\Models\CheckListTypeTrans;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->CheckListType();
        $this->ngoRegisterationCheckList();
        $this->projectRegisterationCheckList();
    }

    protected function CheckListType()
    {
        $checklist = CheckListType::create([
            'id' => CheckListTypeEnum::ngoRegister,
        ]);
        CheckListTypeTrans::create([
            'value' => "NGO Register",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::default,
        ]);

        CheckListTypeTrans::create([
            'value' => "ثبت موسسه",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTypeTrans::create([
            'value' => "د موسسې ثبتول",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::pashto,
        ]);
        // Project
        $checklist = CheckListType::create([
            'id' => CheckListTypeEnum::projectRegister,
        ]);
        CheckListTypeTrans::create([
            'value' => "Project Register",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTypeTrans::create([
            'value' => "ثبت پروژه",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTypeTrans::create([
            'value' => "د پروژې راجستر",
            'check_list_type_id' => $checklist->id,
            'language_name' => LanguageEnum::pashto,
        ]);
    }

    protected function ngoRegisterationCheckList()
    {

        // 1.
        $checklist = CheckList::create([
            'id' => CheckListEnum::NgoDirectorNid,
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تذکره یا پاسپورت  رئیس موسسه",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د موسسه د  رئیس تذکره یا پاسپورټ",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د موسسه جوړښت",
            'language_name' => LanguageEnum::pashto,
        ]);


        //2. 
        $checklist = CheckList::create([
            "id" => CheckListEnum::director_work_permit,
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Director Work Permit",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "جواز کار رئیس",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د رئیس کار جواز",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 2.
        $checklist = CheckList::create([
            'id' => CheckListEnum::ministry_of_economy_work_permit,
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Ministry of Economic Work Permit",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "جواز کار وزارت اقتصاد",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د اقتصاد وزارت څخه د کار جواز",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 3.


        $checklist = CheckList::create([
            'id' => CheckListEnum::articles_of_association,
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Articles of Association",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "اساس نامه",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "اساس نامه",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 4.
        $checklist = CheckList::create([
            "id" => CheckListEnum::ngo_representor_letter,
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Representor introducation letter",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "مکتوب معرفی نمایده",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د نماینده د معرفي لیک",
            'language_name' => LanguageEnum::pashto,
        ]);
        //5.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "NGO Structure",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تشکیلات موسسه",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د موسسه جوړښت",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 6.

        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Signed Registration Form (English)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "فرم ثبت نام امضا شده (انگلیسی)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "لاسلیک شوی د نوم لیکنې فورمه (انګلیسي)",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 6.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Signed Registration Form (Pashto)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "فرم ثبت نام امضا شده (پشتو)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "لاسلیک شوی د نوم لیکنې فورمه (فارسي)",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 7.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::ngoRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Signed Registration Form (Farsi)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "فرم ثبت نام امضا شده (فارسی)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "لاسلیک شوی د نوم لیکنې فورمه (پښتو)",
            'language_name' => LanguageEnum::pashto,
        ]);
    }
    protected function projectRegisterationCheckList()
    {
        // 1.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Ministry of Economic Work Permit",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "جواز کار وزارت اقتصاد",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د اقتصاد وزارت څخه د کار جواز",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 2.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Articles of Association",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "اساس نامه",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "اساس نامه",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 3.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Project introduction letter from the Ministry of Economy",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "مکتوب معرفی پروژه از وزارت اقتصاد",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د اقتصاد وزارت له خوا د پروژې د معرفي کولو لیک",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 4.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "NGO & Donor Contract Letter",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "نامه قرارداد موسسه و دونر",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د موسسه او دونر ترمنځ د قرارداد لیک",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 5.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,ppt,pptx",
            'acceptable_mimes' => ".pdf,.ppt,.pptx",
            'description' => "",
            'file_size' => 2048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Project Presentation",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "پرزنتیشن پروژه",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "د پروژې پریزنټیشن",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 6.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Memorandum of Understanding (English)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (انگلیسی)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (انگلیسی)",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 7.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Memorandum of Understanding (Farsi)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (فارسی)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (فارسی)",
            'language_name' => LanguageEnum::pashto,
        ]);
        // 8.
        $checklist = CheckList::create([
            'check_list_type_id' => CheckListTypeEnum::projectRegister,
            'acceptable_extensions' => "pdf,jpeg,jpg,png",
            'acceptable_mimes' => ".pdf,.jpeg,.jpg,.png",
            'description' => "",
            'file_size' => 4048,
            'user_id' => RoleEnum::super,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "Memorandum of Understanding (Pashto)",
            'language_name' => LanguageEnum::default,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (پشتو)",
            'language_name' => LanguageEnum::farsi,
        ]);
        CheckListTrans::create([
            'check_list_id' => $checklist->id,
            'value' => "تفاهم نامه (پشتو)",
            'language_name' => LanguageEnum::pashto,
        ]);
    }
}
