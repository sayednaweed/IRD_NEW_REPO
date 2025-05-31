<?php

namespace App\Enums;

enum SubPermissionEnum: int
{
    // User
    case user_information = 1;
    case user_password = 2;
    case user_permission = 3;
    public const USERS = [
        1 => "account_information",
        2 => "update_account_password",
        3 => "permissions"
    ];
        // settings
    case setting_language = 21;
    case setting_job = 22;
    case setting_destination = 23;
    case setting_checklist = 24;
    case setting_news_type = 25;
    case setting_priority = 26;
    public const SETTINGS = [
        21 => "language",
        22 => "job",
        23 => "destination",
        24 => "checklist",
        25 => "news_type",
        26 => "priority",
    ];
        // Approval
    case pending_approval = 31;
    case approved_approval = 32;
    case rejected_approval = 33;
    public const APPROVALS = [
        31 => "user",
        32 => "ngo",
        33 => "donor"
    ];
        // Activity
    case user_activity = 41;
    case password_activity = 42;
    public const ACTIVITY = [
        41 => "user",
        42 => "password",
    ];
        // NGO
    case ngo_information = 52;
    case ngo_director_information = 53;
    case ngo_agreement = 54;
    case ngo_agreement_status = 55;
    case ngo_more_information = 56;
    case ngo_status = 57;
    case ngo_representative = 58;
    case ngo_update_account_password = 59;
    public const NGO = [
        52 => "account_information",
        53 => "director_information",
        54 => "agreement_checklist",
        55 => "agreement_status",
        56 => "more_information",
        57 => "status",
        58 => "representative",
        59 => "update_account_password",
    ];
    case project_detail = 61;
    case project_center_budget = 62;
    case project_organization_structure = 63;
    case project_checklist = 64;
    public const PROJECT = [
        61 => "detail",
        62 => "center_budget",
        63 => "organ_structure",
        64 => "checklist",
    ];
        // ABOUT
    case about_director = 71;
    case about_manager = 72;
    case about_office = 73;
    case about_slider = 74;
    case about_technical = 75;
    public const ABOUT = [
        71 => "director",
        72 => "manager",
        73 => "office",
        74 => "pic",
        75 => "technical_sup"
    ];


    // donor

    public const Donor = [
        76 => "donor_status",
        77 => 'donor_information',
        78 => 'project',
        79 => 'donor_update_account_password'



    ];
}
