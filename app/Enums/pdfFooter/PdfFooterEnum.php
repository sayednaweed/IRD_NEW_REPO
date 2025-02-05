<?php

namespace App\Enums\pdfFooter;

enum PdfFooterEnum: string
{
    case REGISTER_FOOTER = '
        <div style="width: 100%; text-align: right; margin: 0; padding: 0; direction: ltr; font-family: amiri; font-size: 10pt; border: none;">
            <table style="width: 100%; border: none; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 33%; text-align: left; border: none;">{PAGENO}</td>
                    <td style="width: 34%; text-align: center; border: none;">مابین</td>
                    <td style="width: 33%; text-align: right; border: none;">
                        وزارت صحت عامه امارت اسلامی افغانستان وزیر اکبر خان
                        <br>ریاست روابط بین الملل
                        <br>شماره تماس :۰۷۹۸۳۲۹۳۸
                        <br>ایمیل ادرس : test@gmail.com
                    </td>
                </tr>
            </table>
        </div>
    ';

    case MOU_FIRST_FOOTER_en = '
    
    <div>   
    <p style="font-size:12px; margin:0; padding:0;">
    Ministry of Public Health of Islamic emirate of Afghanistan</p> 
    <p style="font-size:12px;margin:0; padding:0; ">
    International Relations Directorate Contact:
 
    
    <br>
     020 230 1529 
    <br>
    Email add: <email> moph.ird@gmail.com </email>
    </p>
    </div>
    ';
    case MOU_FOOTER_en = '
        <div style="width: 100%; text-align: right; margin: 0; padding: 0; direction: rtl; font-family: amiri; font-size: 10pt; border: none;">
            <table style="width: 100%; border: none; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 33%; text-align: left; border: none;">{PAGENO}</td>
                    <td style="width: 34%; text-align: center; border: none;">مابین</td>
                    <td style="width: 33%; text-align: right; border: none;">
                        وزارت صحت عامه امارت اسلامی افغانستان وزیر اکبر خان
                        <br>ریاست روابط بین الملل
                        <br>شماره تماس :۰۷۹۸۳۲۹۳۸
                        <br>ایمیل ادرس : test@gmail.com
                    </td>
                </tr>
            </table>
        </div>
    ';



    case DIRECTORATE = 'Directorate footer content';
}
