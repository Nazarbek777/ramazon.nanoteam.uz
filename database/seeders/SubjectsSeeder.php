<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Question;
use App\Models\Option;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Tasviriy faoliyat',
                'icon' => 'fas fa-palette',
                'code' => 'TF-001',
                'questions' => [
                    ['content' => 'Tasviriy faoliyatning asosiy turlari qaysilar?', 'options' => [
                        ['content' => 'Rasm chizish, qirqish-yopishtirish, loy ishlash', 'is_correct' => true],
                        ['content' => 'Yugurish, sakrash, suzish', 'is_correct' => false],
                        ['content' => 'O\'qish, yozish, sanash', 'is_correct' => false],
                        ['content' => 'Kuyish, raqs, she\'r', 'is_correct' => false],
                    ]],
                    ['content' => 'Bolalarda rang farqlash qobiliyati necha yoshdan boshlanadi?', 'options' => [
                        ['content' => '1 yoshdan', 'is_correct' => false],
                        ['content' => '2 yoshdan', 'is_correct' => true],
                        ['content' => '4 yoshdan', 'is_correct' => false],
                        ['content' => '5 yoshdan', 'is_correct' => false],
                    ]],
                    ['content' => 'Applikatsiya nima?', 'options' => [
                        ['content' => 'Qog\'ozdan shakl qirqib yopishtirish', 'is_correct' => true],
                        ['content' => 'Rasm chizish', 'is_correct' => false],
                        ['content' => 'Loy ishlash', 'is_correct' => false],
                        ['content' => 'Qum bilan o\'ynash', 'is_correct' => false],
                    ]],
                    ['content' => 'Tasviriy faoliyatda qanday materiallar ishlatiladi?', 'options' => [
                        ['content' => 'Faqat qalamlar', 'is_correct' => false],
                        ['content' => 'Faqat bo\'yoqlar', 'is_correct' => false],
                        ['content' => 'Qalam, bo\'yoq, plastilin, qog\'oz va boshqalar', 'is_correct' => true],
                        ['content' => 'Faqat plastilin', 'is_correct' => false],
                    ]],
                    ['content' => 'Bolalarni rasm chizishga o\'rgatishda birinchi qadam nima?', 'options' => [
                        ['content' => 'Murakkab rasmlar chizish', 'is_correct' => false],
                        ['content' => 'Qalamni to\'g\'ri ushlashni o\'rgatish', 'is_correct' => true],
                        ['content' => 'Bo\'yoq bilan ishlash', 'is_correct' => false],
                        ['content' => 'Portret chizish', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Sahnalashtirish',
                'icon' => 'fas fa-theater-masks',
                'code' => 'SH-001',
                'questions' => [
                    ['content' => 'Bog\'chada sahnalashtirish mashg\'ulotining asosiy maqsadi nima?', 'options' => [
                        ['content' => 'Bolalarning ijodiy qobiliyatini rivojlantirish', 'is_correct' => true],
                        ['content' => 'Bolalarni uyquga yotqizish', 'is_correct' => false],
                        ['content' => 'Bolalarni ovqatlantirish', 'is_correct' => false],
                        ['content' => 'Bolalarni jazolash', 'is_correct' => false],
                    ]],
                    ['content' => 'Qo\'g\'irchoq teatri nima?', 'options' => [
                        ['content' => 'Bolalar o\'ynayotgan joy', 'is_correct' => false],
                        ['content' => 'Qo\'g\'irchoqlar yordamida ertak ko\'rsatish', 'is_correct' => true],
                        ['content' => 'Rasm ko\'rsatish', 'is_correct' => false],
                        ['content' => 'Musiqa tinglash', 'is_correct' => false],
                    ]],
                    ['content' => 'Sahnalashtirish mashg\'ulotida bolaning qaysi qobiliyati rivojlanadi?', 'options' => [
                        ['content' => 'Faqat xotira', 'is_correct' => false],
                        ['content' => 'Nutq, xotira, ijodkorlik, o\'zaro munosabat', 'is_correct' => true],
                        ['content' => 'Faqat jismoniy kuch', 'is_correct' => false],
                        ['content' => 'Faqat yozish', 'is_correct' => false],
                    ]],
                    ['content' => 'Dramatizatsiya bu nima?', 'options' => [
                        ['content' => 'Bolalarning ertak mazmunini rolli o\'ynashi', 'is_correct' => true],
                        ['content' => 'Rasm chizish', 'is_correct' => false],
                        ['content' => 'Qo\'shiq aytish', 'is_correct' => false],
                        ['content' => 'Kitob o\'qish', 'is_correct' => false],
                    ]],
                    ['content' => 'Barmoq teatri qaysi yosh guruhiga mos?', 'options' => [
                        ['content' => 'Faqat katta guruh', 'is_correct' => false],
                        ['content' => 'Faqat tayyorlov guruh', 'is_correct' => false],
                        ['content' => 'Barcha yosh guruhlarga', 'is_correct' => true],
                        ['content' => 'Faqat maktab o\'quvchilariga', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Nutq o\'stirish',
                'icon' => 'fas fa-comments',
                'code' => 'NO-001',
                'questions' => [
                    ['content' => 'Bolaning nutqi rivojlanishida eng muhim davr qaysi?', 'options' => [
                        ['content' => '0-3 yosh', 'is_correct' => true],
                        ['content' => '7-10 yosh', 'is_correct' => false],
                        ['content' => '10-15 yosh', 'is_correct' => false],
                        ['content' => '15-18 yosh', 'is_correct' => false],
                    ]],
                    ['content' => 'Bog\'chada nutq o\'stirishning asosiy usullari qaysilar?', 'options' => [
                        ['content' => 'Faqat kitob o\'qish', 'is_correct' => false],
                        ['content' => 'Suhbat, hikoya qilish, she\'r yodlash, rasmlar ustida ishlash', 'is_correct' => true],
                        ['content' => 'Faqat yozish', 'is_correct' => false],
                        ['content' => 'Faqat tinglash', 'is_correct' => false],
                    ]],
                    ['content' => 'Monolog nutq bu nima?', 'options' => [
                        ['content' => 'Ikki kishi suhbati', 'is_correct' => false],
                        ['content' => 'Bir kishining uzluksiz gapirishi', 'is_correct' => true],
                        ['content' => 'Guruh suhbati', 'is_correct' => false],
                        ['content' => 'Jim turish', 'is_correct' => false],
                    ]],
                    ['content' => 'Lug\'at boyligini oshirishning samarali usuli qaysi?', 'options' => [
                        ['content' => 'Bolani jim o\'tqizish', 'is_correct' => false],
                        ['content' => 'Yangi so\'zlarni o\'yin va mashg\'ulotlar orqali o\'rgatish', 'is_correct' => true],
                        ['content' => 'Televizor ko\'rsatish', 'is_correct' => false],
                        ['content' => 'Uxlatish', 'is_correct' => false],
                    ]],
                    ['content' => 'Dialog nutq bu nima?', 'options' => [
                        ['content' => 'Bir kishining gapirishi', 'is_correct' => false],
                        ['content' => 'Ikki yoki undan ortiq kishining suhbati', 'is_correct' => true],
                        ['content' => 'Yozma nutq', 'is_correct' => false],
                        ['content' => 'Imo-ishora', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Maktabgacha pedagogika',
                'icon' => 'fas fa-chalkboard-teacher',
                'code' => 'MP-001',
                'questions' => [
                    ['content' => 'Maktabgacha ta\'limning asosiy faoliyat turi nima?', 'options' => [
                        ['content' => 'O\'yin faoliyati', 'is_correct' => true],
                        ['content' => 'Mehnat faoliyati', 'is_correct' => false],
                        ['content' => 'O\'quv faoliyati', 'is_correct' => false],
                        ['content' => 'Sport faoliyati', 'is_correct' => false],
                    ]],
                    ['content' => 'MTT tashkilotida necha yosh guruhlari mavjud?', 'options' => [
                        ['content' => '2 ta', 'is_correct' => false],
                        ['content' => '3 ta', 'is_correct' => false],
                        ['content' => '4-5 ta (1-guruh, 2-guruh, o\'rta, katta, tayyorlov)', 'is_correct' => true],
                        ['content' => '1 ta', 'is_correct' => false],
                    ]],
                    ['content' => 'Tarbiyachining asosiy vazifasi nima?', 'options' => [
                        ['content' => 'Faqat bolalarni kuzatish', 'is_correct' => false],
                        ['content' => 'Bolalarni har tomonlama rivojlantirish', 'is_correct' => true],
                        ['content' => 'Faqat ovqatlantirish', 'is_correct' => false],
                        ['content' => 'Faqat uyquga yotqizish', 'is_correct' => false],
                    ]],
                    ['content' => 'Pedagogik jarayon nima?', 'options' => [
                        ['content' => 'O\'qituvchi va o\'quvchi o\'rtasidagi maqsadli ta\'lim-tarbiya jarayoni', 'is_correct' => true],
                        ['content' => 'Faqat dars berish', 'is_correct' => false],
                        ['content' => 'Faqat nazorat qilish', 'is_correct' => false],
                        ['content' => 'Faqat uy vazifasi berish', 'is_correct' => false],
                    ]],
                    ['content' => 'Individual yondashuv deganda nima tushuniladi?', 'options' => [
                        ['content' => 'Barcha bolalarga bir xil munosabat', 'is_correct' => false],
                        ['content' => 'Har bir bolaning xususiyatlariga qarab ta\'lim berish', 'is_correct' => true],
                        ['content' => 'Faqat iqtidorli bolalar bilan ishlash', 'is_correct' => false],
                        ['content' => 'Faqat qiyin bolalar bilan ishlash', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Elementar Matematika',
                'icon' => 'fas fa-calculator',
                'code' => 'EM-001',
                'questions' => [
                    ['content' => 'Bog\'chada bolalarga sanashni o\'rgatish necha yoshdan boshlanadi?', 'options' => [
                        ['content' => '2-3 yoshdan', 'is_correct' => true],
                        ['content' => '5-6 yoshdan', 'is_correct' => false],
                        ['content' => '7 yoshdan', 'is_correct' => false],
                        ['content' => '1 yoshdan', 'is_correct' => false],
                    ]],
                    ['content' => 'Geometrik shakllarni o\'rgatishda qaysi shakl birinchi o\'rgatiladi?', 'options' => [
                        ['content' => 'Doira, kvadrat, uchburchak', 'is_correct' => true],
                        ['content' => 'Konus, silindr', 'is_correct' => false],
                        ['content' => 'Piramida', 'is_correct' => false],
                        ['content' => 'Trapetsiya', 'is_correct' => false],
                    ]],
                    ['content' => 'Katta guruhda bolalar nechagacha sanashni o\'rganadi?', 'options' => [
                        ['content' => '5 gacha', 'is_correct' => false],
                        ['content' => '10 gacha', 'is_correct' => true],
                        ['content' => '100 gacha', 'is_correct' => false],
                        ['content' => '3 gacha', 'is_correct' => false],
                    ]],
                    ['content' => 'Matematik tushunchalarni o\'rgatishning samarali usuli qaysi?', 'options' => [
                        ['content' => 'Faqat daftarga yozish', 'is_correct' => false],
                        ['content' => 'Ko\'rgazmali va amaliy usullar, didaktik o\'yinlar', 'is_correct' => true],
                        ['content' => 'Faqat og\'zaki tushuntirish', 'is_correct' => false],
                        ['content' => 'Faqat kitob o\'qish', 'is_correct' => false],
                    ]],
                    ['content' => 'Miqdorni taqqoslash nima?', 'options' => [
                        ['content' => 'Ikki buyumni rang bo\'yicha solishtirish', 'is_correct' => false],
                        ['content' => 'Buyumlarning ko\'p-oz, teng ekanligini aniqlash', 'is_correct' => true],
                        ['content' => 'Rasm chizish', 'is_correct' => false],
                        ['content' => 'She\'r o\'qish', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'O\'RQ-595',
                'icon' => 'fas fa-gavel',
                'code' => 'ORQ-595',
                'questions' => [
                    ['content' => 'O\'RQ-595-sonli qaror qachon qabul qilingan?', 'options' => [
                        ['content' => '2017-yil', 'is_correct' => true],
                        ['content' => '2020-yil', 'is_correct' => false],
                        ['content' => '2015-yil', 'is_correct' => false],
                        ['content' => '2022-yil', 'is_correct' => false],
                    ]],
                    ['content' => 'O\'RQ-595 nimaga bag\'ishlangan?', 'options' => [
                        ['content' => 'Maktabgacha ta\'lim tizimini tubdan takomillashtirish', 'is_correct' => true],
                        ['content' => 'Oliy ta\'lim islohoti', 'is_correct' => false],
                        ['content' => 'Sport sohasini rivojlantirish', 'is_correct' => false],
                        ['content' => 'Sog\'liqni saqlash', 'is_correct' => false],
                    ]],
                    ['content' => 'O\'RQ-595 bo\'yicha MTTda tarbiyachi malakasi qanday bo\'lishi kerak?', 'options' => [
                        ['content' => 'Istalgan ma\'lumot', 'is_correct' => false],
                        ['content' => 'Pedagogik ma\'lumotga ega bo\'lishi shart', 'is_correct' => true],
                        ['content' => 'Ma\'lumot shart emas', 'is_correct' => false],
                        ['content' => 'Faqat oliy ma\'lumot', 'is_correct' => false],
                    ]],
                    ['content' => 'O\'RQ-595 asosida MTTlarda nechta bolaga 1 tarbiyachi belgilangan?', 'options' => [
                        ['content' => '30 ta bolaga', 'is_correct' => false],
                        ['content' => '25 ta bolaga', 'is_correct' => false],
                        ['content' => 'Me\'yorlarga muvofiq belgilangan', 'is_correct' => true],
                        ['content' => '50 ta bolaga', 'is_correct' => false],
                    ]],
                    ['content' => 'O\'RQ-595 maqsadi nima?', 'options' => [
                        ['content' => 'MTT tizimini zamonaviy talablar asosida isloh qilish', 'is_correct' => true],
                        ['content' => 'Maktablarni yopish', 'is_correct' => false],
                        ['content' => 'Soliqlarni oshirish', 'is_correct' => false],
                        ['content' => 'Kasalxonalar qurish', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => '802-sonli qaror',
                'icon' => 'fas fa-file-alt',
                'code' => 'QR-802',
                'questions' => [
                    ['content' => '802-sonli qaror qaysi sohaga tegishli?', 'options' => [
                        ['content' => 'Maktabgacha ta\'lim tashkilotlari faoliyatini tartibga solish', 'is_correct' => true],
                        ['content' => 'Oliy ta\'lim', 'is_correct' => false],
                        ['content' => 'Harbiy ta\'lim', 'is_correct' => false],
                        ['content' => 'Tibbiyot', 'is_correct' => false],
                    ]],
                    ['content' => '802-sonli qarorga ko\'ra MTT qanday ish tartibida ishlaydi?', 'options' => [
                        ['content' => 'Faqat tunda', 'is_correct' => false],
                        ['content' => 'Belgilangan ish tartibi va kun rejimiga muvofiq', 'is_correct' => true],
                        ['content' => 'Istalgan vaqtda', 'is_correct' => false],
                        ['content' => 'Faqat dam olish kunlari', 'is_correct' => false],
                    ]],
                    ['content' => '802-sonli qarorga ko\'ra MTTda bolalar ovqatlanishi qanday tashkil etiladi?', 'options' => [
                        ['content' => 'Kuniga 1 marta', 'is_correct' => false],
                        ['content' => 'Belgilangan me\'yor va ratsion asosida', 'is_correct' => true],
                        ['content' => 'Ovqat berilmaydi', 'is_correct' => false],
                        ['content' => 'Faqat nonushta beriladi', 'is_correct' => false],
                    ]],
                    ['content' => '802-sonli qaror bo\'yicha MTTda tibbiy ko\'rik qanday o\'tkaziladi?', 'options' => [
                        ['content' => 'Umuman o\'tkazilmaydi', 'is_correct' => false],
                        ['content' => 'Yiliga bir marta', 'is_correct' => false],
                        ['content' => 'Belgilangan tartibda muntazam ravishda', 'is_correct' => true],
                        ['content' => 'Faqat kasal bo\'lganda', 'is_correct' => false],
                    ]],
                    ['content' => '802-sonli qarorga ko\'ra ota-onalar bilan hamkorlik qanday amalga oshiriladi?', 'options' => [
                        ['content' => 'Ota-onalar bilan aloqa qilish shart emas', 'is_correct' => false],
                        ['content' => 'Yig\'ilishlar, maslahatlar va hamkorlik rejasi asosida', 'is_correct' => true],
                        ['content' => 'Faqat telefon orqali', 'is_correct' => false],
                        ['content' => 'Faqat maktublar orqali', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Ilk qadam dasturi',
                'icon' => 'fas fa-baby',
                'code' => 'IQ-001',
                'questions' => [
                    ['content' => '"Ilk qadam" dasturi qaysi yosh bolalari uchun mo\'ljallangan?', 'options' => [
                        ['content' => '1 yoshdan 3 yoshgacha', 'is_correct' => true],
                        ['content' => '5 yoshdan 7 yoshgacha', 'is_correct' => false],
                        ['content' => '7 yoshdan 10 yoshgacha', 'is_correct' => false],
                        ['content' => '10 yoshdan 15 yoshgacha', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilk qadam" dasturining asosiy maqsadi nima?', 'options' => [
                        ['content' => 'Go\'dakni maktabga tayyorlash', 'is_correct' => false],
                        ['content' => 'Erta yoshdagi bolalarning har tomonlama rivojlanishini ta\'minlash', 'is_correct' => true],
                        ['content' => 'Faqat jismoniy rivojlantirish', 'is_correct' => false],
                        ['content' => 'Faqat nutqni rivojlantirish', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilk qadam" dasturida nechta ta\'lim sohasi mavjud?', 'options' => [
                        ['content' => '2 ta', 'is_correct' => false],
                        ['content' => '3 ta', 'is_correct' => false],
                        ['content' => '5 ta', 'is_correct' => true],
                        ['content' => '10 ta', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilk qadam" dasturida bola rivojlanishining qaysi sohalari qamrab olingan?', 'options' => [
                        ['content' => 'Faqat jismoniy', 'is_correct' => false],
                        ['content' => 'Jismoniy, aqliy, nutqiy, ijtimoiy-emotsional', 'is_correct' => true],
                        ['content' => 'Faqat aqliy', 'is_correct' => false],
                        ['content' => 'Faqat emotsional', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilk qadam" dasturi kim tomonidan ishlab chiqilgan?', 'options' => [
                        ['content' => 'Chet el mutaxassislari', 'is_correct' => false],
                        ['content' => 'O\'zbekiston MTT vazirligi mutaxassislari', 'is_correct' => true],
                        ['content' => 'Maktab o\'qituvchilari', 'is_correct' => false],
                        ['content' => 'Talabalar', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => 'Ilm yo\'li variativ dasturi',
                'icon' => 'fas fa-road',
                'code' => 'IY-001',
                'questions' => [
                    ['content' => '"Ilm yo\'li" dasturi qaysi yosh bolalari uchun mo\'ljallangan?', 'options' => [
                        ['content' => '3 yoshdan 7 yoshgacha', 'is_correct' => true],
                        ['content' => '1 yoshdan 2 yoshgacha', 'is_correct' => false],
                        ['content' => '7 yoshdan 11 yoshgacha', 'is_correct' => false],
                        ['content' => '0 yoshdan 1 yoshgacha', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilm yo\'li" dasturining maqsadi nima?', 'options' => [
                        ['content' => 'Bolalarni maktabga tayyorlash va har tomonlama rivojlantirish', 'is_correct' => true],
                        ['content' => 'Faqat yozishni o\'rgatish', 'is_correct' => false],
                        ['content' => 'Faqat sport bilan shug\'ullantirish', 'is_correct' => false],
                        ['content' => 'Faqat uxlatish', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilm yo\'li" variativ dasturi nimasi bilan farq qiladi?', 'options' => [
                        ['content' => 'Tarbiyachi o\'zi tanlashi va moslashtirishi mumkin', 'is_correct' => true],
                        ['content' => 'Hech qanday farqi yo\'q', 'is_correct' => false],
                        ['content' => 'Faqat matematikani o\'z ichiga oladi', 'is_correct' => false],
                        ['content' => 'Faqat katta bolalar uchun', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilm yo\'li" dasturida qanday ta\'lim texnologiyalari qo\'llaniladi?', 'options' => [
                        ['content' => 'Faqat an\'anaviy usullar', 'is_correct' => false],
                        ['content' => 'O\'yin, loyiha, interfaol texnologiyalar', 'is_correct' => true],
                        ['content' => 'Faqat ma\'ruza usuli', 'is_correct' => false],
                        ['content' => 'Faqat test usuli', 'is_correct' => false],
                    ]],
                    ['content' => '"Ilm yo\'li" dasturida bolaning qaysi kompetensiyalari shakllantiriladi?', 'options' => [
                        ['content' => 'Faqat bilim', 'is_correct' => false],
                        ['content' => 'Bilim, ko\'nikma, malaka va shaxsiy sifatlar', 'is_correct' => true],
                        ['content' => 'Faqat jismoniy kuch', 'is_correct' => false],
                        ['content' => 'Faqat xotira', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => '424-sonli qaror',
                'icon' => 'fas fa-file-contract',
                'code' => 'QR-424',
                'questions' => [
                    ['content' => '424-sonli qaror nimaga bag\'ishlangan?', 'options' => [
                        ['content' => 'Maktabgacha ta\'lim sifatini oshirish chora-tadbirlari', 'is_correct' => true],
                        ['content' => 'Soliq islohoti', 'is_correct' => false],
                        ['content' => 'Harbiy xizmat', 'is_correct' => false],
                        ['content' => 'Savdo-sotiq', 'is_correct' => false],
                    ]],
                    ['content' => '424-sonli qaror bo\'yicha tarbiyachilar malaka oshirishi shartmi?', 'options' => [
                        ['content' => 'Yo\'q, ixtiyoriy', 'is_correct' => false],
                        ['content' => 'Ha, belgilangan tartibda muntazam', 'is_correct' => true],
                        ['content' => 'Faqat yangi tarbiyachilar', 'is_correct' => false],
                        ['content' => 'Faqat rahbarlar', 'is_correct' => false],
                    ]],
                    ['content' => '424-sonli qarorga ko\'ra MTTda ta\'lim qaysi tilda olib boriladi?', 'options' => [
                        ['content' => 'Faqat o\'zbek tilida', 'is_correct' => false],
                        ['content' => 'Davlat tili va boshqa tillarda qonunga muvofiq', 'is_correct' => true],
                        ['content' => 'Faqat rus tilida', 'is_correct' => false],
                        ['content' => 'Faqat ingliz tilida', 'is_correct' => false],
                    ]],
                    ['content' => '424-sonli qaror bo\'yicha MTT binolariga qanday talablar qo\'yiladi?', 'options' => [
                        ['content' => 'Hech qanday talablar yo\'q', 'is_correct' => false],
                        ['content' => 'Sanitariya-gigiena va xavfsizlik me\'yorlariga javob berishi', 'is_correct' => true],
                        ['content' => 'Faqat chiroyli bo\'lishi', 'is_correct' => false],
                        ['content' => 'Faqat katta bo\'lishi', 'is_correct' => false],
                    ]],
                    ['content' => '424-sonli qaror qachon qabul qilingan?', 'options' => [
                        ['content' => '2017-yil', 'is_correct' => true],
                        ['content' => '2010-yil', 'is_correct' => false],
                        ['content' => '2023-yil', 'is_correct' => false],
                        ['content' => '2005-yil', 'is_correct' => false],
                    ]],
                ],
            ],
            [
                'name' => '334-sonli buyruq',
                'icon' => 'fas fa-scroll',
                'code' => 'BY-334',
                'questions' => [
                    ['content' => '334-sonli buyruq nimaga bag\'ishlangan?', 'options' => [
                        ['content' => 'Maktabgacha ta\'lim tashkilotlari uchun me\'yoriy hujjat', 'is_correct' => true],
                        ['content' => 'Oliy ta\'lim', 'is_correct' => false],
                        ['content' => 'Sanoat korxonalari', 'is_correct' => false],
                        ['content' => 'Savdo markazi', 'is_correct' => false],
                    ]],
                    ['content' => '334-sonli buyruqga ko\'ra tarbiyachi ish kunini qanday boshlaydi?', 'options' => [
                        ['content' => 'Bolalarni qabul qilish va sog\'ligini tekshirish bilan', 'is_correct' => true],
                        ['content' => 'Nonushta tayyorlash bilan', 'is_correct' => false],
                        ['content' => 'Uyquga yotqizish bilan', 'is_correct' => false],
                        ['content' => 'O\'yin o\'ynash bilan', 'is_correct' => false],
                    ]],
                    ['content' => '334-sonli buyruq bo\'yicha kun rejimiga nimalar kiradi?', 'options' => [
                        ['content' => 'Faqat ovqatlanish', 'is_correct' => false],
                        ['content' => 'Ertalabki gimnastika, mashg\'ulotlar, ovqatlanish, uyqu, sayr', 'is_correct' => true],
                        ['content' => 'Faqat darslar', 'is_correct' => false],
                        ['content' => 'Faqat uyqu', 'is_correct' => false],
                    ]],
                    ['content' => '334-sonli buyruqqa ko\'ra MTTda hujjat yuritish shartmi?', 'options' => [
                        ['content' => 'Yo\'q, shart emas', 'is_correct' => false],
                        ['content' => 'Ha, belgilangan tartibda yuritilishi shart', 'is_correct' => true],
                        ['content' => 'Faqat rahbar uchun', 'is_correct' => false],
                        ['content' => 'Faqat oshpaz uchun', 'is_correct' => false],
                    ]],
                    ['content' => '334-sonli buyruqqa ko\'ra tarbiyachi qanday shaxsiy sifatlarga ega bo\'lishi kerak?', 'options' => [
                        ['content' => 'Hech qanday talablar yo\'q', 'is_correct' => false],
                        ['content' => 'Sabr-toqatli, mehribon, mas\'uliyatli va kasbiy bilimga ega', 'is_correct' => true],
                        ['content' => 'Faqat kuchli bo\'lishi kerak', 'is_correct' => false],
                        ['content' => 'Faqat yosh bo\'lishi kerak', 'is_correct' => false],
                    ]],
                ],
            ],
        ];

        foreach ($data as $subjectData) {
            $subject = Subject::firstOrCreate(
                ['slug' => Str::slug($subjectData['name'])],
                [
                    'name' => $subjectData['name'],
                    'slug' => Str::slug($subjectData['name']),
                    'icon' => $subjectData['icon'],
                    'description' => $subjectData['name'] . ' bo\'yicha attestatsiya testi',
                ]
            );

            // Create quiz for this subject
            $quiz = Quiz::firstOrCreate(
                ['access_code' => $subjectData['code']],
                [
                    'subject_id' => $subject->id,
                    'title' => $subjectData['name'],
                    'access_code' => $subjectData['code'],
                    'time_limit' => 15,
                    'pass_score' => 70,
                    'is_random' => true,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(30),
                ]
            );

            // Create questions
            foreach ($subjectData['questions'] as $qData) {
                $existing = Question::where('subject_id', $subject->id)
                    ->where('content', $qData['content'])
                    ->first();

                if (!$existing) {
                    $question = Question::create([
                        'subject_id' => $subject->id,
                        'content' => $qData['content'],
                        'type' => 'single',
                    ]);

                    foreach ($qData['options'] as $oData) {
                        Option::create([
                            'question_id' => $question->id,
                            'content' => $oData['content'],
                            'is_correct' => $oData['is_correct'],
                        ]);
                    }

                    $quiz->questions()->attach($question->id);
                }
            }
        }
    }
}
